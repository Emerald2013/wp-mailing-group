<?php
$WPMG_SETTINGS = get_option("WPMG_SETTINGS");
/* get all variables */
$addme = (isset($_POST["addme"])? sanitize_text_field($_POST["addme"]): '');
$info  = (isset($_REQUEST["info"])? sanitize_text_field($_REQUEST["info"]): '');
$_POST = stripslashes_deep( $_POST );
/* get all variables */
$subscriptioncheck = $WPMG_SETTINGS["MG_SUBSCRIPTION_REQUEST_CHECK"];

$substr = "";
if(isset($a['visibility'])){
	$checkFor = $visibilityArray[$a['visibility']];
	$substr = " AND visibility = '$checkFor'";
}else{
	$substr = " AND visibility = '1'";
}

$result_groups = $objMem->selectRows($table_name_group, "", " where status = '1' $substr order by id asc");
$myFields=array("id","name","email","status");
if(isset($_POST['submit'])) {
	if(sanitize_text_field($_POST['c_captcha'])!='' && (sanitize_text_field($_POST['c_captcha'])==sanitize_text_field($_SESSION['HUE_CAPCHA']))) {
		$_POST['name'] = sanitize_text_field($_POST['fname']);
		if(!$objMem->checkRowExists($table_name_requestmanager, "email", $_POST, "")) {
			$insertId = $objMem->addNewRow($table_name_requestmanager,$_POST, $myFields);
			$objMem->addUserGroup($table_name_requestmanager_taxonomy, $insertId, $_POST);
			if($subscriptioncheck=='1') {
				wpmg_sendmessagetoAdmin(sanitize_text_field($_POST['fname']),sanitize_email($_POST['email']), implode(",",$_POST['group_name']));
			}
			wpmg_redirectTo("?info=saved","front");
			exit;
		} else {
			wpmg_showmessages("error", __("User with email address already exists, please contact administrator for more info.", 'mailing-group-module'));
		}
	} else {
		wpmg_showmessages("error", __("Invalid captcha code, Please try again.", 'mailing-group-module'));
	}
} else if($info=="saved") {
	wpmg_showmessages("updated", __("Thank you for your request to join our mailing group(s). You will be informed once a moderator has reviewed your request.", 'mailing-group-module'));
}
$id = "";
$fname  = (isset($_POST['fname']) && $_POST['fname']!=''?sanitize_text_field($_POST['fname']):"");
$email  = (isset($_POST['email']) && $_POST['email']!=''?sanitize_email($_POST['email']):"");
$add = "";
$group_name= (isset($_POST['group_name']) && $_POST['group_name']!=''?$_POST['group_name']:array());

$hidval = 1;
if($group_name=="") {
	$group_name = array();
}
?>
<style>
.updated{display:inline !important;}
<?php echo $custom_style = $WPMG_SETTINGS["MG_CUSTOM_STYLESHEET"]; ?>
</style>
<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub">
	<div class="icon32" id="icon-edit"><br/></div>
    <div id="col-left">
        <div class="col-wrap">
            <div class="user_form_div">
                <div class="form-wrap">
                    <form class="validate" action="" method="post" id="mailingrequestform">
                        <div class="form-field">
                            <label for="tag-name"><?php _e("Name", 'mailing-group-module'); ?> : </label>
                            <input type="text" size="40" id="fname" name="fname" value="<?php echo $fname; ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="tag-name"><?php _e("Email Address", 'mailing-group-module'); ?> : </label>
                            <input type="text" size="40" id="email" name="email" value="<?php echo $email; ?>"/>
                        </div>
    					<div class="outer_group_div">
                            <div class="check_div_fir">
                                <p class="inner_check_imp"><?php _e("Mailing Group", 'mailing-group-module'); ?> :</p>
                            </div>
                            <div class="check_div_imp">
                            <?php $groupCount = count($result_groups); 
								if($groupCount>0){
								foreach($result_groups as $group) { ?>
                                <p class="inner_check_imp_group"><input type="checkbox" name="group_name[]" id="selector" value="<?php echo $group->id; ?>" <?php echo (in_array($group->id,$group_name)?"checked":""); ?> />&nbsp;<?php echo $group->title; ?></p>
                            <?php }}else{ _e("No group available", 'mailing-group-module'); }?>
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="tag-name"><?php _e("Captcha", 'mailing-group-module'); ?> : </label>
                            <img src="<?php echo WPMG_PLUGIN_URL.'/lib/captcha.php'; ?>">
                            <input type="text" size="40" id="c_captcha" name="c_captcha" value=""/>
                        </div>
                        <div class="form-field">
                            <p class="submit">
                                <input type="submit" value="Subscribe" class="button" id="submit" name="submit"/>
                                <input type="hidden" name="addme" value="<?php echo $hidval;?>" >
                                <input type="hidden" name="id" value="<?php echo $id;?>" >
                                <input type="hidden" name="status" value="0" >
                            </p>
                         </div>
                         <div class="clearbth"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>