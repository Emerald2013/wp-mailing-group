<?php
/* get all variables */
$addme  = (isset($_POST["addme"])? sanitize_text_field($_POST["addme"]): '');
$act    = (isset($_REQUEST["act"])? sanitize_text_field($_REQUEST["act"]): '');
$gid    = (isset($_REQUEST["gid"])? sanitize_text_field($_REQUEST["gid"]): '');
$recid  = (isset($_REQUEST["id"])? sanitize_text_field($_REQUEST["id"]): '');
$unsid  = (isset($_REQUEST["unsid"])? sanitize_text_field($_REQUEST["unsid"]): '');
$info   = (isset($_REQUEST["info"])? sanitize_text_field($_REQUEST["info"]): '');
$_POST = stripslashes_deep( $_POST );
/* get all variables */
if($act=="check") {
	$username = sanitize_text_field($_REQUEST['username']);
	$username_e = username_exists( $username );
	$email_e = email_exists($username);
	if($username_e || $email_e) {
		echo "no";
	} else {
		echo "yes";
	}
	die();
} else if($act=="upd") {
	$result = get_userdata( $recid );
	if ($result) {		
		$id = $result->ID;
		$name = $result->first_name;
		$email = $result->user_email;
		$username = $result->user_login;
		$status  = get_user_meta($id, "User_status", true);
		$group_namearr = $objMem->selectRows($table_name_user_taxonomy, "",  " where user_id = '".$id."' order by id desc");
		if (count($group_namearr)>0) {
			foreach ($group_namearr as $grouparr) {
				$group_name[$grouparr->group_id] = $grouparr->group_email_format;
			}
		} else {
			$group_name = array();
		}
		$btn = __("Update Member", 'mailing-group-module');
		$hidval = 2;
	}
} else if($act=="uns" && $unsid!='') {
	$group_arr_old = unserialize(get_user_meta($recid, "Group_subscribed", true));
	unset($group_arr_old[$unsid]);
	$grpserial = serialize($group_arr_old);
	update_user_meta( $recid, "Group_subscribed", $grpserial );
	$objMem->updUserGroupTaxonomy($table_name_user_taxonomy, $recid, $group_arr_old);
	wpmg_redirectTo("wpmg_mailinggroup_memberadd&act=upd&id=$recid&gid=$gid&info=uns");
	exit;
} else {
	$btn = __("Submit", 'mailing-group-module');
	$id = "";
	$name = "";
	$email = "";
	$status = "";
	$group_name = array();
	$email_format = "";
	$add = "";
	$hidval = 1;
}
if($addme==1)
{
	$usercount = $objMem->getGroupUserCount($table_name_user_taxonomy, $gid);
	$usercount = count($usercount);
	if($usercount>=$memberLimit) {
		wpmg_showmessages("error", __("You can only add 20 member(s) per group, Please upgrade to paid version for more features.", 'mailing-group-module'));
	} else {
		$random_password = wp_generate_password( 12, false );
		$name = sanitize_text_field($_POST['name']);
		$email = sanitize_email($_POST['email']);
		$username = sanitize_text_field($_POST['username']);
		$auto_generate   = (isset($_POST["auto_generate"])? sanitize_text_field($_POST["auto_generate"]): '');
		$confirmation_email = sanitize_text_field($_POST['confirmation_email']);
		$status = sanitize_text_field($_POST['status']);
		if($auto_generate && $username=="") {
			$username = $email;
		}
		$username_e = username_exists( $username );
		$email_e = email_exists($email);
		if($username!='' && $email!='') {
			if(is_numeric($username_e)) {
				wpmg_showmessages("error", __("The username entered already exists in the system.", 'mailing-group-module'));
				$username = "";
			} else if(is_numeric($email_e)) {
				wpmg_showmessages("error", __("The email entered already exists in the system.", 'mailing-group-module'));
			} else {
				$userdata = array( 
					'user_login' => $username,
					'first_name' => $name,
					'user_pass' => $random_password,
					'user_email' => $email,
					'role' => 'subscriber' );
				$user_id = wp_insert_user( $userdata );
				
				if($user_id) {			
					$grpsArray = $objMem->getGroupSerialized($_POST);
					$grpserial = serialize($grpsArray);
					add_user_meta( $user_id, "Plugin", "groupmailing" );
					add_user_meta( $user_id, "User_status", $status );
					add_user_meta( $user_id, "Group_subscribed", $grpserial );
					$objMem->addUserGroupTaxonomy($table_name_user_taxonomy, $user_id, $grpsArray);
				}
				if($confirmation_email) {
					$wpdb->update( $wpdb->users, array( sanitize_key( "user_status" ) => "2" ), array( 'ID' => $user_id ) );
					wpmg_sendConfirmationtoMember($user_id, $grpsArray);
				} else {
					wp_new_user_notification($user_id, $random_password);
				}
				wpmg_redirectTo("wpmg_mailinggroup_memberlist&info=saved&gid=".$gid);
				exit;
			}
		} else {
			wpmg_showmessages("error", __("Please enter username or email to proceed.", 'mailing-group-module'));
		}
	}
} else if($addme==2) {
	$recid = sanitize_text_field($_POST["id"]);
	$name = sanitize_text_field($_POST['name']);
	$status = sanitize_text_field($_POST['status']);
	if($name!='') {
		$userdata = array( 
				'ID' => $recid,
				'first_name' => $name
				);
		$statusold  = get_user_meta($recid, "User_status", true);
		wp_update_user( $userdata );
		update_user_meta( $recid, "User_status", $status, $statusold );
		$grpsArray = $objMem->getGroupSerialized($_POST);
		$grpserial = serialize($objMem->getGroupSerialized($_POST));
		update_user_meta( $recid, "Group_subscribed", $grpserial );
		$objMem->updUserGroupTaxonomy($table_name_user_taxonomy, $recid, $grpsArray);
		wpmg_redirectTo("wpmg_mailinggroup_memberlist&info=upd&gid=".$gid);
		exit;
	} else {
		wpmg_showmessages("error", __("Please enter username to proceed.", 'mailing-group-module'));
	}
}
if($info=="userexists") {
	wpmg_showmessages("error", __("The email entered already exists in the system.", 'mailing-group-module'));
} else if($info=="uns") {
	wpmg_showmessages("updated", __("Member has been unsubcribed from the group.", 'mailing-group-module'));
}
$email_format="";
$result_groups = $objMem->selectRows($table_name_group, "", " order by id asc");
?>
<script type="application/javascript">
	jQuery(document).ready(function() {
		jQuery('#auto_generate').click( function(){
			if (jQuery('#auto_generate').is(':checked')) {
				jQuery("#gen_username").hide();
			} else {
				jQuery("#gen_username").show();
			}
		});
		if (jQuery('#auto_generate').is(':checked')) {
			jQuery("#gen_username").hide();
		}
		jQuery("#check_username").click( function(){
			if(trim(jQuery("#username").val())=="") {
				alert("<?php _e("Please enter username to check.", 'mailing-group-module'); ?>");
				jQuery("#username").focus();
				return false;
			}
			var thisUsername = trim(jQuery("#username").val());
			var data = { action: 'wpmg_checkusername', page: 'wpmg_mailinggroup_memberadd',username:thisUsername,act:"check"};
			jQuery.post(ajaxurl, data, function(response) {
				if(trim(response)=='yes') {
					alert("<?php _e("Username is available.", 'mailing-group-module'); ?>");
					jQuery("#username").val(thisUsername);
					return true;
				} else {
					alert("<?php _e("Username is not available, please try again.", 'mailing-group-module'); ?>");
					jQuery("#username").val(thisUsername);
					return true;
				}
			});
		});
		jQuery('#auto_generate').click();
	});
