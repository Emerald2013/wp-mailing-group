<?php
/* get all variables */
$WPMG_SETTINGS = get_option("WPMG_SETTINGS");
$plugintype   = $WPMG_SETTINGS["MG_PLUGIN_TYPE"];
$addme = (isset($_POST["addme"]) ? sanitize_text_field($_POST["addme"]): '');
$act   = (isset($_REQUEST["act"])? sanitize_text_field($_REQUEST["act"]): '');
$recid = (isset($_REQUEST["id"]) ? sanitize_text_field($_REQUEST["id"]): '');
$_POST = stripslashes_deep( $_POST );
/* get all variables */
$myFields=array("id","title","description","status");
if($addme==1) {
	$objMem->addNewRow($table_name_message,$_POST, $myFields);
	wpmg_redirectTo("wpmg_mailinggroup_messagelist&info=saved");
	exit;
} else if($addme==2) {
	$objMem->updRow($table_name_message,$_POST, $myFields);
	wpmg_redirectTo("wpmg_mailinggroup_messagelist&info=upd");
	exit;
}
if($act=="upd") {
	$result = $objMem->selectRows($table_name_message, $recid);
	if (count($result) > 0 ) {
		foreach($result as $row) {
			$id        = $row->id;
			$title      = wpmg_dbStripslashes($row->title);
			$description  = wpmg_dbStripslashes($row->description);
			$status  = $row->status;
			$btn	   = __("Update Message", 'mailing-group-module');
			$hidval	   = 2;
		}
	}
} else {
	$btn	   = __("Submit", 'mailing-group-module');
	$id        = "";
	$title 	   = (isset($_POST['title']) && $_POST['title']!=''?sanitize_text_field($_POST['title']):"");
	$description = (isset($_POST['description']) && $_POST['description']!=''?sanitize_text_field($_POST['description']):"");
	$status    = (isset($_POST['status']) && $_POST['status']!=''?sanitize_text_field($_POST['status']):"");
	$add       = "";
	$hidval	   = 1;
}
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#toplevel_page_mailinggroup_intro").removeClass('wp-not-current-submenu');
		jQuery("#toplevel_page_mailinggroup_intro").addClass('wp-has-current-submenu');
		jQuery(".toplevel_page_mailinggroup_intro").removeClass('wp-not-current-submenu');
		jQuery(".toplevel_page_mailinggroup_intro").addClass('wp-has-current-submenu');
		jQuery("#toplevel_page_mailinggroup_intro ul li.wp-first-item").addClass("current");
	});
</script>
<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub">
	<h2 class="nav-tab-wrapper">
        <a href="admin.php?page=wpmg_mailinggroup_intro" title="<?php _e("Introduction", 'mailing-group-module'); ?>" class="nav-tab"><?php _e("Introduction", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_messagelist" class="nav-tab nav-tab-active" title="<?php _e("Custom Messages", 'mailing-group-module'); ?>"><?php _e("Custom Messages", 'mailing-group-module'); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_adminmessagelist" class="nav-tab" title="<?php _e("Admin Messages", 'mailing-group-module'); ?>"><?php _e("Admin Messages", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_style" class="nav-tab" title="<?php _e("Stylesheet", 'mailing-group-module'); ?>"><?php _e("Stylesheet", 'mailing-group-module'); ?></a>
        <?php if($plugintype=='PAID') { ?>
        	<a href="admin.php?page=wpmg_mailinggroup_contact" class="nav-tab" title="<?php _e("Contact", 'mailing-group-module'); ?>"><?php _e("Contact", 'mailing-group-module'); ?></a>
        <?php } ?>
        <a href="admin.php?page=wpmg_mailinggroup_help" class="nav-tab" title="<?php _e("Help", 'mailing-group-module'); ?>"><?php _e("Help", 'mailing-group-module'); ?></a>
    </h2>
    <div>&nbsp;</div>
	<div class="icon32" id="icon-edit"><br/></div>
    <h2><?php _e("Add/Edit Message", 'mailing-group-module'); ?> <a class='backlink' href='admin.php?page=wpmg_mailinggroup_messagelist'><?php _e("Back to Custom Messages", 'mailing-group-module') ?></a></h2>
    <div id="col-left">
        <div class="col-wrap">
            <div>
                <div class="form-wrap">
                    <form class="validate" action="" method="post" id="addmessage">
                        <div class="form-field">
                            <label for="tag-name"><?php _e("Title", 'mailing-group-module'); ?> : </label>
                            <input type="text" size="40" id="title" name="title" value="<?php echo $title; ?>"/>
                        </div>
    					<div class="form-field">
                            <label for="tag-name"><?php _e("Description", 'mailing-group-module'); ?> : </label>
                            <textarea name="description" rows="8" cols="50" id="description"><?php echo $description; ?></textarea>
                        </div>
                        <div class="form-field">
                            <label for="tag-name"><?php _e("Visibility", 'mailing-group-module'); ?> : </label>
                            <select name="status" id="status">
                            	<option value=""><?php _e("Select", 'mailing-group-module'); ?></option>
                                <option value="0" <?php echo ($status=='0'?"selected":""); ?>><?php _e("Hidden", 'mailing-group-module'); ?></option>
                                <option value="1" <?php echo ($status=='1'?"selected":""); ?>><?php _e("Visible", 'mailing-group-module'); ?></option>
                            </select>
                        </div>
                        <div class="form-field">
                        	<label for="tag-name"><?php _e("Available variables", 'mailing-group-module'); ?> : </label>
                            <div class="variableslist">
                                <p>{%name%} = <?php _e("User's Name", 'mailing-group-module'); ?>,</p>
                                <p>{%email%} = <?php _e("User's Email", 'mailing-group-module'); ?></p>
                                <p>{%site_email%} = <?php _e("Site's Email", 'mailing-group-module'); ?></p>
                                <p>{%site_title%} = <?php _e("Site's Title", 'mailing-group-module'); ?></p>
                                <p>{%site_url%} = <?php _e("Site's Web Address", 'mailing-group-module'); ?></p>
                                <p>{%group_name%} = <?php _e("Current Group Name", 'mailing-group-module'); ?></p>
                            </div>
                        </div>
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