
<?php

/* get all variables */

$info   = (isset($_REQUEST["info"])? sanitize_text_field($_REQUEST["info"]): '');

$delid  = (isset($_GET["did"])? sanitize_text_field($_GET["did"]): '');

/* get all variables */

if($info=="del") {

	$wpdb->query("delete from ".$table_name_group." where id=".$delid);

	$wpdb->query("delete from ".$table_name_requestmanager_taxonomy." where group_id=".$delid);

	wpmg_showmessages("updated", __("Mailing group has been deleted successfully.", 'mailing-group-module'));

}

$WPMG_SETTINGS = get_option("WPMG_SETTINGS");

$plugintype    = $WPMG_SETTINGS["MG_PLUGIN_TYPE"];

$websiteurl    = $WPMG_SETTINGS["MG_WEBSITE_URL"];



$result = $objMem->selectRows($table_name_group, "",  " order by id desc");



$totcount = count($result);

?>

<div id="ajaxMessages"></div>

<div class="wrap" id="mail_listing">

	<script type="text/javascript">

        jQuery(document).ready(function() {

            /* Build the DataTable with third column using our custom sort functions */

			<?php if(count($result)>0) { ?>

            //jQuery('#mailinggrouplist').dataTable();

			<?php } ?>

            jQuery('.quick_edit').click(function(){ jQuery('#mailinggrouplist > tbody  > tr').each(function() { jQuery(this).closest('tr').css("background-color","#F9F9F9"); }); jQuery(this).closest('tr').css("background-color","#FEA03D"); jQuery("#mg_loading").show(); var thisId = this.name; var data = { action: 'wpmg_addeditmailinggroup', page: 'wpmg_mailinggroup_add',id:thisId,act:"upd"}; jQuery.post(ajaxurl, data, function(response) {jQuery("#mg_loading").hide(); jQuery("#ajaxContent").html(response);});})

			jQuery('#quick_add').click(function(){ jQuery("#mg_loading").show(); var data = { action: 'wpmg_addeditmailinggroup', page: 'wpmg_mailinggroup_add',act:"add"}; jQuery.post(ajaxurl, data, function(response) {jQuery("#mg_loading").hide(); jQuery("#ajaxContent").html(response);});});});

			function showdatatable() { var data = { action: 'wpmg_mailinggrouplisting',page: 'wpmg_mailinggroup_list'}; jQuery.post(ajaxurl, data, function(response) { jQuery("#mail_listing").html(response);}); }

    </script>

    <h2><?php _e("Mailing Groups", 'mailing-group-module');?>

    <?php if( $plugintype == 'FREE' && count($result) > 0 ) { } else {?> <a class="button add-new-h2" id="quick_add" href="#"><?php _e("Add New Mailing Group", 'mailing-group-module');?></a> <?php } ?>

    </h2>

    <p>

    <?php echo sprintf( __("<p>Your Mailing Group can be added and configured below. Only one Mailing Group is available in this Free plugin. Just click 'Add New Mailing Group' to get started. The Premium plugin supports unlimited Mailing Groups for all your different group needs. Why not <a href='%s' target='_blank'>upgrade now</a>?"), $websiteurl ); ?></p>

	 <table class="wp-list-table widefat fixed" id="mailinggrouplist">

		<thead>

			<tr role="row" class="topRow">

				<th class="sort topRow_grouplist"><a href="#"><?php _e("Group Name", 'mailing-group-module');?></a></th>

                <th class="sort topRow_grouplist"><a href="#"><?php _e("Email Address", 'mailing-group-module');?></a></th>

                <th class="sort topRow_grouplist"><?php _e("Status", 'mailing-group-module');?></th>

				<th width="22%"><?php _e("Actions", 'mailing-group-module');?></th>

			</tr>

		</thead>

		<tbody>

<?php

	if ($totcount > 0 )

		{

			foreach ($result as $row)

			{

				$id = $row->id;

				$title = wpmg_dbStripslashes($row->title);

				$email = wpmg_dbStripslashes($row->email);

				$status = wpmg_dbStripslashes($row->status);

				$archive_message = wpmg_dbStripslashes($row->archive_message);

	?>

			<tr>

				<td><?php echo $title; ?></td>

                <td><?php echo $email; ?></td>

                <td><?php echo ($status == '0' ? 'Inactive' : 'Active'); ?></td>

				<td class="last">

                <a class="add_subscriber" title="<?php _e("Add Subscriber", 'mailing-group-module');?>" href="admin.php?page=wpmg_mailinggroup_memberadd&act=add&gid=<?php echo $id; ?>"></a>

                |<a class="view_users" title="<?php _e("View Members", 'mailing-group-module');?>" href="admin.php?page=wpmg_mailinggroup_memberlist&gid=<?php echo $id;?>"></a>

      |<a class="import_users" title="<?php _e("Import Users", 'mailing-group-module');?>" href="admin.php?page=wpmg_mailinggroup_importuser&gid=<?php echo $id;?>"></a>
      
                |<a class="quick_edit edit_record" title="<?php _e("Edit", 'mailing-group-module');?>" name="<?php echo $id;?>" href="#"></a>|<a class="delete_record" title="<?php _e("Delete", 'mailing-group-module');?>" href="admin.php?page=wpmg_mailinggroup_list&info=del&did=<?php echo $id;?>" onclick="return confirm('<?php _e("Are you sure you want to delete this group?", 'mailing-group-module');?>');"></a></td>

			</tr>

<?php }

	} else { ?>

			<tr>

				<td colspan="3" align="center"><?php _e("Click 'Add New Mailing Group' to get started", 'mailing-group-module');?></td>

			<tr>

	<?php } ?>

	</tbody>

	</table>
    <div id="mg_loading" class="mg_loading" style="display:none;"></div>
	<div id="ajaxContent" class="ajaxContent"></div>

</div>