</script>
<script type="text/javascript">
	/* <![CDATA[ */
	jQuery(document).ready(function() {
		/* Build the DataTable with third column using our custom sort functions */
		jQuery('#memberaddedit').dataTable( {
			"aoColumnDefs": [ 
			  { "bSortable": false,
			  	"aTargets": [ 0,1,2 ]
			  }
			],
			"bPaginate": false,
			"bFilter": false
		} );
		jQuery("#toplevel_page_mailinggroup_intro").removeClass('wp-not-current-submenu');
		jQuery("#toplevel_page_mailinggroup_intro").addClass('wp-has-current-submenu');
		//jQuery("#toplevel_page_mailinggroup_intro ul :nth-child(3)").addClass("current");
	} );
	/* ]]> */
</script>
<style>
.dataTables_info {
	display:none;
}
.check_div {
	width:400px;
}
.col-left-2 {
	width:100% !important;
}
</style>
<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub">
	<div class="icon32" id="icon-edit"><br/></div>
    <h2><?php _e("Add/Edit Member", 'mailing-group-module'); ?></h2>
    <div id="col-left-2">
        <div class="col-wrap">
            <div>
            	<p>To add a new Mailing Group subscriber, please fill in the form below. This will automatically create a basic user account on your WordPress site, which will enable the subscriber to log in and update their mailing preferences later on. They will not be able to make any changes to the rest of your website or settings.</p>
                <p>If the user you wish to add already exists on your WordPress site, please use the Import User page to add them to your Mailing Group. You can also import a list of names and email addresses from a CSV file (and VCF for Premium plugin users), or create a Subscription Request to add a new user to multiple Mailing Groups at the same time (multiple Mailing Groups available in Premium plugin only).</p>
                <div class="form-wrap">
                    <form class="validate" action="" method="post" id="addmember">
                        <div class="form-field">
                            <label for="tag-name"><?php _e("Name", 'mailing-group-module'); ?> : </label>
                            <input type="text" size="40" id="name" name="name" value="<?php echo $name; ?>"/>
                        </div>
                        <?php if($act!='upd') { ?>
                            <div class="form-field">
                                <label for="tag-name">&nbsp;</label>
                                <input type="checkbox" name="auto_generate" <?php echo (isset($auto_generate) && $auto_generate =='1'?"checked":""); ?> value="1" id="auto_generate" />&nbsp;<?php _e("Auto-generate WordPress username", 'mailing-group-module'); ?>
                            </div>
                        <?php } ?>
    					<div class="form-field" id="gen_username">
                            <label for="tag-name"><?php _e("Username", 'mailing-group-module'); ?> : </label>
                            <input type="text" size="40" id="username" name="username" <?php echo (isset($act) && $act=='upd'?'disabled="disabled"':"")?> value="<?php echo (isset($username))? $username :''; ?>"/><?php if($act!='upd') { ?>&nbsp;<a href="#" id="check_username" name="check_username"/><?php _e("Check Availability", 'mailing-group-module'); ?></a><?php } ?><?php echo ($act=='upd'?'&nbsp;'.__("Username cannot be edited.", 'mailing-group-module'):"")?>
                        </div>
                        <div class="form-field">
                            <label for="tag-name"><?php _e("Email Address", 'mailing-group-module'); ?> : </label>
                            <input type="text" size="40" id="email" name="email" <?php echo ($act=='upd'?'disabled="disabled"':"")?> value="<?php echo $email; ?>"/><?php echo ($act=='upd'?'&nbsp;'.__("Email cannot be edited.", 'mailing-group-module'):"")?>
                        </div>
                        <?php if($act!='upd') { ?>
                            <div class="form-field">
                                <label for="tag-name">&nbsp;</label>
                                <div><p class="ltfloat"><input type="radio" name="confirmation_email" <?php echo (isset($confirmation_email) && $confirmation_email=='1'?"checked":(!isset($confirmation_email)?"checked":"")); ?> class="confirmation_email" id="confirmation_email_1" value="1" /><?php _e("Send opt-in confirmation link", 'mailing-group-module'); ?></p><p class="ltfloat"><input type="radio" <?php echo (isset($confirmation_email) && $confirmation_email=='0'?"checked":""); ?> name="confirmation_email" class="confirmation_email" id="confirmation_email_0" value="0" /><?php _e(" Skip opt-in confirmation", 'mailing-group-module'); ?></p></div>
                            </div>
                        <?php } ?>
                        <div class="form-field">
                            <label for="tag-name"><?php _e("Group Name", 'mailing-group-module'); ?> : </label>
                            <div class="check_div">
                            	<table class="wp-list-table widefat fixed" id="memberaddedit">
                                	<thead>
                                        <tr role="row" class="topRow">
                                            <th class="sort topRow_messagelist"><?php _e("Mailing Group Name", 'mailing-group-module'); ?></th>
                                            <th><?php _e("Subscription Status", 'mailing-group-module'); ?></th>
                                            <th><?php _e("Email Format", 'mailing-group-module'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
									<?php
									foreach($result_groups as $group) {
										$checkSelected = false;
										if(array_key_exists($group->id,$group_name)) {
											$checkSelected = true;
										}
									?>
                                        <tr>
                                        	<td><input type="checkbox" name="group_name[]" id="selector" value="<?php echo $group->id; ?>" <?php echo ($checkSelected?"checked":($gid==$group->id?"checked":"")) ?> />&nbsp;<?php echo $group->title; ?>
                                            </td>
                                            <td>
                                            	<?php if($checkSelected) { echo "Yes"; } else { echo "No"; } ?>
                                            </td>
                                            <td>
                                            	<div class="check_div">
                                                    <input type="radio" name="email_format_<?php echo $group->id; ?>" <?php echo (isset($group_name[$group->id]) && $group_name[$group->id]=='1'?"checked":(count($group_name)=='0'?"checked":(!isset($group_name[$group->id])?"checked":""))); ?> value="1" />&nbsp;<?php _e("HTML", 'mailing-group-module'); ?>
                                                    <br />
                                                    <input type="radio" <?php echo (isset($group_name[$group->id]) && $group_name[$group->id]=='2'?"checked":""); ?> name="email_format_<?php echo $group->id; ?>" value="2" />&nbsp;<?php _e("Plain Text", 'mailing-group-module'); ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    	</tbody>
                            	</table>
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="status">&nbsp;</label>
                            <div>
                                <p class="ltfloat">
                                    <input type="radio" name="status" <?php echo ($status=='0'?"checked":""); ?> <?php echo ($status==''?"checked":"") ?> id="status_0" value="0" />&nbsp;<?php _e("On Hold", 'mailing-group-module'); ?></p>
                                <p class="ltfloat"><input type="radio" <?php echo ($status=='1'?"checked":""); ?> name="status" id="status_1" value="1" <?php if($act!="upd") { ?>disabled="disabled"<?php } ?> />&nbsp;<?php _e("Active", 'mailing-group-module'); ?></p>
                            </div>
                        </div>
                        <div class="clearbth"></div>
                        <p><i>NB: On Hold is the default status for new members until they confirm their subscription by clicking the opt-in link sent by email. To make a new member active immediately, select the "Skip opt-in confirmation" option above. Please ONLY do this if you have received permission from the new member, or you may be contravening laws on Unsolicited Email.</i></p><div class="clearbth"></div>
                        <?php if($act=='upd') { ?>
                        	<p><i>NB: To unsubscribe a member from a Mailing Group, de-select the checkbox next to the group name and click Update Member.</i></p>
                        <?php } ?>
                        <div class="clearbth"></div>
                        <p class="submit">
                            <input type="submit" value="<?php echo $btn; ?>" class="button" id="submit" name="submit"/>
                            <input type="hidden" name="addme" value="<?php echo $hidval;?>" >
                            <input type="hidden" name="id" value="<?php echo $id;?>" >
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>