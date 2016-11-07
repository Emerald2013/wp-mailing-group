<?php

$WPMG_SETTINGS = get_option("WPMG_SETTINGS");

if(isset($_POST['submit']) && $_POST['submit']) {

	$WPMG_SETTINGS["MG_SUBSCRIPTION_REQUEST_CHECK"] = !sanitize_text_field($_POST['alert_on_subscription'])?"0":"1";

	$WPMG_SETTINGS["MG_SUBSCRIPTION_REQUEST_ALERT_EMAIL"] = sanitize_email($_POST['subscription_email']);

	$WPMG_SETTINGS["MG_BOUNCE_CHECK"] = !sanitize_text_field($_POST['email_bounce_alert'])?"0":"1";

	$WPMG_SETTINGS["MG_BOUNCE_CHECK_ALERT_TIMES"] = sanitize_text_field($_POST['bounce_no_times']);

	$WPMG_SETTINGS["MG_BOUNCE_CHECK_ALERT_EMAIL"] = sanitize_text_field($_POST['bounce_alert_email']);



    update_option("WPMG_SETTINGS", $WPMG_SETTINGS);		

	wpmg_showmessages("updated", __("Settings have been updated successfully.", 'mailing-group-module'));

}

$versionno             = $WPMG_SETTINGS["MG_VERSION_NO"];

$plugintype            = $WPMG_SETTINGS["MG_PLUGIN_TYPE"];

$subscriptioncheck     = $WPMG_SETTINGS["MG_SUBSCRIPTION_REQUEST_CHECK"];

$subscriptionemail     = $WPMG_SETTINGS["MG_SUBSCRIPTION_REQUEST_ALERT_EMAIL"];

$bouncecheck           = $WPMG_SETTINGS["MG_BOUNCE_CHECK"];

$bouncealerttimes      = $WPMG_SETTINGS["MG_BOUNCE_CHECK_ALERT_TIMES"];

$bouncecheckalertemail = $WPMG_SETTINGS["MG_BOUNCE_CHECK_ALERT_EMAIL"];

$websiteurl            = $WPMG_SETTINGS["MG_WEBSITE_URL"];

?>

<style>

.form-table th, .form-wrap label {

	width:200px !important;

}

</style>

