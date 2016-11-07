<?php
/* get all variables */
/* get all variables */
$addme = (isset($_POST["addme"])? sanitize_text_field($_POST["addme"]): '');
$gid   = (isset($_GET["gid"])? sanitize_text_field($_GET["gid"]): '');
$_POST = stripslashes_deep( $_POST );
/* get all variables */
$result_groups = $objMem->selectRows($table_name_group, "", " order by id asc");
$myFields=array("id","name","email","status");
if($addme==1) {
	if(!$objMem->checkRowExists($table_name_requestmanager, "email", $_POST, "")) {
		$insertId = $objMem->addNewRow($table_name_requestmanager,$_POST, $myFields);
		$objMem->addUserGroup($table_name_requestmanager_taxonomy, $insertId, $_POST);
		wpmg_redirectTo("wpmg_mailinggroup_requestmanagerlist&info=saved");
		exit;
	} else {
		$result = $objMem->selectRowsbyField($table_name_requestmanager, 'email', sanitize_email($_POST['email']));
		$objMem->updUserGroup($table_name_requestmanager_taxonomy, $result[0]->id, $_POST);
		wpmg_redirectTo("wpmg_mailinggroup_requestmanagerlist&info=upd2");
		exit;
	}
}
$btn = __("Add Subscriber", 'mailing-group-module');
$id = "";
$name = (isset($_POST['name']) && $_POST['name']!=''?sanitize_text_field($_POST['name']):"");
$email  = (isset($_POST['email']) && $_POST['email']!=''?sanitize_email($_POST['email']):"");
$add = "";
$group_name = (isset($_POST['group_name']) && $_POST['group_name']!=''?sanitize_text_field($_POST['group_name']):array());
$hidval = 1;
if($group_name=="") {
	$group_name = array();
}
?>
<style>
.dataTables_info {
	display:none;
}
.check_div {
	width:400px;
}
</style>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#toplevel_page_mailinggroup_intro").removeClass('wp-not-current-submenu');
		jQuery("#toplevel_page_mailinggroup_intro").addClass('wp-has-current-submenu');
		jQuery(".toplevel_page_mailinggroup_intro").removeClass('wp-not-current-submenu');
		jQuery(".toplevel_page_mailinggroup_intro").addClass('wp-has-current-submenu');
		jQuery("#toplevel_page_mailinggroup_intro ul :nth-child(4)").addClass("current");
	});
</script>
<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub">
	<h2 class="nav-tab-wrapper">
        <a href="admin.php?page=wpmg_mailinggroup_requestmanagerlist" title="<?php _e("Subscription Request Manager", 'mailing-group-module'); ?>" class="nav-tab"><?php _e("Subscription Request Manager", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_requestmanageradd&act=add" class="nav-tab nav-tab-active" title="<?php _e("Add New Subscriber", 'mailing-group-module'); ?>"><?php _e("Add New Subscriber", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_importuser" class="nav-tab" title="<?php _e("Import Users", 'mailing-group-module'); ?>"><?php _e("Import Users", 'mailing-group-module'); ?></a>
    </h2>
    <div id="col-left">
        <div class="col-wrap">
            <div>
            	<p>Fill out the form below to add a subscriber to a mailing group.<br>
You will then need to activate their membership via the Subscription Request Manager.<br>
They will then be sent an email to confirm that they are now a subscriber.<br>
<em>NB: Please only add subscribers here if you have their permission already.</em></p>
                <div class="form-wrap">
                    <form class="validate" action="" method="post" id="addmailingrequest">
                        <div class="form-field">
                            <label for="tag-name"><?php _e("Name", 'mailing-group-module'); ?> : </label>
                            <input type="text" size="40" id="name" name="name" value="<?php echo $name; ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="tag-name"><?php _e("Email Address", 'mailing-group-module'); ?> : </label>
                            <input type="text" size="40" id="email" name="email" value="<?php echo $email; ?>"/>
                        </div>
    					<div class="form-field">
                            <label for="tag-name"><?php _e("Group Name", 'mailing-group-module'); ?> : </label>
                            <div class="check_div">
                            	<table class="wp-list-table widefat fixed" id="subscriptionaddedit">
                                	<thead>
                                        <tr role="row" class="topRow">
                                            <th class="sort topRow_messagelist"><?php _e("Mailing Group(s)", 'mailing-group-module'); ?></th>
                                            <th><?php _e("Email Format", 'mailing-group-module'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
									<?php foreach($result_groups as $group) { ?>
                                        <tr>
                                        	<td><input type="checkbox" name="group_name[]" id="selector" value="<?php echo $group->id; ?>" <?php echo (in_array($group->id,$group_name)?"checked":($gid==$group->id?"checked":"")) ?> />&nbsp;<?php echo $group->title; ?>
                                            </td>
                                            <td>
                                            	<div class="check_div">
                                                    <div class="lft"><input type="radio" name="email_format_<?php echo $group->id; ?>" <?php echo (isset($email_format) && $email_format=='1'?"checked":($email_format==''?"checked":"")); ?> value="1" />&nbsp;<?php _e("HTML", 'mailing-group-module'); ?></div>
                                                    <div class="rgt"><input type="radio" <?php echo (isset($email_format) && $email_format=='2'?"checked":""); ?> name="email_format_<?php echo $group->id; ?>" value="2" />&nbsp;<?php _e("Plain Text", 'mailing-group-module'); ?></div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                            	</table>
                            </div>
                        </div>
                        <div class="clearbth"></div>
                        <p class="submit">
                            <input type="submit" value="<?php echo $btn; ?>" class="button" id="submit" name="submit"/>
                            <input type="hidden" name="addme" value="<?php echo $hidval;?>" >
                            <input type="hidden" name="id" value="<?php echo $id;?>" >
                            <input type="hidden" name="status" value="0" >
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>