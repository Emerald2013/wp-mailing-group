<?php
/* get all variables */
$actreq = (isset($_REQUEST["act"])? sanitize_text_field($_REQUEST["act"]): '');
$info   = (isset($_REQUEST["info"])? sanitize_text_field($_REQUEST["info"]): '');
$delid  = (isset($_GET["did"])? sanitize_text_field($_GET["did"]): '');
$id     = (isset($_GET["id"])? sanitize_text_field($_GET["id"]): '');
/* get all variables */
if($actreq=='vis') {
	$myFields=array("status");
	$_ARR['id'] = $id;
	$_ARR['status'] = '1';
	$objMem->updRow($table_name_message,$_ARR, $myFields);
	wpmg_redirectTo("wpmg_mailinggroup_adminmessagelist&info=vis");
	exit;
} else if($actreq=='hid') {
	$myFields=array("status");
	$_ARR['id'] = $id;
	$_ARR['status'] = '0';
	$objMem->updRow($table_name_message,$_ARR, $myFields);
	wpmg_redirectTo("wpmg_mailinggroup_adminmessagelist&info=hid");
	exit;
}
if($info=="saved") {
	wpmg_showmessages("updated", __("Message has been added successfully.", 'mailing-group-module'));
} else if($info=="upd") {
	wpmg_showmessages("updated", __("Message has been updated successfully.", 'mailing-group-module'));
} else if($info=="vis") {
	wpmg_showmessages("updated", __("Message has been set to visible successfully.", 'mailing-group-module'));
} else if($info=="hid") {
	wpmg_showmessages("updated", __("Message has been  set to hidden successfully.", 'mailing-group-module'));
} else if($info=="del") {
	$wpdb->query("delete from ".$table_name_message." where id=".$delid);
	wpmg_showmessages("updated", __("Message has been deleted successfully.", 'mailing-group-module'));
}
$WPMG_SETTINGS = get_option("WPMG_SETTINGS");
$plugintype = $WPMG_SETTINGS["MG_PLUGIN_TYPE"];
$result = $objMem->selectRows($table_name_message, "",  " where message_type!='' order by id asc");
$totcount = count($result);
?>
<script type="text/javascript">
	/* <![CDATA[ */
	jQuery(document).ready(function() {
		/* Build the DataTable with third column using our custom sort functions */
		jQuery("#toplevel_page_mailinggroup_intro").removeClass('wp-not-current-submenu');
		jQuery("#toplevel_page_mailinggroup_intro").addClass('wp-has-current-submenu');
		jQuery(".toplevel_page_mailinggroup_intro").removeClass('wp-not-current-submenu');
		jQuery(".toplevel_page_mailinggroup_intro").addClass('wp-has-current-submenu');
		jQuery("#toplevel_page_mailinggroup_intro ul li.wp-first-item").addClass("current");
	} );
	/* ]]> */
</script>
<?php
if ($totcount>0) {
?>
<?php } ?>
<div class="wrap">
	<h2 class="nav-tab-wrapper">
        <a href="admin.php?page=wpmg_mailinggroup_intro" title="<?php _e("Introduction", 'mailing-group-module'); ?>" class="nav-tab"><?php _e("Introduction", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_messagelist" class="nav-tab" title="<?php _e("Custom Messages", 'mailing-group-module'); ?>"><?php _e("Custom Messages", 'mailing-group-module'); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_adminmessagelist" class="nav-tab nav-tab-active" title="<?php _e("Admin Messages", 'mailing-group-module'); ?>"><?php _e("Admin Messages", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_style" class="nav-tab" title="<?php _e("Stylesheet", 'mailing-group-module'); ?>"><?php _e("Stylesheet", 'mailing-group-module'); ?></a>
        <?php if($plugintype=='PAID') { ?>
        	<a href="admin.php?page=wpmg_mailinggroup_contact" class="nav-tab" title="<?php _e("Contact", 'mailing-group-module'); ?>"><?php _e("Contact", 'mailing-group-module'); ?></a>
        <?php } ?>
        <a href="admin.php?page=wpmg_mailinggroup_help" class="nav-tab" title="<?php _e("Help", 'mailing-group-module'); ?>"><?php _e("Help", 'mailing-group-module'); ?></a>
    </h2>
    <div>&nbsp;</div>
    <!--<a class="button add-new-h2" href="admin.php?page=wpmg_mailinggroup_adminmessageadd&act=add"><?php _e("New custom message", 'mailing-group-module'); ?></a>--></h2>
    <p>The following emails are sent out automatically by the plugin when the events described in the title column occur. You are welcome to customise the text according to your preference. The available dynamic variables are shown when you click to edit a message.</p>
	 <table class="wp-list-table widefat fixed" id="messagelist">
		<thead>
			<tr role="row" class="topRow">
				<th width="35%" class="sort topRow_messagelist"><a href="#"><?php _e("Title", 'mailing-group-module'); ?></a></th>
                <th width="35%"><?php _e("Message", 'mailing-group-module'); ?></th>
                <th width="20%" class="hidetd"><?php _e("Hidden/Visible", 'mailing-group-module'); ?></th>
				<th width="8%"><?php _e("Actions", 'mailing-group-module'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
		if ($totcount > 0 )
		{
			foreach ($result as $row)
			{
				
				/* //echo count($row);
				//echo "<pre>";print_r($row);echo "</pre>"; */
				$id = $row->id;
				$title = wpmg_dbStripslashes($row->title);
				$desc = wpmg_stringlength(wpmg_nl2brformat(wpmg_dbStripslashes($row->description)),50);
				$status = $row->status;
				$act = "hid";
				$lablestatus = __("Visible", 'mailing-group-module');
				if($status==0) {
					$act = "vis";
					$lablestatus = __("Hidden", 'mailing-group-module');
				}
	?>
			<tr>
				<td width="40%"><?php echo $title; ?></td>
                <td width="40%"><?php echo $desc; ?></td>
                <td width="15%" class="hidetd"><a href="admin.php?page=wpmg_mailinggroup_messagelist&act=<?php echo $act; ?>&id=<?php echo $id;?>"><?php echo $lablestatus; ?></a></td>
				<td width="10%" class="last"><a href="admin.php?page=wpmg_mailinggroup_adminmessageadd&act=upd&id=<?php echo $id;?>" class="edit_record" title="<?php _e("Edit", 'mailing-group-module'); ?>"></a></td>
			</tr>
<?php }
	} else { ?>
			<tr>
				<td colspan="3" align="center"><?php _e("No Message Found!", 'mailing-group-module'); ?></td>
			<tr>
	<?php } ?>
	</tbody>
	</table>
</div>