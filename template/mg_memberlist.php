<?php

/* get all variables */

$info   = (isset($_REQUEST["info"])? sanitize_text_field($_REQUEST["info"]): '');

$gid    = (isset($_REQUEST["gid"])? sanitize_text_field($_REQUEST["gid"]): '');

$actreq = (isset($_REQUEST["act"])? sanitize_text_field($_REQUEST["act"]): '');

$id     = (isset($_REQUEST["id"])? sanitize_text_field($_REQUEST["id"]): '');

$delid  = (isset($_GET["did"])? sanitize_text_field($_GET["did"]): '');

/* get all variables */

if($gid=="") {

	wpmg_redirectTo("wpmg_mailinggroup_list");

}

if($info=="saved") {

	wpmg_showmessages("updated", __("Member has been added successfully.", 'mailing-group-module'));

} else if($info=="upd") {

	wpmg_showmessages("updated", __("Member has been updated successfully.", 'mailing-group-module'));

} else if($info=="del") {

	delete_user_meta( $delid, "Plugin" );

	delete_user_meta( $delid, "User_status" );

	delete_user_meta( $delid, "Group_subscribed" );

	$wpdb->query("delete from ".$table_name_user_taxonomy." where user_id=".$delid);
	
	$message = "Member has been deleted successfully from mailinggroup";
	
		if(isset($_GET['delfrmwp'])){
		
			wp_delete_user( $delid );
			
			$message = "Member has been successfully deleted from wordpress and mailinggroup";
		
		}
			
			

	wpmg_showmessages("updated", __($message, 'mailing-group-module'));

}

if($actreq=='hold') {

	update_user_meta( $id, "User_status", '0', '1' );

	wpmg_redirectTo("wpmg_mailinggroup_memberlist&info=vis&gid=".$gid);

	exit;

} else if($actreq=='active') {

	update_user_meta( $id, "User_status", '1', '0' );

	wpmg_redirectTo("wpmg_mailinggroup_memberlist&info=hid&gid=".$gid);

	exit;

}

$result = $objMem->selectRows($table_name_user_taxonomy, "",  " where group_id='".$gid."' order by id desc");

$totcount = count($result);

if ($totcount>0) {

?>

<script type="text/javascript">

	/* <![CDATA[ */

	jQuery(document).ready(function() {

		/* Build the DataTable with third column using our custom sort functions */

		<?php if(count($result)>0) { ?>

		jQuery('#memberlist').dataTable( {

			"aoColumnDefs": [ 

			  { "bSortable": false, "aTargets": [ 3,4,5 ] },

			],

			"oLanguage": {

			  "sZeroRecords": "<?php _e("No members found.", 'mailing-group-module'); ?>"

			},

			"fnDrawCallback":function(){

				if('<?php echo $totcount; ?>'<=5){

					document.getElementById('memberlist_paginate').style.display = "none";

				} else {

					document.getElementById('memberlist_paginate').style.display = "block";

				}

			}

		} );

		<?php } ?>

		jQuery("#toplevel_page_mailinggroup_intro").removeClass('wp-not-current-submenu');

		jQuery("#toplevel_page_mailinggroup_intro").addClass('wp-has-current-submenu');

		jQuery("#toplevel_page_mailinggroup_intro ul :nth-child(3)").addClass("current");

	} );

	/* ]]> */

</script>

<?php

}

$resultgp = $objMem->selectRows($table_name_group, "",  " where id='".$gid."'");

if (count($resultgp)>0) {

	foreach ($resultgp as $rowgp) {

		$groupName = $rowgp->title;

	}

}

?>

<script type="text/javascript">

	jQuery(document).ready(function() {

		jQuery("#toplevel_page_mailinggroup_intro").removeClass('wp-not-current-submenu');

		jQuery("#toplevel_page_mailinggroup_intro").addClass('wp-has-current-submenu');

		jQuery("#toplevel_page_mailinggroup_intro ul :nth-child(3)").addClass("current");

	} );

</script>