<div class="wrap">

	<h2 class="nav-tab-wrapper">

        <a href="admin.php?page=wpmg_mailinggroup_intro" title="<?php _e("Introduction", 'mailing-group-module'); ?>" class="nav-tab nav-tab-active"><?php _e("Introduction", 'mailing-group-module'); ?></a>

        <a href="admin.php?page=wpmg_mailinggroup_messagelist" class="nav-tab" title="<?php _e("Custom Messages", 'mailing-group-module'); ?>"><?php _e("Custom Messages", 'mailing-group-module'); ?></a>

		<a href="admin.php?page=wpmg_mailinggroup_adminmessagelist" class="nav-tab" title="<?php _e("Admin Messages", 'mailing-group-module'); ?>"><?php _e("Admin Messages", 'mailing-group-module'); ?></a>

        <a href="admin.php?page=wpmg_mailinggroup_style" class="nav-tab" title="<?php _e("Stylesheet", 'mailing-group-module'); ?>"><?php _e("Stylesheet", 'mailing-group-module'); ?></a>

        <?php if($plugintype=='PAID') { ?>

        	<a href="admin.php?page=wpmg_mailinggroup_contact" class="nav-tab" title="<?php _e("Contact", 'mailing-group-module'); ?>"><?php _e("Contact", 'mailing-group-module'); ?></a>

        <?php } ?>

        <a href="admin.php?page=wpmg_mailinggroup_help" class="nav-tab" title="<?php _e("Help", 'mailing-group-module'); ?>"><?php _e("Help", 'mailing-group-module'); ?></a>

    </h2>

    <div>

    	<h3><?php echo sprintf( __('WordPress Mailing Group - v.%s','mailing-group-module'), $versionno ); ?><i><br /><font size="2">- by <a href="http://axactsoft.com" target="_blank">Yumna Tatheer</a></font></i></h3>

    </div>

    <div class="div800">

    	<?php echo sprintf( __('<p>The WP MailingGroup plugin allows you to run a Mailing Group, also known as a Listserv, right from your WordPress website. This means you can sign up your users, friends, neighbours, family and whoever else you want, directly from your WordPress administration area, and they can then all exchange emails via their favourite email software. This is a true Mailing Group, just like on Yahoo Groups or Google Groups, where there is an email address to send messages to, and everyone who is subscribed to the mailing group gets the message. They can then click Reply, and the whole list will receive their response.'), $websiteurl ); ?>

    </div>

    <?php if($plugintype=='FREE') { ?>

        <div class="div800">

        	<?php

             echo sprintf( __('<p>HOW GET STARTED? Check the FAQ here for a step-by-step tutorial: <a href="http://www.wpmailinggroup.com/faq/quick-start-in-6-steps/" target="_blank">www.wpmailinggroup.com/faq/quick-start-in-6-steps/</a></p>'), $websiteurl);

			?>

        </div>  

        <div class="div800">

        	<?php

             echo sprintf( __('<p>You are using the FREE version of this plugin - enjoy! A Premium version is available with more features too. See <a target="_blank" href="%s">WPMailingGroup.com</a> for more.</p>'), $websiteurl);

			?>

        </div>   		

    <?php } ?>

    <div id="col-left">

        <div class="col-wrap">

        	<h3>Administrator Email Settings</h3>

            <div>

                <div class="form-wrap">

                    <form class="validate" action="" method="post" id="mgintropage">

                        <div class="form-field">

                            <label for="tag-name"><?php _e("Alert on new subscription", 'mailing-group-module'); ?> : </label>

                            <input type="checkbox" name="alert_on_subscription" id="selector" <?php echo ($subscriptioncheck=='1'?"checked":"") ?> value="1" />

                        </div>

    					<div class="form-field">

                            <label for="tag-name"><?php _e("Subscription alert email", 'mailing-group-module'); ?> : </label>

                            <input type="text" size="40" id="subscription_email" name="subscription_email" value="<?php echo ($subscriptionemail!=''?$subscriptionemail:_e("e.g. your-mail@example.com", 'mailing-group-module')); ?>" onfocus="if(this.value=='<?php _e("e.g. your-mail@example.com", 'mailing-group-module') ?>'){ this.value=''; }" onblur="if(this.value==''){ <?php if($subscriptionemail!='') { ?>this.value='<?php echo $subscriptionemail; ?>'; <?php } else { ?> this.value='<?php _e("e.g. your-mail@example.com", 'mailing-group-module'); ?>'; <?php } ?> }"/>

                        </div>

                        <div class="form-field">

                            <label for="tag-name"><?php _e("Email bounce alert", 'mailing-group-module'); ?> : </label>

                            <input type="checkbox" name="email_bounce_alert" id="selector2" <?php echo ($bouncecheck=='1'?"checked":"") ?> value="1" />

                        </div>

    					<div class="form-field">

                            <label for="tag-name"><?php _e("No. of bounces prior to alert", 'mailing-group-module'); ?> : </label>

                            <select name="bounce_no_times" id="bounce_no_times">

                            	<option value="1" <?php echo ($bouncealerttimes=='1'?"selected":"") ?>>1</option>

                                <option value="2" <?php echo ($bouncealerttimes=='2'?"selected":"") ?>>2</option>

                                <option value="3" <?php echo ($bouncealerttimes=='3'?"selected":"") ?>>3</option>

                            </select>

                        </div>

                        <div class="form-field">

                            <label for="tag-name"><?php _e("Send bounce alert email to", 'mailing-group-module'); ?> : </label>

                            <input type="text" size="40" id="bounce_alert_email" name="bounce_alert_email" value="<?php echo ($bouncecheckalertemail!=''?$bouncecheckalertemail:_e("e.g. your-mail@example.com", 'mailing-group-module')); ?>" onfocus="if(this.value=='<?php _e("e.g. your-mail@example.com", 'mailing-group-module') ?>'){ this.value=''; }" onblur="if(this.value==''){ <?php if($bouncecheckalertemail!='') { ?>this.value='<?php echo $bouncecheckalertemail; ?>'; <?php } else { ?> this.value='<?php _e("e.g. your-mail@example.com", 'mailing-group-module'); ?>'; <?php } ?> }"/>

                        </div>

                        <div class="clearbth"></div>

                       

                        <p class="submit">

                            <input type="submit" value="Submit" class="button" id="submit" name="submit"/>

                        </p>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>