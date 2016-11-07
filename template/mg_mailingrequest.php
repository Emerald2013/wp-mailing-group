<?php
/* get all variables */
$actreq = (isset($_REQUEST["act"])? sanitize_text_field($_REQUEST["act"]): '');
$UpdId  = (isset($_GET["id"])? sanitize_text_field($_GET["id"]): '');
$gid    = (isset($_GET["gid"])? sanitize_text_field($_GET["gid"]): '');
$delid  = (isset($_GET["did"])? sanitize_text_field($_GET["did"]): '');
$info   = (isset($_REQUEST["info"])? sanitize_text_field($_REQUEST["info"]): '');
$type   = (isset($_REQUEST["type"])? sanitize_text_field($_REQUEST["type"]): '');
/* get all variables */
if(isset($_POST['Save']) && is_array($_POST['selectusers'])) {
	foreach($_POST['selectusers'] as $key => $val) {
		$user_id = '';
		$getIds = explode("_",$val);
		$UpdId = $delid = $getIds[0];
		$gid = $getIds[1];
		$mact = ($_POST['massaction']?sanitize_text_field($_POST['massaction']):sanitize_text_field($_POST['massaction2']));
		if($mact=='1') {
			$usercount = $objMem->getGroupUserCount($table_name_user_taxonomy, $gid);
			$usercount = count($usercount);
			if($usercount>=$memberLimit) {
				$mact='3';
			} else {
				$addRequesttodb = $objMem->selectRows($table_name_requestmanager, "",  " where id = '".$UpdId."'");
				$random_password = wp_generate_password( 12, false );
				$name = $addRequesttodb[0]->name;
				$email = $addRequesttodb[0]->email;
				$username = $addRequesttodb[0]->username;
				$group_name =  $objMem->getUserGroup($table_name_requestmanager_taxonomy, $UpdId);
				if(trim($username)=="") {
					$username = $email;
				}
				$status = "1";
				$username_e = username_exists( $username );
				$email_e = email_exists($email);
				if ( !$user_id and email_exists($email) == false ) {
					$userdata = array( 
						'user_login' => $username,
						'first_name' => $name,
						'user_pass' => $random_password,
						'user_email' => $email,
						'role' => 'subscriber' );
					/* //print_r($userdata); */
					$user_id = wp_insert_user( $userdata );
					$msg = "";
					if(is_numeric($user_id)) {
						/* //echo "<br>---".$user_id; */
						wp_new_user_notification($user_id, $random_password);
						add_user_meta( $user_id, "Plugin", "groupmailing" );
						add_user_meta( $user_id, "User_status", $status );
						$gropArray = array($gid=>$group_name[$gid]);
						add_user_meta( $user_id, "Group_subscribed", serialize($gropArray) );
						$objMem->addUserGroupTaxonomy($table_name_user_taxonomy, $user_id, $gropArray);
						if(count($group_name)>1) {
							 $objMem->deleteUserGroup($table_name_requestmanager_taxonomy,$gid,$UpdId);
						} else {
							$myFields=array("status");
							$_ARR['id'] = $UpdId;
							$_ARR['status'] = $status;
							$objMem->updRow($table_name_requestmanager,$_ARR, $myFields);
						}
					}
					wpmg_sendGroupConfirmationtoMember($user_id, $gropArray);
				} else {
					if($username_e || $email_e) {
						$userId = (is_numeric($username_e)?$username_e:$email_e);
						if(is_numeric($userId)) {
							$usergroupnames = get_user_meta($userId, "Group_subscribed", true);
							$group_name_new = unserialize($usergroupnames);
							if(!in_array($gid, $group_name_new)) {
								$group_name_new[$gid] = $group_name[$gid];
							}
							update_user_meta( $userId, "Group_subscribed", serialize($group_name_new) );
							$objMem->updUserGroupTaxonomy($table_name_user_taxonomy, $userId, $group_name_new);
							if(count($group_name)>1) {
								$objMem->deleteUserGroup($table_name_requestmanager_taxonomy,$gid,$UpdId);
							} else {
								$objMem->deleteUserGroup($table_name_requestmanager_taxonomy,$gid,$UpdId);
								$myFields=array("status");
								$_ARR['id'] = $UpdId;
								$_ARR['status'] = $status;
								$objMem->updRow($table_name_requestmanager,$_ARR, $myFields);
							}
						}
					}
				}
			}
		} else if($mact=='2'){
			$addRequesttodb = $objMem->selectRows($table_name_requestmanager, "",  " where id = '".$delid."'");
			$groupArr = $objMem->getUserGroup($table_name_requestmanager_taxonomy, $delid);
			if(count($groupArr)>1) {
				$objMem->deleteUserGroup($table_name_requestmanager_taxonomy,$gid,$delid);
			} else {
				$objMem->deleteUserGroup($table_name_requestmanager_taxonomy,$gid,$delid);
				$wpdb->query("delete from ".$table_name_requestmanager." where id=".$delid);
			}
		}
	}
	wpmg_redirectTo("wpmg_mailinggroup_requestmanagerlist&info=mass&type=".$mact);
	exit;
}

