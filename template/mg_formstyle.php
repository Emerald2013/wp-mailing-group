<?php
$WPMG_SETTINGS = get_option("WPMG_SETTINGS");
if(isset($_POST['submit']) and $_POST['submit']) {
	$WPMG_SETTINGS["MG_CUSTOM_STYLESHEET"] = sanitize_text_field($_POST['user_style']);
	update_option("WPMG_SETTINGS", $WPMG_SETTINGS);
	wpmg_showmessages("updated", __("Stylesheet has been updated successfully.", 'mailing-group-module'));
}
$plugintype   = $WPMG_SETTINGS["MG_PLUGIN_TYPE"];
$custom_style = wpmg_dbStripslashes($WPMG_SETTINGS["MG_CUSTOM_STYLESHEET"]);
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
<div class="wrap">
	<h2 class="nav-tab-wrapper">
        <a href="admin.php?page=wpmg_mailinggroup_intro" title="<?php _e("Introduction", 'mailing-group-module'); ?>" class="nav-tab"><?php _e("Introduction", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_messagelist" class="nav-tab" title="<?php _e("Custom Messages", 'mailing-group-module'); ?>"><?php _e("Custom Messages", 'mailing-group-module'); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_adminmessagelist" class="nav-tab" title="<?php _e("Admin Messages", 'mailing-group-module'); ?>"><?php _e("Admin Messages", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_style" class="nav-tab nav-tab-active" title="<?php _e("Stylesheet", 'mailing-group-module'); ?>"><?php _e("Stylesheet", 'mailing-group-module'); ?></a>
        <?php if($plugintype=='PAID') { ?>
        	<a href="admin.php?page=wpmg_mailinggroup_contact" class="nav-tab" title="<?php _e("Contact", 'mailing-group-module'); ?>"><?php _e("Contact", 'mailing-group-module'); ?></a>
        <?php } ?>
        <a href="admin.php?page=wpmg_mailinggroup_help" class="nav-tab" title="<?php _e("Help", 'mailing-group-module'); ?>"><?php _e("Help", 'mailing-group-module'); ?></a>
    </h2>
	<p>You can show a registration form for your mailing groups on your website by using this shortcode: [mailing_group_form]
	To customise its appearance, you may input your own CSS styles in the text area below, to override the default styling for each class.</p>
    <div id="col-left">
        <div class="col-wrap">
            <div>
                <div class="form-wrap">
                    <form class="validate" action="" method="post" id="styleform">
                        <div class="form-field">
                            <label for="tag-name"><?php _e("Override Form Style", 'mailing-group-module'); ?> : </label>
                            <textarea name="user_style" rows="20" cols="80" id="user_style"><?php echo $custom_style; ?></textarea>
                        </div>
                        <div class="clearbth"></div>
                        <p class="submit">
                            <input type="submit" value="Submit" class="button" id="submit" name="submit"/>
                        </p>
                        <div class="clearbth"></div>
                        <div class="variableslist">
                        	<p><strong>Default CSS used in Subscription Request forms.</strong>:</p>
                            <p><code class="lftcode">/* top main div */<br />
                            .user_form_div{}<br />
                            /* form main div */<br />
                            .form-wrap{}<br />
                            /* messages class */<br />
                            .updated, .error {<br />
                                margin: 5px 0 15px;<br />
                                padding-left:5px;<br />
                            }<br />
                            .updated {<br />
                                background-color:#FFCC66;<br />
                                border-color: #333333;<br />
                            }<br />
                            .error {<br />
                                background-color:#FFCCCC;<br />
                                padding-left:5px;<br />
                            }<br />
                            /* form fields div */<br />
                            .form-field {}<br />
                            /* form field inputs */<br />
                            .form-field input {}
                            </code>
                            <code class="lftcode">
                            /* first name input */<br />
                            .fname{}<br />
                            /* email input */<br />
                            .email{}<br />
                            /* checkbox class */<br />
                            .selector{}<br />
                            /* captcha field Id */<br />
                            #c_captcha{}<br />
                            /* submit Id */<br />
                            #submit{}<br />
                            /* group listing div */<br />
                            .outer_group_div {}<br />
                            /* group checkboxes div */<br />
                            .check_div_imp{}<br />
                            /* group checkbox p */<br />
                            .inner_check_imp{}<br />
                            /* submit p class */<br />
                            .submit{}<br />
                            .button{}<br />
                            /* Form field labels */<br />
                            #mailingrequestform label{}<br />
                            /* Mailing group listing */<br />
                            .inner_check_imp_group{}</code></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>