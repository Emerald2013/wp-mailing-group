<?php
/* get all variables */
$addme = (isset($_POST["addme"]) ? sanitize_text_field($_POST["addme"]) : '');
$gid   = (isset($_REQUEST["gid"])? sanitize_text_field($_REQUEST["gid"]): '');
$recid = get_current_user_id();
$unsid = (isset($_REQUEST["unsid"])? sanitize_text_field($_REQUEST["unsid"]): '');
$info  = (isset($_REQUEST["info"]) ? sanitize_text_field($_REQUEST["info"]): '');
$act   = (isset($_REQUEST["act"])  ? sanitize_text_field($_REQUEST["act"]): '');
$_POST = stripslashes_deep( $_POST );
/* get all variables */
if($recid!='') {
	$result = get_userdata( $recid );
	if ($result) {		
		$id = $result->ID;
		get_user_meta($id, "Group_subscribed", true);
		$group_name  = unserialize(get_user_meta($id, "Group_subscribed", true));
		$btn = __("Update", 'mailing-group-module');
		$hidval = 2;
	}
} else if($act=="uns" && $unsid!='') {
	$group_arr_old = unserialize(get_user_meta($recid, "Group_subscribed", true));
	unset($group_arr_old[$unsid]);
	$grpserial = serialize($group_arr_old);
	update_user_meta( $recid, "Group_subscribed", $grpserial );
	$objMem->updUserGroupTaxonomy($table_name_user_taxonomy, $recid, $group_arr_old);
	wpmg_redirectTo("wpmg_mailinggroup_membergroups&info=uns");
	exit;
}
if($addme==2) {
	$grpsArray = $objMem->getGroupSerialized($_POST);
	$grpserial = serialize($grpsArray);
	update_user_meta( $recid, "Group_subscribed", $grpserial );
	$objMem->updUserGroupTaxonomy($table_name_user_taxonomy, $recid, $grpsArray);
	wpmg_redirectTo("wpmg_mailinggroup_membergroups&info=upd");
}
if($info=="uns") {
	wpmg_showmessages("updated", __("Member has been unsubcribed from the group.", 'mailing-group-module'));
} else if($info=="upd") {
	wpmg_showmessages("updated", __("You have succesfully updated your groups settings.", 'mailing-group-module'));
}
$email_format="";
$result_groups = $objMem->selectRows($table_name_group, "", " where visibility='1' order by id asc");
?>
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
	} );
	/* ]]> */
</script>
<style>
.dataTables_info {
	display:none;
}
.check_div {
	width:800px;
}
</style>
<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub">
	<div class="icon32" id="icon-edit"><br/></div>
    <h2><?php _e("Update Group Subscribed", 'mailing-group-module'); ?></h2>
    <div id="col-left">
    	<p>If you would like to unsubscribe from a mailing group, please uncheck the box next to its name, and click Update.</p>
        <div class="col-wrap">
            <div>
                <div class="form-wrap">
                    <form class="validate" action="" method="post" id="addmember">
                        <div class="form-field">
                            <label for="tag-name"><?php _e("Group Name", 'mailing-group-module'); ?> : </label>
                            <div class="check_div">
                            	<table class="wp-list-table widefat fixed" id="memberaddedit">
                                	<thead>
                                        <tr role="row" class="topRow">
                                            <th class="sort topRow_messagelist"><?php _e("Mailing Group Name", 'mailing-group-module'); ?></th>
                                            <th><?php _e("Subscription Status", 'mailing-group-module'); ?></th>
                                            <th><?php _e("Email Format", 'mailing-group-module'); ?></th>
                                            <?php if(isset($act) && $act=='upd') { ?>
                                            	<th><?php _e("Unsubscribe from this group", 'mailing-group-module'); ?></th>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
									<?php
									foreach($result_groups as $group) {
										$checkSelected = false;
										if(count($group_name)>0) {
											if(array_key_exists($group->id,(array)$group_name)) {
												$checkSelected = true;
											}
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
                                                    <div class="lft"><input type="radio" name="email_format_<?php echo $group->id; ?>" <?php echo ($group_name[$group->id]=='1'?"checked":"") ?> value="1" />&nbsp;<?php _e("HTML", 'mailing-group-module'); ?></div>
                                                    <div class="rgt"><input type="radio" <?php echo ($group_name[$group->id]=='2'?"checked":(count($group_name)=='0'?"checked":(!isset($group_name[$group->id])?"checked":""))) ?> name="email_format_<?php echo $group->id; ?>" value="2" />&nbsp;<?php _e("Plain Text", 'mailing-group-module'); ?></div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    	</tbody>
                            	</table>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <p class="submit">
                            <input type="submit" value="<?php echo $btn; ?>" class="button" id="submit" name="submit"/>
                            <input type="hidden" name="addme" value=<?php echo $hidval;?> >
                            <input type="hidden" name="id" value=<?php echo $id;?> >
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>