if($actreq=='app') {
	$addRequesttodb = $objMem->selectRows($table_name_requestmanager, "",  " where id = '".$UpdId."'");
	$random_password = wp_generate_password( 12, false );
	$name = $addRequesttodb[0]->name;
	$email = $addRequesttodb[0]->email;
	$username = $addRequesttodb[0]->username;
	$group_name =  $objMem->getUserGroup($table_name_requestmanager_taxonomy, $UpdId);
	$usercount = $objMem->getGroupUserCount($table_name_user_taxonomy, $gid);
	$usercount = count($usercount);
	if($usercount>=$memberLimit) {
		wpmg_redirectTo("wpmg_mailinggroup_requestmanagerlist&info=free");
		exit;
	} else {
		/* //echo "<pre>";
		//print_r($group_name); */
		if(trim($username)=="") {
			$username = $email;
		}
		$status = "1";
		$username_e = username_exists( $username );
		$email_e = email_exists($email);
		if ( !$user_id and email_exists($email) == false ) {
			$userdata = array( 
				'user_login' => $username,
				'first_name' => $name,
				'user_pass' => $random_password,
				'user_email' => $email,
				'role' => 'subscriber' );
			/* //print_r($userdata); */
			$user_id = wp_insert_user( $userdata );
			/* //wp_new_user_notification($user_id, $random_password); */
			$msg = "";
			if(is_numeric($user_id)) {
				/* //echo "<br>---".$user_id; */
				wp_new_user_notification($user_id, $random_password);
				add_user_meta( $user_id, "Plugin", "groupmailing" );
				add_user_meta( $user_id, "User_status", $status );
				$gropArray = array($gid=>$group_name[$gid]);
				add_user_meta( $user_id, "Group_subscribed", serialize($gropArray) );
				$objMem->addUserGroupTaxonomy($table_name_user_taxonomy, $user_id, $gropArray);
				if(count($group_name)>1) {
					/* //echo "<br> in delete---"; */
					 $objMem->deleteUserGroup($table_name_requestmanager_taxonomy,$gid,$UpdId);
				} else {
					$myFields=array("status");
					$_ARR['id'] = $UpdId;
					$_ARR['status'] = $status;
					$objMem->updRow($table_name_requestmanager,$_ARR, $myFields);
				}
				wpmg_sendGroupConfirmationtoMember($user_id, $gropArray);
				wpmg_redirectTo("wpmg_mailinggroup_requestmanagerlist&info=app");
				exit;
			} else {
				foreach($user_id->errors as $errr) {
					$msg .= $errr[0];
				}
				wpmg_showmessages("error", $msg);
			}
		} else {
			if($username_e || $email_e) {
				$userId = (is_numeric($username_e)?$username_e:$email_e);
				if(is_numeric($userId)) {
					$usergroupnames = get_user_meta($userId, "Group_subscribed", true);
					$group_name_new = unserialize($usergroupnames);
					if(!in_array($gid, $group_name_new)) {
						$group_name_new[$gid] = $group_name[$gid];
					}
					update_user_meta( $userId, "Group_subscribed", serialize($group_name_new) );
					$objMem->updUserGroupTaxonomy($table_name_user_taxonomy, $userId, $group_name_new);
					if(count($group_name)>1) {
						$objMem->deleteUserGroup($table_name_requestmanager_taxonomy,$gid,$UpdId);
					} else {
						$objMem->deleteUserGroup($table_name_requestmanager_taxonomy,$gid,$UpdId);
						$myFields=array("status");
						$_ARR['id'] = $UpdId;
						$_ARR['status'] = $status;
						$objMem->updRow($table_name_requestmanager,$_ARR, $myFields);
					}
				}
				wpmg_sendGroupConfirmationtoMember($user_id, $gropArray);
				wpmg_redirectTo("wpmg_mailinggroup_requestmanagerlist&info=upd2");
				exit;
			}
		}
	}
} else if($actreq=='del') {
	$addRequesttodb = $objMem->selectRows($table_name_requestmanager, "",  " where id = '".$delid."'");
	$groupArr = $objMem->getUserGroup($table_name_requestmanager_taxonomy, $delid);
	if(count($groupArr)>1) {
		$objMem->deleteUserGroup($table_name_requestmanager_taxonomy,$gid,$delid);
		wpmg_redirectTo("wpmg_mailinggroup_requestmanagerlist&info=upd");
		exit;
	} else {
		$objMem->deleteUserGroup($table_name_requestmanager_taxonomy,$gid,$delid);
		$wpdb->query("delete from ".$table_name_requestmanager." where id=".$delid);
		wpmg_redirectTo("wpmg_mailinggroup_requestmanagerlist&info=del");
		exit;
	}
} else if($actreq=='delsubs') {
	$wpdb->query("delete from ".$table_name_requestmanager." where id=".$delid);
	wpmg_redirectTo("wpmg_mailinggroup_requestmanagerlist&info=delsubs");
	exit;
}
if($info=="app") {
	wpmg_showmessages("updated", __("Subscription request has been approved successfully.", 'mailing-group-module'));
} else if($info=="upd") {
	wpmg_showmessages("updated", __("Subscription request has been updated successfully.", 'mailing-group-module'));
} else if($info=="upd2") {
	wpmg_showmessages("updated", __("Subscription request was already registered, groups updated successfully.", 'mailing-group-module'));
} else if($info=="del") {
	wpmg_showmessages("updated", __("Subscription request has been rejected successfully.", 'mailing-group-module'));
} else if($info=="delsubs") {
	wpmg_showmessages("updated", __("Subscription request has been deleted successfully.", 'mailing-group-module'));
} else if($info=="saved") {
	wpmg_showmessages("updated", __("Subscription request has been added successfully. Now approve the request to activate the membership.", 'mailing-group-module'));
}  else if($info=="free") {
	wpmg_showmessages("error", __("You can only add 20 member(s) per group, Please upgrade to paid version for more features.", 'mailing-group-module'));
} else if($info=="mass") {
	if($type=='1') {
		wpmg_showmessages("updated", __("Subscription request(s) has been added successfully.", 'mailing-group-module'));
	} else if($type=='2') {
		wpmg_showmessages("updated", __("Subscription request(s) has been rejected successfully.", 'mailing-group-module'));
	} else if($type=='3') {
		wpmg_showmessages("error", __("You can only add 20 member(s) per group, Please upgrade to paid version for more features.", 'mailing-group-module'));
	}
}
$result = $objMem->selectRows($table_name_requestmanager, "",  " where status = '0' order by id desc");
$totcount = count($result);
if ($totcount>0) {
?>
<script type="text/javascript">
	jQuery(document).ready(function() {		jQuery('#mailingrequestmanager').dataTable( {			"aoColumnDefs":[{"bSortable": false, "aTargets": [ 0, 3, 4 ]}],			"fnDrawCallback":function(){				if('<?php echo $totcount; ?>'<=5){					document.getElementById('mailingrequestmanager_paginate').style.display = "none";				} else {					document.getElementById('mailingrequestmanager_paginate').style.display = "block";				}			}		});	});
</script>
<?php } ?>
<div class="wrap">
	<h2 class="nav-tab-wrapper">
        <a href="admin.php?page=wpmg_mailinggroup_requestmanagerlist" title="<?php _e("Subscription Request Manager", 'mailing-group-module'); ?>" class="nav-tab nav-tab-active"><?php _e("Subscription Request Manager", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_requestmanageradd&act=add" class="nav-tab" title="<?php _e("Add New Subscriber", 'mailing-group-module'); ?>"><?php _e("Add New Subscriber", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_importuser" class="nav-tab" title="<?php _e("Import Users", 'mailing-group-module'); ?>"><?php _e("Import Users", 'mailing-group-module'); ?></a>
    </h2>
    <div style="padding-top:20px; padding-bottom:25px;"><?php _e("Any new subscriber requests submitted via your website, or via the Add New Subscriber panel, will appear below. You need to use the plugin’s shortcode to display the subscription request form on your website - see the Help tab in the General Settings for more information.", 'mailing-group-module'); ?></div>
    <form name="approvedeleterequest" id="approvedeleterequest" method="post">
    <div class="tablenav top">
		<div class="alignleft actions">
			<select name="massaction" id="massaction">            	<option selected="selected" value=""><?php _e("Bulk actions", 'mailing-group-module'); ?></option>                <option value="1"><?php _e("Approve Selected", 'mailing-group-module'); ?></option>                <option value="2"><?php _e("Reject Selected", 'mailing-group-module'); ?></option>
            </select>
			<input type="submit" id="doaction" name="Save" value="<?php _e("Apply", 'mailing-group-module'); ?>" />
		</div>
		<br class="clear">
	</div>
	<table class="wp-list-table widefat fixed" id="mailingrequestmanager">
		<thead>
			<tr role="row" class="topRow header_tab">
            	<th width="8%">
                	<input type="checkbox" id="selectorall" name="selectorall" value="1" />
        		</th>
				<th width="25%" class="sort" style="cursor:pointer;"><a href="#"><?php _e("Name", 'mailing-group-module'); ?></a></th>
                <th class="sortemail"><a href="#"><?php _e("Email Address", 'mailing-group-module'); ?></a></th>
                <th><?php _e("Group Name", 'mailing-group-module'); ?></th>
				<th width="10%"><?php _e("Actions", 'mailing-group-module'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
		if ($totcount > 0 )
		{
			foreach ($result as $row)
			{
				$id = $row->id;
				$name = wpmg_dbStripslashes($row->name);
				$email = wpmg_dbStripslashes($row->email);
				$message_sent = $row->message_sent;
				/* $group_name = wpmg_dbStripslashes($row->group_name); */
				$status = $row->status;
				$act = "hid";
				$lablestatus = __("Visible", 'mailing-group-module');
				if($status==0) {
					$act = "vis";
					$lablestatus = __("Hidden", 'mailing-group-module');
				}
				$result_groups = $objMem->getCompleteUserGroups($table_name_requestmanager_taxonomy, $table_name_group, $id);
	?>
			<tr>
            	<td width="8%">
                	<?php
					if(count($result_groups)>0) {
						foreach($result_groups as $groups) {
					?>
							<input type="checkbox" class="selectorsubscription" id="selector" name="selectusers[]" value="<?php echo $id; ?>_<?php echo $groups->group_id;?>" id="" /><br />
                    <?php
						}
					}
					?>
                </td>
				<td width="25%"><?php echo $name; ?></td>
                <td><?php echo $email; ?></td>
                <td>
				<?php
					if(count($result_groups)>0) {
						foreach($result_groups as $groups) {
							echo wpmg_dbStripslashes($groups->title)."<br>";
						}
					}
				?></td>
				<td width="22%" class="last">
                	<?php
                    	if(count($result_groups)>0) {
							$ijk = 1;
							foreach($result_groups as $groups) {
					?>
								<a class="approve_record" title="<?php _e("Approve", 'mailing-group-module'); ?>" href="admin.php?page=wpmg_mailinggroup_requestmanagerlist&act=app&id=<?php echo $id;?>&gid=<?php echo $groups->group_id;?>" onclick="return confirm('<?php _e("Are you sure you want to approve this subscription request?", 'mailing-group-module'); ?>');"></a>|<a href="admin.php?page=wpmg_mailinggroup_requestmanagerlist&act=del&did=<?php echo $id;?>&gid=<?php echo $groups->group_id;?>" onclick="return confirm('<?php _e("Are you sure you want to reject this subscription request?", 'mailing-group-module'); ?>');" title="<?php _e("Reject", 'mailing-group-module'); ?>" class="reject_record"></a>|<a href="admin.php?page=wpmg_mailinggroup_sendmessage&act=upd&id=<?php echo $id;?>&gid=<?php echo $groups->group_id;?>&TB_iframe=true&width=550&height=530" title="<?php _e("Send Message", 'mailing-group-module'); ?>" class="send_mail thickbox"></a>
                                <?php if(count($result_groups)!==$ijk) { ?>
                                	<br />
                                <?php } ?>
					<?php
								$ijk++;
                    		}
							if($message_sent>0) {
								echo "|<a href='#' title='Messages Sent'>(".$message_sent.")</a>";
							}
						} else {
					?>
                    			<a class="reject_record" title="<?php _e("Delete", 'mailing-group-module'); ?>" href="admin.php?page=wpmg_mailinggroup_requestmanagerlist&act=delsubs&did=<?php echo $id;?>&gid=<?php echo $groups->group_id;?>" onclick="return confirm('<?php _e("Are you sure you want to delete this subscription request completely?", 'mailing-group-module'); ?>');"></a>
                    <?php
						}
					?>
                </td>
			</tr>
<?php }
	} else { ?>
			<tr>
				<td colspan="5" align="center"><?php _e("No new subscription requests", 'mailing-group-module'); ?></td>
			</tr>
	<?php } ?>
	</tbody>
	</table>
    <div class="tablenav bottom">
		<div class="alignleft actions">
			<select name="massaction2" id="massaction2">
            	<option selected="selected" value=""><?php _e("Bulk actions", 'mailing-group-module'); ?></option>                <option value="1"><?php _e("Approve Selected", 'mailing-group-module'); ?></option>                <option value="2"><?php _e("Reject Selected", 'mailing-group-module'); ?></option>
            </select>
			<input type="submit" id="doaction2" name="Save" value="<?php _e("Apply", 'mailing-group-module'); ?>" />
		</div>
		<br class="clear">
	</div>
    </form>
</div>
<?php add_thickbox(); ?>