<?php
$WPMG_SETTINGS  = get_option("WPMG_SETTINGS");
$websiteurl     = $WPMG_SETTINGS["MG_WEBSITE_URL"];
$contactaddress = $WPMG_SETTINGS["MG_CONTACT_ADDRESS"];
$supportemail   = $WPMG_SETTINGS["MG_SUPPORT_EMAIL"];
$contactphone   = $WPMG_SETTINGS["MG_SUPPORT_PHONE"];
$plugintype     = $WPMG_SETTINGS["MG_PLUGIN_TYPE"];

?>
<div class="wrap">
	<h2 class="nav-tab-wrapper">
        <a href="admin.php?page=wpmg_mailinggroup_intro" title="<?php _e("Introduction", 'mailing-group-module'); ?>" class="nav-tab"><?php _e("Introduction", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_messagelist" class="nav-tab" title="<?php _e("Custom Messages", 'mailing-group-module'); ?>"><?php _e("Custom Messages", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_style" class="nav-tab" title="<?php _e("Stylesheet", 'mailing-group-module'); ?>"><?php _e("Stylesheet", 'mailing-group-module'); ?></a>
        <?php if($plugintype=='PAID') { ?>
        	<a href="admin.php?page=wpmg_mailinggroup_contact" class="nav-tab nav-tab-active" title="<?php _e("Contact", 'mailing-group-module'); ?>"><?php _e("Contact", 'mailing-group-module'); ?></a>
        <?php } ?>
        <a href="admin.php?page=wpmg_mailinggroup_help" class="nav-tab" title="<?php _e("Help", 'mailing-group-module'); ?>"><?php _e("Help", 'mailing-group-module'); ?></a>
    </h2>
    <div>
    	<h3><?php _e("Our Address", 'mailing-group-module'); ?></h3>
    	<?php echo $contactaddress; ?>
    </div>
    <div>
    	<h3><?php _e("Support", 'mailing-group-module'); ?></h3>
        <p><?php _e("You can contact us on following email address in case you need any asistance.", 'mailing-group-module');?></p>
    	<p><h3><?php echo $supportemail; ?></h3></p>
        <p>Or you can call us on the following Phone number.</p>
        <p><h3><?php echo $contactphone; ?></h3></p>
    </div>
    <div>
    	<?php echo sprintf( __('<p>This plugin was developed to add functionality in wordpress to manager multiple mailing groups for users.This plugin was developed to add functionality in wordpress to manager multiple mailing groups for users.This plugin was developed to add functionality in wordpress to manager multiple mailing groups for users.This plugin was developed to add functionality in wordpress to manager multiple mailing groups for users.<br /><br />This plugin was developed to add functionality in wordpress to manager multiple mailing groups for users.This plugin was developed to add functionality in wordpress to manager multiple mailing groups for users.This plugin was developed to add functionality in wordpress to manager multiple mailing groups for users.This plugin was developed to add functionality in wordpress to manager multiple mailing groups for users.<br /><br />This plugin was developed to add functionality in wordpress to manager multiple mailing groups for users.This plugin was developed to add functionality in wordpress to manager multiple mailing groups for users.This plugin was developed to add functionality in wordpress to manager multiple mailing groups for users.This plugin was developed to add functionality in wordpress to manager multiple mailing groups for users.</p><p><a target="_blank" href="%s">More info on website</a></p>'), $websiteurl ); ?>
    </div>
</div>