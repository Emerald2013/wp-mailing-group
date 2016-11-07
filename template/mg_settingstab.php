<?php
$WPMG_SETTINGS = get_option("WPMG_SETTINGS");
$plugintype    = $WPMG_SETTINGS["MG_PLUGIN_TYPE"];
?>
<div class="wrap">
    <h2><?php _e("General Setting", 'mailing-group-module'); ?></h2>
    <div>
    	<h3><?php _e('Please click on below icons to manage the content.', 'mailing-group-module'); ?></h3>
    </div>
    <div id="col-left">
        <div class="col-wrap">
        	<div class="tabs_icons">
            	<div class="icon_content">
                	<a href="admin.php?page=wpmg_mailinggroup_intro" title="<?php _e("Introduction", 'mailing-group-module'); ?>"><img src="<?php echo WPMG_PLUGIN_URL.'/images/introction-icon.png'; ?>" alt="<?php _e("Introduction", 'mailing-group-module'); ?>" width="80" title="<?php _e("Introduction", 'mailing-group-module'); ?>" /></a>
                </div>
                <div class="icon_content">
                	<a href="admin.php?page=wpmg_mailinggroup_messagelist" title="<?php _e("Messages Manager", 'mailing-group-module'); ?>"><img src="<?php echo WPMG_PLUGIN_URL.'/images/message-icon.png'; ?>" alt="<?php _e("Messages Manager", 'mailing-group-module'); ?>" title="<?php _e("Messages Manager", 'mailing-group-module'); ?>" width="80" /></a>
                </div>
                <div class="icon_content">
                	<a href="admin.php?page=wpmg_mailinggroup_style" title="<?php _e("Style Manager", 'mailing-group-module'); ?>"><img src="<?php echo WPMG_PLUGIN_URL.'/images/style-icon.png'; ?>" width="80" alt="<?php _e("Style Manager", 'mailing-group-module'); ?>" title="<?php _e("Style Manager", 'mailing-group-module'); ?>" /></a>
                </div>
                <?php if($plugintype=='PAID') { ?>
                	<div class="icon_content">
                        <a href="admin.php?page=wpmg_mailinggroup_contact" title="<?php _e("Contact Info", 'mailing-group-module'); ?>"><img src="<?php echo WPMG_PLUGIN_URL.'/images/contact-info.png'; ?>" width="80" alt="<?php _e("Contact Info", 'mailing-group-module'); ?>" title="<?php _e("Contact Info", 'mailing-group-module'); ?>" /></a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>