<div class="wrap">

    <h2><?php _e("Member Manager", 'mailing-group-module'); ?> <?php echo ($groupName!=''?"($groupName) <a class='backlink' href='admin.php?page=wpmg_mailinggroup_list'>". __("Back", 'mailing-group-module')."</a>":"") ?>

	<a class="button add-new-h2" href="admin.php?page=wpmg_mailinggroup_memberadd&act=add&gid=<?php echo $gid; ?>"><?php _e("Add New Member", 'mailing-group-module'); ?></a></h2>

	 <table class="wp-list-table widefat fixed" id="memberlist">

		<thead>

			<tr role="row" class="topRow" id="memberlistdata">

				<th class="sort topRow_messagelist"><a href="#"><?php _e("Name", 'mailing-group-module'); ?></a></th>

                <th><a href="#"><?php _e("Username", 'mailing-group-module'); ?></a></th>

                <th><a href="#"><?php _e("Email Address", 'mailing-group-module'); ?></a></th>

                <th><?php _e("No of Email Bounces", 'mailing-group-module'); ?></th>

                <th><?php _e("Status", 'mailing-group-module'); ?></th>

				<th width="10%"><?php _e("Actions", 'mailing-group-module'); ?></th>

			</tr>

		</thead>

		<tbody>

<?php

		

		if ($totcount>0) {

			foreach ($result as $row) {

				$userId = $row->user_id;

				

				$Userrow = get_user_by("id", $userId);

				

				$user_login = $Userrow->user_login;

				$user_email = $Userrow->user_email;

				$display_name = $Userrow->first_name;

				$status = get_user_meta($userId, "User_status", true);

				

				$mailbounceresult = 0;

				$mailbounceresult = $objMem->selectRows($table_name_sent_emails, "",  " where user_id = '".$userId."' and status='2'");

				$noofemailb = count($mailbounceresult);

				

				$act = "hold";

				

				$lablestatus = __("Active", 'mailing-group-module');

				$labledetail = __("click to put On Hold", 'mailing-group-module');

				if($status==0) {

					$act = "active";

					$lablestatus = __("On Hold", 'mailing-group-module');

					$labledetail = __("click to Activate", 'mailing-group-module');

				}

?>				<tr>

					<td><?php echo $display_name; ?></td>

					<td><?php echo $user_login; ?></td>

					<td><?php echo $user_email; ?></td>

					<td><?php echo $noofemailb; ?></td>

					<td><?php echo $lablestatus; ?> (<a href="admin.php?page=wpmg_mailinggroup_memberlist&act=<?php echo $act; ?>&id=<?php echo $userId;?>&gid=<?php echo $gid;?>"><?php echo $labledetail; ?></a>)</td>

					<td width="20%" class="last"><a href="admin.php?page=wpmg_mailinggroup_memberadd&act=upd&id=<?php echo $userId;?>&gid=<?php echo $gid;?>" class="edit_record" title="<?php _e("Edit", 'mailing-group-module'); ?>"></a><?php if($Userrow->roles[0]!='administrator') { ?>|<a href="admin.php?page=wpmg_mailinggroup_memberlist&info=del&did=<?php echo $userId;?>&gid=<?php echo $gid;?>" onclick="return confirm('<?php _e("Are you sure you want to delete this member?", 'mailing-group-module'); ?>');" class="delete_record" title="Remove User from this Mailing List"></a>|<a href="admin.php?page=wpmg_mailinggroup_memberlist&info=del&delfrmwp=1&did=<?php echo $userId;?>&gid=<?php echo $gid;?>" onclick="return confirm('<?php _e("Are you sure you want to delete this member from wordpress altogether?", 'mailing-group-module'); ?>');" class="total_del_user" title="Delete User Completely from Website"></a><?php } ?>|<a href="admin.php?page=wpmg_mailinggroup_adminarchive&uid=<?php echo $userId;?>" class="archive_messages" title="<?php _e("View Archived Messages", 'mailing-group-module'); ?>"></a></td>

				</tr>

	<?php

    		}

		} else { ?>

			<tr>

				<td colspan="6" align="center"><?php _e("No members found.", 'mailing-group-module'); ?></td>

			<tr>

	<?php } ?>

	</tbody>

	</table>

</div>