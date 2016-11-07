<?php /**
* @package Mailing_group_module
* @version 1.3.7
*/
/*
Plugin Name: WP Mailing Group
Plugin URI: http://www.wpmailinggroup.com
Description: Connect yourselves with a mailing group run from your WordPress website! This is NOT a one-way mailing or announcement list from an administrator to a group, but a Group email list where all subscribers can exchange messages via one central email address. (NB: POP / IMAP email box required - Cron optional but recommended for low traffic websites)
Author: Yumna Tatheer
Version: 1.3.7
Plugin URI: http://www.wpmailinggroup.com
*/
if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
    die('You are not allowed to call this page directly.');
}
/**

* Indicates that a clean exit occured. Handled by set_exception_handler

*/
if (!class_exists('E_Clean_Exit')) {
    class E_Clean_Exit extends RuntimeException
    {
    }
}
define("WPMG_PLUGIN_URL", plugin_dir_url(__FILE__));
define("WPMG_PLUGIN_PATH", dirname(__FILE__));
/* Class to be used in complete plugin for all db requests */

require_once("lib/mailinggroupclass.php");
$objMem = new mailinggroupClass();
global $objMem;

require_once("lib/receivemail.class.php");
$obj = new receiveMail('','','',$mailserver='',$servertype='',$port='',$ssl='');
global $obj;

$WPMG_SETTINGS = get_option("WPMG_SETTINGS");
$MG_VERSION_NO = '1.3.7';
$WPMG_SETTINGS['MG_VERSION_NO']  = $MG_VERSION_NO;
$WPMG_SETTINGS['MG_PLUGIN_TYPE'] = 'FREE';
update_option("WPMG_SETTINGS", $WPMG_SETTINGS);
global $WPMG_SETTINGS;

/*Define global variable to be used in plugin*/
global $wpdb, $table_name_group, $table_name_message, $table_name_requestmanager, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_parsed_emails, $table_name_sent_emails, $memberLimit, $table_name_attachments, $table_name_users, $table_name_usermeta;
$charset_collate = $wpdb->get_charset_collate();
$visibilityArray  = array(
    "Public" => '1',
    "Invitation" => '2',
    "Private" => '3'
);

$memberLimit                        = 20;
$table_name_group                   = $wpdb->prefix . "mailing_group";
$table_name_message                 = $wpdb->prefix . "mailing_group_messages";
$table_name_requestmanager          = $wpdb->prefix . "mailing_group_requestmanager";
$table_name_requestmanager_taxonomy = $wpdb->prefix . "mailing_group_taxonomy";
$table_name_user_taxonomy           = $wpdb->prefix . "mailing_group_user_taxonomy";
$table_name_parsed_emails           = $wpdb->prefix . "mailing_group_parsed_emails";
$table_name_sent_emails             = $wpdb->prefix . "mailing_group_sent_emails";
$table_name_attachments             = $wpdb->prefix . "mailing_group_attachments";
$table_name_users                   = $wpdb->prefix . "users";
$table_name_usermeta                = $wpdb->prefix . "usermeta";

function get_test_email(){
		
		$sitename = strtolower( $_SERVER['SERVER_NAME'] );
    if ( substr( $sitename, 0, 4 ) == 'www.' ) {
        $sitename = substr( $sitename, 4 );
    }

    $from_email = 'wordpress@' . $sitename;	
	
	return $from_email;
	
}

function get_noreply_email(){
		
		$sitename = strtolower( $_SERVER['SERVER_NAME'] );
    if ( substr( $sitename, 0, 4 ) == 'www.' ) {
        $sitename = substr( $sitename, 4 );
    }

    $noreply_email = 'noreply@' . $sitename;	
	
	return $noreply_email;
	
}
add_filter( 'cron_schedules', 'cron_add_weekly' );
function cron_add_weekly( $schedules ) {
 	// Adds once weekly to the existing schedules.
 	$schedules['wpmg_two_minute'] = array(
 		'interval' => 120,
 		'display' => __( 'Every Two Minutes' )
 	);
 	$schedules['wpmg_five_minute'] = array(
 		'interval' => 300,
 		'display' => __( 'Every Five Minutes' )
 	);
 	$schedules['wpmg_fifteen_minute'] = array(
 		'interval' => 900,
 		'display' => __( 'Every Fifteen Minutes' )
 	);	
 	return $schedules;
}


function wpmg_add_mailing_group_plugin()
{

    global $wpdb, $table_name_group, $WPMG_SETTINGS, $table_name_message, $table_name_requestmanager, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_parsed_emails, $table_name_sent_emails, $memberLimit, $table_name_attachments, $table_name_users, $table_name_usermeta;
	$charset_collate = $wpdb->get_charset_collate();
    /* ADD CONFIG OPTION TO OPTION TABLE*/

	if ( ! wp_next_scheduled('wpmg_cron_task_send_email')) {
	    wp_schedule_event( time(), 'wpmg_two_minute', 'wpmg_cron_task_send_email' );
	}
	if ( ! wp_next_scheduled('wpmg_cron_task_parse_email')) {
	    wp_schedule_event( time(), 'wpmg_five_minute', 'wpmg_cron_task_parse_email' );
	}
	if ( ! wp_next_scheduled('wpmg_cron_task_bounced_email')) {
	    wp_schedule_event( time(), 'wpmg_fifteen_minute', 'wpmg_cron_task_bounced_email' );
	}	
	if ( ! wp_next_scheduled('wpmg_cron_auto_delete_attachments')) {
	    wp_schedule_event( time(), 'daily', 'wpmg_cron_auto_delete_attachments' );
	}
    $wpmgs = get_option("WPMG_SETTINGS");

	$MG_SUBSCRIPTION_REQUEST_ALERT_EMAIL = (isset($wpmgs['MG_SUBSCRIPTION_REQUEST_ALERT_EMAIL']) && $wpmgs['MG_SUBSCRIPTION_REQUEST_ALERT_EMAIL']!=''?$wpmgs['MG_SUBSCRIPTION_REQUEST_ALERT_EMAIL']:'e.g. your-mail@example.com');
	$MG_BOUNCE_CHECK_ALERT_TIMES   = (isset($wpmgs['MG_BOUNCE_CHECK_ALERT_TIMES']) && $wpmgs['MG_BOUNCE_CHECK_ALERT_TIMES']!=''?$wpmgs['MG_BOUNCE_CHECK_ALERT_TIMES']:'2');
	$MG_BOUNCE_CHECK_ALERT_EMAIL   = (isset($wpmgs['MG_BOUNCE_CHECK_ALERT_EMAIL']) && $wpmgs['MG_BOUNCE_CHECK_ALERT_EMAIL']!=''?$wpmgs['MG_BOUNCE_CHECK_ALERT_EMAIL']:'e.g. your-mail@example.com');
	$MG_SUBSCRIPTION_REQUEST_CHECK = (isset($wpmgs['MG_SUBSCRIPTION_REQUEST_CHECK']) && $wpmgs['MG_SUBSCRIPTION_REQUEST_CHECK']!=''?$wpmgs['MG_SUBSCRIPTION_REQUEST_CHECK']:'1');
	$MG_WEBSITE_URL      = (isset($wpmgs['MG_WEBSITE_URL'])  && $wpmgs['MG_WEBSITE_URL']!=''?$wpmgs['MG_WEBSITE_URL']:'http://www.wpmailinggroup.com');
	$MG_PLUGIN_TYPE      = (isset($wpmgs['MG_PLUGIN_TYPE'])  && $wpmgs['MG_PLUGIN_TYPE']!=''?$wpmgs['MG_PLUGIN_TYPE']:'FREE');
	$MG_BOUNCE_CHECK     = (isset($wpmgs['MG_BOUNCE_CHECK']) && $wpmgs['MG_BOUNCE_CHECK']!=''?$wpmgs['MG_BOUNCE_CHECK']:'0');
	$MG_CUSTOM_STYLESHEET= (isset($wpmgs['MG_CUSTOM_STYLESHEET']) && $wpmgs['MG_CUSTOM_STYLESHEET']!=''?$wpmgs['MG_CUSTOM_STYLESHEET']:'');
	$MG_CONTACT_ADDRESS  = (isset($wpmgs['MG_CONTACT_ADDRESS'])   && $wpmgs['MG_CONTACT_ADDRESS']!=''?$wpmgs['MG_CONTACT_ADDRESS']:'Test1, first drive<br>Highway 1st<br>NSD 201345');
	$MG_SUPPORT_EMAIL    = (isset($wpmgs['MG_SUPPORT_EMAIL']) && $wpmgs['MG_SUPPORT_EMAIL']!=''?$wpmgs['MG_SUPPORT_EMAIL']:'marcus@wpmailinggroup.com');
	$MG_SUPPORT_PHONE    = (isset($wpmgs['MG_SUPPORT_PHONE']) && $wpmgs['MG_SUPPORT_PHONE']!=''?$wpmgs['MG_SUPPORT_PHONE']:'1800-123-1234');
	$MG_VERSION_NO       = (isset($wpmgs['MG_VERSION_NO'])    && $wpmgs['MG_VERSION_NO']!=''?$wpmgs['MG_VERSION_NO']:'1.3.7');
	
	$wpmg_setting = array(
		"MG_WEBSITE_URL"  => $MG_WEBSITE_URL,
		"MG_PLUGIN_TYPE"  => $MG_PLUGIN_TYPE,
		"MG_SUBSCRIPTION_REQUEST_CHECK" => $MG_SUBSCRIPTION_REQUEST_CHECK,
		"MG_SUBSCRIPTION_REQUEST_ALERT_EMAIL" => $MG_SUBSCRIPTION_REQUEST_ALERT_EMAIL,
		"MG_BOUNCE_CHECK" => $MG_BOUNCE_CHECK,
		"MG_BOUNCE_CHECK_ALERT_TIMES" => $MG_BOUNCE_CHECK_ALERT_TIMES,
		"MG_BOUNCE_CHECK_ALERT_EMAIL" => $MG_BOUNCE_CHECK_ALERT_EMAIL,
		"MG_CUSTOM_STYLESHEET" => $MG_CUSTOM_STYLESHEET,
		"MG_CONTACT_ADDRESS"   => $MG_CONTACT_ADDRESS,
		"MG_SUPPORT_EMAIL"     => $MG_SUPPORT_EMAIL,
		"MG_SUPPORT_PHONE"     => $MG_SUPPORT_PHONE,
		"MG_VERSION_NO"        => $MG_VERSION_NO
	);

    update_option("WPMG_SETTINGS", $wpmg_setting);		
	
    $MSQL = "show tables like '$table_name_group'";

    if ($wpdb->get_var($MSQL) != $table_name_group) {
        $sql = "CREATE TABLE IF NOT EXISTS `$table_name_group` (
			  `id` mediumint(9) NOT NULL AUTO_INCREMENT,

			  `title` varchar(200) NOT NULL,
			  
			  `use_in_subject` int(2) NOT NULL DEFAULT '0',

			  `email` varchar(255) NOT NULL,

			  `password` varchar(100) NOT NULL,
			  
			  `pop_server_type` varchar(50) NOT NULL,
			  
			  `smtp_server` varchar(100) NOT NULL,

			  `pop_server` varchar(100) NOT NULL,

			  `smtp_port` varchar(20) NOT NULL,

			  `pop_port` varchar(20) NOT NULL,
			  
			  `pop_ssl` tinyint(2) NOT NULL DEFAULT '0',			  

			  `smtp_username` varchar(100) NOT NULL,

			  `smtp_password` varchar(100) NOT NULL,

			  `pop_username` varchar(100) NOT NULL,

			  `pop_password` varchar(100) NOT NULL,

			  `archive_message` tinyint(2) NOT NULL DEFAULT '0',

			  `auto_delete` tinyint(2) NOT NULL DEFAULT '0',

			  `auto_delete_limit` tinyint(2) NOT NULL DEFAULT '0',

			  `footer_text` text NOT NULL,

			  `sender_name` varchar(50) NOT NULL,

			  `sender_email` varchar(50) NOT NULL,
			  
			  `status` tinyint(2) NOT NULL DEFAULT '0',
			  
			  `visibility` enum('1','2','3') NOT NULL DEFAULT '1',

              `mail_type` varchar(50) NOT NULL,			  

			  PRIMARY KEY (`id`)

			) ENGINE=MyISAM  $charset_collate AUTO_INCREMENT=1;";
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");
        dbDelta($sql);
    }
    $MSQL = "show tables like '$table_name_message'";
    if ($wpdb->get_var($MSQL) != $table_name_message) {
        $sql      = "CREATE TABLE IF NOT EXISTS `$table_name_message` (

			  `id` mediumint(9) NOT NULL AUTO_INCREMENT,

			  `title` varchar(255) DEFAULT NULL,

			  `message_type` varchar(255) NOT NULL,

			  `message_subject` varchar(255) NOT NULL,

			  `description` text,

			  `status` enum('0','1') DEFAULT '0',

			  PRIMARY KEY (`id`)

			) ENGINE=MyISAM  $charset_collate AUTO_INCREMENT=1;";
        $sql2     = "INSERT INTO `$table_name_message` (id,title,description,status) VALUES ('','Credentials Check','Hello {%name%},

Thank you for your subscription request to {%group_name%} at {%site_title%} ({%site_url%}).
Could you please send supporting documents to confirm your credentials for joining this list?
Thank you in advance.

The List Admin.
{%site_email%}','1')";
        $data1    = esc_sql("Dear Admin,\r\n\r\nA new subscription request was submitted on {%site_title%}.\r\n\r\nGroup Subscribed :  {%group_name%}\r\nUser Name : {%name%}\r\nUser Email : {%email%}\r\n\r\nPlease visit the <a href='{%group_url%}'>Mailing Group Manager</a> to respond to this request.");
        $subject1 = esc_sql("New subscription request for {%group_name%}");
        $data2    = esc_sql("Hi there,\r\n\r\nWelcome to {%site_title%}! Here's how to log in:\r\n\r\nUsername: {%name%}\r\nPassword: {%password%}\r\n\r\nIf you have any problems, please contact me at {%site_email%}.");
        $subject2 = esc_sql("{%site_title%} account confirmation");
        $data3    = esc_sql("New user registration on {%site_title%}:\r\n\r\nUsername: {%name%}\r\nE-mail: {%email%}.");
        $subject3 = esc_sql("New user on {%site_title%}");
        $data4    = esc_sql("Hi {%displayname%},\r\n\r\n{%group_list%} at <a href='{%site_url%}'>{%site_title%}</a> has received a subscription request for your email address: {%email%}. If you would like to confirm your subscription, please click the activation link below, or copy and paste it into your web browser:\r\n\r\n<a href='{%activation_url%}'>{%activation_url%}</a>\r\n\r\nIf you did not request membership of this mailing group, please disregard this message and accept our apologies for any inconvenience caused.");
        $subject4 = esc_sql("Email opt-in confirmation: {%group_name%}");
        $data5    = esc_sql("Hi {%displayname%},\r\n\r\nWelcome to {%group_list%} at <a href='{%site_url%}'>{%site_title%} - ({%site_url%})</a>!\r\n\r\nYour group subscription was successful, and here are a few hints on how to update it in future.\r\n\r\nTo Unsubscribe, or to change your email format preference (Plain Text / HTML), please visit your <a href='{%login_url%}'>User Profile</a> and log in with the account information sent to you in a separate email.\r\n\r\nShould you mislay that account information, you can request a new password by inputting your email address at <a href='{%login_url%}'>{%login_url%}</a>.");
        $subject5 = esc_sql("Welcome to {%group_name%}!");
        $sql3     = "INSERT INTO `" . $table_name_message . "` (`message_type`, `message_subject`, `title`, `description`) VALUES ('wpmg_sendmessagetoAdmin','" . $subject1 . "','For admin: New subscription request alert', '" . $data1 . "'),('RegistrationNotificationMailToMember','" . $subject2 . "','For subscribers: WP site membership confirmation with login details', '" . $data2 . "'),('RegistrationNotificationMailToAdmin','" . $subject3 . "','For admin: New WP site member with login details', '" . $data3 . "'),('Confirmationemailforsubscribertoverifyaccount','" . $subject4 . "','For subscribers: Opt-in confirmation for new subscribers', '" . $data4 . "'),('Emailuseronsuccessfullregisterationofagroup','" . $subject5 . "','For subscribers: Confirmation of successful group subscription', '" . $data5 . "')";
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");
        dbDelta($sql);
        dbDelta($sql2);
        dbDelta($sql3);
    }else{
		//dbdelta not updating old tables so this is a workaround
		$wpdb->query("ALTER TABLE $table_name_message CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
	
	}
    $MSQL = "show tables like '$table_name_requestmanager'";
    if ($wpdb->get_var($MSQL) != $table_name_requestmanager) {
        $sql = "CREATE TABLE IF NOT EXISTS `$table_name_requestmanager` (

			  `id` int(9) NOT NULL AUTO_INCREMENT,

			  `name` varchar(200) NOT NULL,

			  `username` varchar(150) NOT NULL,

			  `email` varchar(255) NOT NULL,

			  `message_sent` int(2) NOT NULL DEFAULT '0',

			  `status` tinyint(2) NOT NULL,

			  PRIMARY KEY (`id`)

			) ENGINE=MyISAM  $charset_collate AUTO_INCREMENT=1";
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");
        dbDelta($sql);
     }else{
		//dbdelta not updating old tables so this is a workaround
		$wpdb->query("ALTER TABLE $table_name_requestmanager CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
	
	}
    $MSQL = "show tables like '$table_name_requestmanager_taxonomy'";
    if ($wpdb->get_var($MSQL) != $table_name_requestmanager_taxonomy) {
        $sql = "CREATE TABLE IF NOT EXISTS `$table_name_requestmanager_taxonomy` (

			  `id` int(50) NOT NULL AUTO_INCREMENT,

			  `user_id` int(50) NOT NULL,

			  `group_id` int(50) NOT NULL,

			  `group_email_format` tinyint(2) NOT NULL DEFAULT '0',

			  PRIMARY KEY (`id`)

					) ENGINE=MyISAM  $charset_collate AUTO_INCREMENT=1";
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");
        $result=dbDelta($sql);
    }else{
		//dbdelta not updating old tables so this is a workaround
		$wpdb->query("ALTER TABLE $table_name_requestmanager_taxonomy CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
	
	}
    $MSQL = "show tables like '$table_name_user_taxonomy'";
    if ($wpdb->get_var($MSQL) != $table_name_user_taxonomy) {
        $sql = "CREATE TABLE IF NOT EXISTS `$table_name_user_taxonomy` (

			  `id` int(50) NOT NULL AUTO_INCREMENT,

			  `user_id` int(50) NOT NULL,

			  `group_id` int(50) NOT NULL,

			  `group_email_format` tinyint(2) NOT NULL,

			  PRIMARY KEY (`id`)

			) ENGINE=MyISAM  $charset_collate AUTO_INCREMENT=1";
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");
        dbDelta($sql);
    }
    $MSQL = "show tables like '$table_name_parsed_emails'";
    if ($wpdb->get_var($MSQL) != $table_name_parsed_emails) {
        $sql = "CREATE TABLE IF NOT EXISTS `$table_name_parsed_emails` (

			  `id` bigint(100) NOT NULL AUTO_INCREMENT,

              `type` varchar(50) NOT NULL DEFAULT 'email',

			  `email_bounced` varchar(100) NOT NULL,

			  `email_from` varchar(255) NOT NULL,

			  `email_from_name` varchar(255) NOT NULL,

			  `email_to` varchar(255) NOT NULL,

			  `email_to_name` varchar(255) NOT NULL,

			  `email_subject` varchar(255) NOT NULL,

			  `email_content` longblob NOT NULL,

			  `email_group_id` int(20) NOT NULL,

			  `status` tinyint(2) NOT NULL DEFAULT '0',

			  PRIMARY KEY (`id`)

			) ENGINE=MyISAM  $charset_collate AUTO_INCREMENT=1";
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");
        dbDelta($sql);
    }else{
		//dbdelta not updating old tables so this is a workaround
		$wpdb->query("ALTER TABLE $table_name_parsed_emails CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
		
		$wpdb->query("ALTER TABLE `wp_mailing_group_parsed_emails` MODIFY `email_subject` varchar( 255 ) CHARACTER SET utf8");
		
	
	}
    $MSQL = "show tables like '$table_name_sent_emails'";
    if ($wpdb->get_var($MSQL) != $table_name_sent_emails) {
        $sql = "CREATE TABLE IF NOT EXISTS `$table_name_sent_emails` (

			  `id` bigint(20) NOT NULL AUTO_INCREMENT,

			  `user_id` int(10) NOT NULL,

			  `email_id` int(10) NOT NULL,

			  `group_id` int(20) NOT NULL,

			  `sent_date` datetime NOT NULL,

			  `status` int(2) NOT NULL DEFAULT '0',

			  `error_msg` text NOT NULL,

			  PRIMARY KEY (`id`)

			) ENGINE=MyISAM  $charset_collate AUTO_INCREMENT=1";
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");
        dbDelta($sql);
    }else{
		//dbdelta not updating old tables so this is a workaround
		$wpdb->query("ALTER TABLE $table_name_sent_emails CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
	
	}
	
	
	/* Add column if not present. */	
		$group_pop_ssl_column = $wpdb->get_row("SELECT * FROM $table_name_group"); 
	//changed query to get column names even if no records are there in table
	
	$col_names = $wpdb->get_results("SHOW COLUMNS FROM $table_name_group");
	
	  
	   foreach( $col_names as $col_name ) {

         $col_names_array []= $col_name->Field;
    }
	

	if(!in_array('pop_ssl',$col_names_array)){
		$wpdb->query("ALTER TABLE $table_name_group ADD pop_ssl tinyint(2) NOT NULL DEFAULT 0");
	}

	/* Add column if not present in Version 1.2.5  */	
	$group_attachments_column = $wpdb->get_row("SELECT * FROM $table_name_group");
	if(!in_array('save_attachments',$col_names_array)){
		$wpdb->query("ALTER TABLE $table_name_group
				ADD COLUMN `save_attachments` INT(11) NOT NULL DEFAULT 0 AFTER `pop_ssl`,
				ADD COLUMN `att_auto_delete`  INT(11) NOT NULL DEFAULT 0 AFTER `save_attachments`,
				ADD COLUMN `att_auto_delete_limit` INT(11) NOT NULL DEFAULT 0 AFTER `att_auto_delete`,
				ADD COLUMN `size_limit`       INT(11) NOT NULL DEFAULT 0 AFTER `att_auto_delete_limit`,
				ADD COLUMN `size_limit_value` INT(11) NOT NULL DEFAULT 0 AFTER `size_limit`");
	}
	
	/* Add attachment table if not present. */
    $MSQL = "show tables like '$table_name_attachments'";
    if ($wpdb->get_var($MSQL) != $table_name_attachments) {
        $sql = "CREATE TABLE IF NOT EXISTS `$table_name_attachments` (

			  `id` bigint(20) NOT NULL AUTO_INCREMENT,
			  
			  `group_id` int(10) NOT NULL,

			  `file_name` varchar(255) NOT NULL,

			  `size` varchar(55) NOT NULL,

			  `date` varchar(55) NOT NULL,

			  PRIMARY KEY (`id`)

			) ENGINE=MyISAM  $charset_collate AUTO_INCREMENT=1";
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");
        dbDelta($sql);
    }		
	/* Add attachments column if not present. */	
	$group_parsed_column = $wpdb->get_row("SELECT * FROM $table_name_parsed_emails");
		$col_names = $wpdb->get_results("SHOW COLUMNS FROM $table_name_parsed_emails");
	
	  
	   foreach( $col_names as $col_name ) {

         $col_names_array []= $col_name->Field;
    }
	

	if(!in_array('attachments',$col_names_array)){

		$wpdb->query("ALTER TABLE $table_name_parsed_emails ADD attachments int(11) NOT NULL DEFAULT 1 AFTER email_group_id");
	}	
	
	/* Add email_id column if not present. */
	$group_attachments_email_column = $wpdb->get_row("SELECT * FROM $table_name_attachments");
	$col_names = $wpdb->get_results("SHOW COLUMNS FROM $table_name_attachments");


	
	  
	   foreach( $col_names as $col_name ) {

         $col_names_array []= $col_name->Field;
    }
	

	if(!in_array('email_id',$col_names_array)){

		$wpdb->query("ALTER TABLE $table_name_attachments ADD email_id int(11) NOT NULL DEFAULT 0 AFTER size");
	}		
	
}
function wpmg_myStartSession()
{
    if (!session_id() && !is_admin()) {
        session_start();
    }
}
function wpmg_myEndSession()
{
    session_destroy();
}
/* Hooks used in Plugin */
register_activation_hook(__FILE__, 'wpmg_add_mailing_group_plugin');
register_uninstall_hook(__FILE__, "wpmg_mailing_group_uninstall");
register_deactivation_hook(__FILE__, "wpmg_mailing_group_deactivate");
add_action('init', 'wpmg_myStartSession', 1);
add_action('wp_logout', 'wpmg_myEndSession');
add_action('wp_login', 'wpmg_myEndSession');
/* Creating Menus */
function wpmg_mailinggroup_Menu()
{
    $admin_level = 'edit_posts';
    $user_level  = 'read';
    if (current_user_can('manage_options')) {
        /* Adding menus */
        add_menu_page(__('Mailing Group Manager','mailing-group-module'), __('Mailing Group Manager', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_intro', 'wpmg_mailinggroup_intro');
        add_submenu_page('wpmg_mailinggroup_intro', __('General Settings', 'mailing-group-module'), __('General Settings', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_intro', 'wpmg_mailinggroup_intro');
        add_submenu_page('wpmg_mailinggroup_intro', __('Mailing Groups', 'mailing-group-module'), __('Mailing Groups', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_list', 'wpmg_mailinggroup_list');
		add_submenu_page('wpmg_mailinggroup_intro', __('Troubleshooting', 'mailing-group-module'), __('Troubleshooting', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_testmail', 'wpmg_mailinggroup_testmail');
        add_submenu_page('null', __('Add Mailing Group', 'mailing-group-module'), __('Add Mailing Group', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_add', 'wpmg_mailinggroup_add');
        add_submenu_page('null', __('Member Manager', 'mailing-group-module'), __('Member Manager', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_memberlist', 'wpmg_mailinggroup_memberlist');
        add_submenu_page('null', __('Add Member', 'mailing-group-module'), __('Add Member', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_memberadd', 'wpmg_mailinggroup_memberadd');
        add_submenu_page('wpmg_mailinggroup_intro', __('Add Subscribers', 'mailing-group-module'), __('Add Subscribers', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_requestmanageradd', 'wpmg_mailinggroup_requestmanageradd');
        add_submenu_page('null', __('Import Users', 'mailing-group-module'), __('Import Users', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_importuser', 'wpmg_mailinggroup_importuser');
        add_submenu_page('wpmg_mailinggroup_intro', __('Subscription Requests', 'mailing-group-module'), __('Subscription Requests', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_requestmanagerlist', 'wpmg_mailinggroup_requestmanagerlist');
        add_submenu_page('null', __('Add Subscription Request', 'mailing-group-module'), __('Add Subscription Request', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_requestmanageradd', 'wpmg_mailinggroup_requestmanageradd');
        add_submenu_page('null', __('Send Message', 'mailing-group-module'), __('Send Message', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_sendmessage', 'wpmg_mailinggroup_sendmessage');
        add_submenu_page('null', __('Messages Manager', 'mailing-group-module'), __('Messages Manager', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_messagelist', 'wpmg_mailinggroup_messagelist');
        add_submenu_page('null', __('Messages Editor', 'mailing-group-module'), __('Messages Editor', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_adminmessagelist', 'wpmg_mailinggroup_adminmessagelist');
        add_submenu_page('null', __('Add Message', 'mailing-group-module'), __('Add Message', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_messageadd', 'wpmg_mailinggroup_messageadd');
        add_submenu_page('null', __('Add Admin Message', 'mailing-group-module'), __('Add Admin Message', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_adminmessageadd', 'wpmg_mailinggroup_adminmessageadd');
        add_submenu_page('null', __('Import User', 'mailing-group-module'), __('Import User', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_importuser', 'wpmg_mailinggroup_importuser');
        add_submenu_page('null', __('View Message', 'mailing-group-module'), __('View Message', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_viewmessage', 'wpmg_mailinggroup_viewmessage');
        add_submenu_page('null', __('Style Manager', 'mailing-group-module'), __('Style Manager', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_style', 'wpmg_mailinggroup_style');
        add_submenu_page('null', __('Contact Info', 'mailing-group-module'), __('Contact Info', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_contact', 'wpmg_mailinggroup_contact');
        add_submenu_page('null', __('Help', 'mailing-group-module'), __('Help', 'mailing-group-module'), $admin_level, 'wpmg_mailinggroup_help', 'wpmg_mailinggroup_help');
    } else {
        add_menu_page(__('Mailing Groups', 'mailing-group-module'), __('Mailing Groups', 'mailing-group-module'), $user_level, 'wpmg_mailinggroup_membergroups', 'wpmg_mailinggroup_membergroups');
        add_submenu_page('null', __('View Message', 'mailing-group-module'), __('View Message', 'mailing-group-module'), $user_level, 'wpmg_mailinggroup_viewmessage', 'wpmg_mailinggroup_viewmessage');
    }
    wp_register_style('demo_table.css', plugin_dir_url(__FILE__) . 'css/demo_table.css');
    wp_enqueue_style('demo_table.css');
    wp_register_script('jquery.dataTables.js', plugin_dir_url(__FILE__) . 'js/jquery.dataTables.js', array(
        'jquery'
    ));
    wp_enqueue_script('jquery.dataTables.js');
    wp_register_script('custom.js', plugin_dir_url(__FILE__) . 'js/custom.js', array(
        'jquery'
    ));
    wp_enqueue_script('custom.js');
}
/* initialize menu */
add_action('admin_menu', 'wpmg_mailinggroup_Menu');
/* initialize languae loader */
function wpmg_mailing_group_language_init()
{
    load_plugin_textdomain('mailing-group-module', FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('init', 'wpmg_mailing_group_language_init');
/* initialize languae loader */
function wpmg_mailinggroup_generalsettingtab()
{
    include "template/mg_settingstab.php";
}
/* defining template of all pages used start */
function wpmg_mailinggroup_intro()
{
    global $wpdb;
    include "template/mg_intro_text.php";
}
function wpmg_mailinggroup_help()
{
    global $wpdb;
    include "template/mg_help.php";
}
function wpmg_mailinggroup_style()
{
    global $wpdb;
    include "template/mg_formstyle.php";
}
function wpmg_mailinggroup_contact()
{
    global $wpdb;
    include "template/mg_contact.php";
}
function wpmg_mailinggroup_list()
{
    global $wpdb, $objMem, $table_name_group, $table_name_requestmanager, $table_name_requestmanager_taxonomy;
    include "template/mg_mailinggrouplist.php";
}
function wpmg_mailinggroup_add()
{
    global $wpdb, $objMem, $table_name_group;
    include "template/mg_mailinggroupadd.php";
}
function wpmg_mailinggroup_messagelist()
{
    global $wpdb, $objMem, $table_name_message;
    include "template/mg_messagelist.php";
}
function wpmg_mailinggroup_adminmessagelist()
{
    global $wpdb, $objMem, $table_name_message;
    include "template/mg_adminmessagelist.php";
}
function wpmg_mailinggroup_messageadd()
{
    global $wpdb, $objMem, $table_name_message;
    include "template/mg_messageadd.php";
}
function wpmg_mailinggroup_adminmessageadd()
{
    global $wpdb, $objMem, $table_name_message;
    include "template/mg_adminmessageadd.php";
}
function wpmg_mailinggroup_sendmessage()
{
    global $wpdb, $objMem, $table_name_group, $table_name_message, $table_name_requestmanager;
    include "template/mg_sendmessage.php";
}
function wpmg_mailinggroup_importuser()
{
    global $wpdb, $objMem, $table_name_group, $table_name_user_taxonomy, $memberLimit;
    include "lib/vcard.php";
    include "template/mg_importuser.php";
}
function wpmg_mailinggroup_requestmanagerlist()
{
    global $wpdb, $objMem, $table_name_group, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_message, $table_name_requestmanager, $memberLimit;
    add_action('wp_enqueue_script', 'add_thickbox');
    include "template/mg_mailingrequest.php";
}
function wpmg_mailinggroup_requestmanageradd()
{
    global $wpdb, $objMem, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_group, $table_name_requestmanager;
    include "template/mg_mailingrequestadd.php";
}
function wpmg_mailinggroup_testmail()
{
    global $wpdb, $objMem, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_group, $table_name_requestmanager;
    include "template/mg_testmail.php";
}
function wpmg_mailinggroup_memberlist()
{
    global $wpdb, $objMem, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_sent_emails, $table_name_group;
    include "template/mg_memberlist.php";
}
function wpmg_mailinggroup_memberadd()
{
    global $wpdb, $objMem, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_group, $memberLimit;
    include "template/mg_memberadd.php";
}
function wpmg_mailinggroup_membergroups()
{
    global $wpdb, $objMem, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_group;
    include "template/mg_membergroups.php";
}
function wpmg_mailinggroup_viewmessage()
{
    global $wpdb, $objMem, $table_name_parsed_emails;
    include "template/mg_viewmessage.php";
}
/* defining template of all page s used end */
/* general function */
function wpmg_redirectTo($page, $end = "admin")
{
    $url = "admin.php?page=" . $page;
    if ($end == 'front') {
        $url = $_SERVER['REQUEST_URI'] . $page;
    }
    if ($end == 'abs') {
        $url = $page;
    }
    if (headers_sent()) {
?>

		<html><head>

			<script language="javascript" type="text/javascript">

				window.self.location='<?php  echo $url; ?>';

			</script>

		</head></html>

	<?php
        exit;
    } else {
        header("Location: " . $url);
        exit;
    }
}
function wpmg_dbAddslashes($value)
{
    return addslashes($value);
}
function wpmg_dbStripslashes($value)
{
    return stripslashes($value);
}
function wpmg_dbHtmlentities($value)
{
    return htmlentities($value);
}
function wpmg_nl2brformat($value)
{
    return nl2br($value);
}
function wpmg_checklength($value)
{
    return strlen($value);
}
function wpmg_stringlength($value, $length = 75)
{
    return substr($value, 0, $length) . "...";
}
function wpmg_trimVal($value, $by = "")
{
    if ($by == "") {
        return trim($value);
    } else {
        return trim($value, $by);
    }
}
function wpmg_showmessages($type, $message)
{
    echo "<div class='" . $type . "' id='message'><p><strong>Mailing Group Manager: " . $message . "</strong></p></div>";
}
/**

* Parses a set of cards from one or more lines. The cards are sorted by

* the N (name) property value. There is no return value. If two cards

* have the same key, then the last card parsed is stored in the array.

*/
function wpmg_parse_vcards(&$lines)
{
    $cards = array();
    $card  = new VCard();
    while ($card->parse($lines)) {
        $property = $card->getProperty('N');
        if (!$property) {
            return "";
        }
        $n   = $property->getComponents();
        $tmp = array();
        if ($n[3])
            $tmp[] = $n[3];  /* Mr. */
        if ($n[1])
            $tmp[] = $n[1]; /*  John */
        if ($n[2])
            $tmp[] = $n[2]; /*  Quinlan */
        if ($n[4])
            $tmp[] = $n[4]; /*  Esq. */
        $ret = array();
        if ($n[0])
            $ret[] = $n[0];
        $tmp = join(" ", $tmp);
        if ($tmp)
            $ret[] = $tmp;
        $key         = join(", ", $ret);
        $cards[$key] = $card;
        /*  MDH: Create new VCard to prevent overwriting previous one (PHP5) */
        $card        = new VCard();
    }
    ksort($cards);
    return $cards;
}
/* general function */
/* ajax requests */
add_action('wp_ajax_wpmg_addeditmailinggroup', 'wpmg_addeditmailinggroup_callback');
add_action('wp_ajax_wpmg_addmailgroupsetting', 'wpmg_addmailgroupsetting_callback');
add_action('wp_ajax_wpmg_mailinggrouplisting', 'wpmg_mailinggrouplisting_callback');
add_action('wp_ajax_wpmg_sendmessage', 'wpmg_sendmessage_callback');
add_action('wp_ajax_wpmg_checkusername', 'wpmg_checkusername_callback');
add_action('wp_ajax_wpmg_viewmessage', 'wpmg_viewmessage_callback');
/* Short codes for ajax requests */
/* callback function for above ajax requests */
function wpmg_viewmessage_callback()
{
    global $wpdb, $objMem, $table_name_parsed_emails;
    include "template/mg_viewmessageajax.php";
    die();  /* this is required to return a proper result */
}
function wpmg_addeditmailinggroup_callback()
{
    global $wpdb, $objMem, $table_name_group;
    include "template/mg_mailinggroupadd.php";
    die(); /*  this is required to return a proper result */
}
function wpmg_mailinggrouplisting_callback()
{
    global $wpdb, $objMem, $table_name_group;
    include "template/mg_mailinggrouplist.php";
    die(); /*   this is required to return a proper result */
}
function wpmg_addmailgroupsetting_callback()
{
    global $wpdb, $objMem, $_POST, $table_name_group; 
	$_POST = stripslashes_deep( $_POST );
    $addme      = sanitize_text_field($_POST["addme"]);
    $WPMG_SETTINGS = get_option("WPMG_SETTINGS");
	$plugintype = $WPMG_SETTINGS["MG_PLUGIN_TYPE"];

    if ($plugintype == 'FREE') {
        $result = $objMem->selectRows($table_name_group, "", " order by id desc");
        if (count($result) > 0 && $addme != 2) {
            echo "free";
            exit;
        }
    }
    $myFields = array(
        "id",
        "title",
		"use_in_subject",
        "email",
        "password",
        "pop_server_type",		
        "smtp_server",
        "pop_server",
        "smtp_port",
        "pop_port",
		"pop_ssl",				
        "smtp_username",
        "smtp_password",
        "pop_username",
        "pop_password",
        "archive_message",
        "auto_delete",
        "auto_delete_limit",
        "footer_text",
        "sender_name",
        "sender_email",
        "status",
        "visibility",
		"mail_type",
		"save_attachments",
		"att_auto_delete",
		"att_auto_delete_limit",
		"size_limit",
		"size_limit_value"		
    );
    if ($addme == 1) {
        if (!$objMem->checkRowExists($table_name_group, "title", $_POST, "")) {
            $objMem->addNewRow($table_name_group, $_POST, $myFields);
            echo "added";
            exit;
        } else {
            echo "exists";
            exit;
        }
    } else if ($addme == 2) {
        if (!$objMem->checkRowExists($table_name_group, "title", $_POST, "idCheck")) {
            $objMem->updRow($table_name_group, $_POST, $myFields);
            echo "updated";
            exit;
        } else {
            echo "exists";
            exit;
        }
    }
}
function wpmg_sendmessage_callback()
{
    global $wpdb, $objMem, $table_name_group, $table_name_message;
    include "template/mg_sendmessage.php";
    die();
}
function wpmg_checkusername_callback()
{
    global $wpdb, $objMem;
    include "template/mg_memberadd.php";
}
/* callback function for above ajax requests */
/* mail function used in plugin */
function wpmg_sendmessagetoSubscriber($gid, $id, $info)
{
    global $wpdb, $objMem, $table_name_group, $table_name_requestmanager;
    $get_group   = $objMem->selectRows($table_name_group, "", " where id='" . $gid . "'");
    $group_name  = $get_group[0]->title;
    $get_user    = $objMem->selectRows($table_name_requestmanager, "", " where id='" . $id . "'");
    $sendToname  = $get_user[0]->name;
    $sendToemail = $get_user[0]->email;
    $subject     = wpmg_dbStripslashes($info['title']);
    $message     = wpmg_dbStripslashes($info['description']);
    $message     = str_replace("{%name%}", $sendToname, $message);
    $message     = str_replace("{%email%}", $sendToemail, $message);
    $message     = str_replace("{%site_title%}", get_bloginfo('name'), $message);
    $message     = str_replace("{%site_email%}", get_bloginfo('admin_email'), $message);
    $message     = str_replace("{%site_url%}", get_site_url(), $message);
    $message     = str_replace("{%group_name%}", $group_name, $message);
    $headers     = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>' . "\r\n";
    wp_mail($sendToemail, $subject, $message, $headers);
}
/* New subscription arrived message to admin specified email */
function wpmg_sendmessagetoAdmin($name, $email, $grpsel)
{
    add_filter('wp_mail_content_type', 'wpmg_set_content_type');
    global $wpdb, $objMem, $table_name_group, $table_name_message, $table_name_requestmanager;
    $subscriptioncheck = $WPMG_SETTINGS["MG_SUBSCRIPTION_REQUEST_CHECK"];
    if ($subscriptioncheck) {
        $subscriptionemail = $WPMG_SETTINGS["MG_SUBSCRIPTION_REQUEST_ALERT_EMAIL"];
        $get_group         = $objMem->selectRows($table_name_group, "", " where id IN ($grpsel)");
        foreach ($get_group as $grp) {
            $group_selected .= $grp->title . ",  ";
        }
		$siteGroupUrl   = admin_url( 'admin.php?page=wpmg_mailinggroup_intro');
        $group_selected = wpmg_trimVal($group_selected, ",  ");
        $subject        = "New Subscription Request: " . $group_selected;
        $siteTitle      = get_bloginfo('name');
        $siteUrl        = home_url();
        $siteEmail      = get_bloginfo('admin_email');
        $loginURL       = wp_login_url();
        $headers        = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $get_message     = $objMem->selectRows($table_name_message, "", " where message_type = 'wpmg_sendmessagetoAdmin'");
        $message_subject = wpmg_dbStripslashes($get_message[0]->message_subject);
        $dataMessage     = wpmg_dbStripslashes($get_message[0]->description);
        $message         = nl2br(str_replace(array(
            '{%name%}',
            '{%email%}',
            '{%site_title%}',
            '{%group_name%}',
            '{%group_url%}',
            '{%site_url%}',
            '{%site_email%}',
            '{%login_url%}'
        ), array(
            $name,
            $email,
            $siteTitle,
            $group_selected,
            $siteGroupUrl,
            $siteUrl,
            $siteEmail,
            $loginURL
        ), $dataMessage));
        $subject         = str_replace(array(
            '{%name%}',
            '{%email%}',
            '{%site_title%}',
            '{%group_name%}',
            '{%group_url%}',
            '{%site_url%}',
            '{%site_email%}',
            '{%login_url%}'
        ), array(
            $name,
            $email,
            $siteTitle,
            $group_selected,
            $siteGroupUrl,
            $siteUrl,
            $siteEmail,
            $loginURL
        ), $message_subject);
        wp_mail($subscriptionemail, $subject, $message, $headers);
    }
}
/*  Redefine user notification function */
if (!function_exists('wp_new_user_notification')) {
    function wp_new_user_notification($user_id, $plaintext_pass = '')
    {
        global $wpdb, $objMem, $table_name_message;
        $siteTitle       = get_bloginfo('name');
        $siteUrl         = home_url();
        $siteEmail       = get_bloginfo('admin_email');
        $loginURL        = wp_login_url();
        $user            = new WP_User($user_id);
        $user_login      = stripslashes($user->user_login);
        $user_email      = stripslashes($user->user_email);
        $get_message     = $objMem->selectRows($table_name_message, "", " where message_type = 'RegistrationNotificationMailToAdmin'");
        $dataMessage     = wpmg_dbStripslashes($get_message[0]->description);
        $message_subject = wpmg_dbStripslashes($get_message[0]->message_subject);
        $message         = nl2br(str_replace(array(
            '{%name%}',
            '{%email%}',
            '{%site_title%}',
            '{%group_name%}',
            '{%group_url%}',
            '{%site_url%}',
            '{%site_email%}',
            '{%login_url%}'
        ), array(
            $user_login,
            $user_email,
            $siteTitle,
            $group_selected,
            $siteGroupUrl,
            $siteUrl,
            $siteEmail,
            $loginURL
        ), $dataMessage));
        $subject         = str_replace(array(
            '{%name%}',
            '{%email%}',
            '{%site_title%}',
            '{%group_name%}',
            '{%group_url%}',
            '{%site_url%}',
            '{%site_email%}',
            '{%login_url%}'
        ), array(
            $user_login,
            $user_email,
            $siteTitle,
            $group_selected,
            $siteGroupUrl,
            $siteUrl,
            $siteEmail,
            $loginURL
        ), $message_subject);
        $headers  = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>' . "\r\n";
        $headers .= 'Content-type: text/html; charset = UTF-8' . "\r\n";
        @wp_mail(get_option('admin_email'), $subject, $message, $headers);
        if (empty($plaintext_pass))
            return;
        $get_message     = $objMem->selectRows($table_name_message, "", " where message_type = 'RegistrationNotificationMailToMember'");
        $dataMessage     = wpmg_dbStripslashes($get_message[0]->description);
        $message_subject = wpmg_dbStripslashes($get_message[0]->message_subject);
        $message         = nl2br(str_replace(array(
            '{%name%}',
            '{%email%}',
            '{%password%}',
            '{%site_title%}',
            '{%group_name%}',
            '{%group_url%}',
            '{%site_url%}',
            '{%site_email%}',
            '{%login_url%}'
        ), array(
            $user_login,
            $user_email,
            $plaintext_pass,
            $siteTitle,
            $group_selected,
            $siteGroupUrl,
            $siteUrl,
            $siteEmail,
            $loginURL
        ), $dataMessage));
        $subject         = str_replace(array(
            '{%name%}',
            '{%email%}',
            '{%password%}',
            '{%site_title%}',
            '{%group_name%}',
            '{%group_url%}',
            '{%site_url%}',
            '{%site_email%}',
            '{%login_url%}'
        ), array(
            $user_login,
            $user_email,
            $plaintext_pass,
            $siteTitle,
            $group_selected,
            $siteGroupUrl,
            $siteUrl,
            $siteEmail,
            $loginURL
        ), $message_subject);
        wp_mail($user_email, $subject, $message, $headers);
    }
}
/*confirmation email for subscriber to verify account*/
function wpmg_sendConfirmationtoMember($id, $groupArray)
{
    add_filter('wp_mail_content_type', 'wpmg_set_content_type');
    global $wpdb, $objMem, $table_name_group, $table_name_message;
    $siteTitle    = get_bloginfo('name');
    $siteUrl      = home_url();
    $siteEmail    = get_bloginfo('admin_email');
    $loginURL     = wp_login_url();
    $user         = new WP_User($id);
    $display_name = stripslashes($user->display_name);
    $user_login   = stripslashes($user->user_login);
    $user_email   = stripslashes($user->user_email);
    $user_reg     = stripslashes($user->user_registered);
    if (count($groupArray) > 0) {
        foreach ($groupArray as $key => $value) {
            $get_group  = $objMem->selectRows($table_name_group, "", " where id='" . $key . "'");
            $group_name = $get_group[0]->title;
            $grouplist .= $group_name . ", ";
        }
        $grouplist = wpmg_trimVal($grouplist, ", ");
    }
    $activationURL   = wpmg_activation_url($id, $user_reg);
    $get_message     = $objMem->selectRows($table_name_message, "", " where message_type = 'Confirmationemailforsubscribertoverifyaccount'");
    $dataMessage     = wpmg_dbStripslashes($get_message[0]->description);
    $message_subject = wpmg_dbStripslashes($get_message[0]->message_subject);
    $message         = nl2br(str_replace(array(
        '{%displayname%}',
        '{%name%}',
        '{%email%}',
        '{%site_title%}',
        '{%group_name%}',
        '{%group_url%}',
        '{%site_url%}',
        '{%site_email%}',
        '{%activation_url%}',
        '{%login_url%}',
        '{%group_list%}'
    ), array(
        $display_name,
        $user_login,
        $user_email,
        $siteTitle,
        $group_selected,
        $siteGroupUrl,
        $siteUrl,
        $siteEmail,
        $activationURL,
        $loginURL,
        $grouplist
    ), $dataMessage));
    $subject         = str_replace(array(
        '{%displayname%}',
        '{%name%}',
        '{%email%}',
        '{%site_title%}',
        '{%group_name%}',
        '{%group_url%}',
        '{%site_url%}',
        '{%site_email%}',
        '{%activation_url%}',
        '{%login_url%}',
        '{%group_list%}'
    ), array(
        $display_name,
        $user_login,
        $user_email,
        $siteTitle,
        $group_selected,
        $siteGroupUrl,
        $siteUrl,
        $siteEmail,
        $activationURL,
        $loginURL,
        $grouplist
    ), $message_subject);
    $headers         = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>' . "\r\n";
    $headers .= 'Content-type: text/html; charset= UTF-8' . "\r\n";
    wp_mail($user_email, $subject, $message, $headers);
}
function wpmg_set_content_type($content_type)
{
    return 'text/html';
}
/*email user on successful registration of a group*/
function wpmg_sendGroupConfirmationtoMember($id, $groupArray)
{
    add_filter('wp_mail_content_type', 'wpmg_set_content_type');
    global $objMem, $table_name_group, $table_name_message;
    $siteTitle    = get_bloginfo('name');
    $siteUrl      = home_url();
    $siteEmail    = get_bloginfo('admin_email');
    $loginURL     = wp_login_url();
    $user         = new WP_User($id);
    $display_name = stripslashes($user->display_name);
    $user_login   = stripslashes($user->user_login);
    $user_email   = stripslashes($user->user_email);
    $user_reg     = stripslashes($user->user_registered);
    $i            = 1;
    if (count($groupArray) > 0) {
        foreach ($groupArray as $key => $value) {
            $get_group  = $objMem->selectRows($table_name_group, "", " where id='" . $key . "'");
            $group_name = $get_group[0]->title;
            $grouplist .= $group_name . ", ";
        }
        $grouplist = wpmg_trimVal($grouplist, ", ");
    }
    $get_message     = $objMem->selectRows($table_name_message, "", " where message_type = 'Emailuseronsuccessfullregisterationofagroup'");
    $dataMessage     = wpmg_dbStripslashes($get_message[0]->description);
    $message_subject = wpmg_dbStripslashes($get_message[0]->message_subject);
    $message         = nl2br(str_replace(array(
        '{%displayname%}',
        '{%name%}',
        '{%email%}',
        '{%site_title%}',
        '{%group_name%}',
        '{%group_url%}',
        '{%site_url%}',
        '{%site_email%}',
        '{%activation_url%}',
        '{%group_list%}',
        '{%login_url%}'
    ), array(
        $display_name,
        $user_login,
        $user_email,
        $siteTitle,
        $group_selected,
        $siteGroupUrl,
        $siteUrl,
        $siteEmail,
        $activationURL,
        $grouplist,
        $loginURL
    ), $dataMessage));
    $subject         = nl2br(str_replace(array(
        '{%displayname%}',
        '{%name%}',
        '{%email%}',
        '{%site_title%}',
        '{%group_name%}',
        '{%group_url%}',
        '{%site_url%}',
        '{%site_email%}',
        '{%activation_url%}',
        '{%group_list%}',
        '{%login_url%}'
    ), array(
        $display_name,
        $user_login,
        $user_email,
        $siteTitle,
        $grouplist,
        $siteGroupUrl,
        $siteUrl,
        $siteEmail,
        $activationURL,
        $grouplist,
        $loginURL
    ), $message_subject));
    $headers  = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>' . "\r\n";
    $headers .= 'Content-type: text/html; charset= UTF-8' . "\r\n";
    wp_mail($user_email, $subject, $message, $headers);
}
/* mail function used in plugin */
function wpmg_activation_url($user_id, $user_reg = "")
{
    $md5Userid       = md5($user_id);
    $md5UserRegister = md5($user_reg);
    return get_bloginfo('wpurl') . "?activationkey=$md5Userid&nonce=$md5UserRegister&verify=1";
}
/* frontend shortcode call */
function wpmg_mailing_group_form_func($atts)
{
    if(isset($atts) && !empty($atts)){
    $a = shortcode_atts(array(
        'visibility' => $atts['visibility']
    ), array(
        'visibility'
    ));
	}
    ob_start();
    global $wpdb, $objMem, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_group, $table_name_requestmanager;
    global $visibilityArray;
    if (!is_admin()) {
        wp_register_style('demo_table.css', plugin_dir_url(__FILE__) . 'css/demo_table.css');
        wp_enqueue_style('demo_table.css');
        wp_register_script('custom.js', plugin_dir_url(__FILE__) . 'js/custom.js', array(
            'jquery'
        ));
        wp_enqueue_script('custom.js');
        include "template/mg_user_form.php";
    }
    return ob_get_clean();
}
add_shortcode('mailing_group_form', 'wpmg_mailing_group_form_func');
add_filter('template_include', 'wpmg_check_user_activation_link', 1);
/* frontend shortcode call */
/* user activation link check & verify */
function wpmg_check_user_activation_link($template)
{
    global $wpdb, $objMem, $table_name_user_taxonomy;
    /* wpmg_activation_url(98, "2013-08-29 13:14:31"); */
    extract($_GET);
    $error = new WP_Error();
    if ($verify == '1' && $activationkey != '' && $nonce != '') {
        $result = $objMem->selectRows($wpdb->users, "", " where MD5(ID) = '" . $activationkey . "' and MD5(user_registered) = '" . $nonce . "' order by id desc");
        if ($result[0] && is_array($result)) {
            $user_status = $result[0]->user_status;
            if ($user_status == '2') {
                $user_id = $result[0]->ID;
                $wpdb->query("UPDATE $wpdb->users SET user_status = 0 WHERE ID =" . $user_id);
                update_user_meta($user_id, "User_status", 1);
                $random_password = wp_generate_password(12, false);
                wp_set_password($random_password, $user_id);
                wp_new_user_notification($user_id, $random_password);
                $gropArray   = get_user_meta($user_id, "Group_subscribed", true);
                $arrayString = unserialize($gropArray);
                wpmg_sendGroupConfirmationtoMember($user_id, $arrayString);
                $error->add('verified_success', __("<div align='center'>Thank you for your subscription.<br>Please check your email for your account login credentials, so you can update your preferences and profile.</div>"));
                echo $error->get_error_message("verified_success");
               /*  sleep(5);
                wpmg_redirectTo("wp-login.php","abs"); */
            } else {
                $error->add('already_verified', __("<div align='center'><strong>Verified</strong>: Account already verified, Please <a href='wp-login.php'>login here</a>.</div>"));
                echo $error->get_error_message("already_verified");
                wpmg_redirectTo("wp-login.php", "abs");
            }
        } else {
            $error->add('invalid_request', __("<div align='center'><strong>ERROR</strong>: Invalid verification request, Please contact administrator.</div>"));
            echo $error->get_error_message("invalid_request");
        }
    } else if ($unsubscribe == '1' && $userid != '' && $group != '') {
        extract($_GET);
        $group_arr_old = unserialize(get_user_meta($userid, "Group_subscribed", true));
        unset($group_arr_old[$group]);
        $grpserial = serialize($group_arr_old);
        update_user_meta($userid, "Group_subscribed", $grpserial);
        $objMem->updUserGroupTaxonomy($table_name_user_taxonomy, $userid, $group_arr_old);
        $error->add('success_unsubscribe', __("<div align='center'><strong>Success</strong>: You are successfully unsubscribed from the selected group.</div>"));
        echo $error->get_error_message("success_unsubscribe");
    } else {
        return $template;
    }
}
/* user activation link check & verify */
add_filter('authenticate', 'wpmg_user_signup_disable_inactive', 28);
/* disable user with status 2 to login */
function wpmg_user_signup_disable_inactive($user)
{
    /*  check to see if the $user has already failed logging in, if so return $user as-is */
    if (is_wp_error($user) || empty($user))
        return $user;
    if (is_a($user, 'WP_User') && 2 == $user->user_status)
        return new WP_Error('invalid_username', __("<strong>ERROR</strong>: You account has been deactivated."));
    return $user;
}
/* disable user with status 2 to login */
/*uninstall and deactivate code*/
function wpmg_mailing_group_deactivate()
{
    /* REMOVE CONFIG OPTION SET FROM OPTION TABLE*/
    /*delete_option( "MG_VERSION_NO" );
    
    delete_option( "MG_PLUGIN_TYPE" );
    
    delete_option( "MG_SUBSCRIPTION_REQUEST_CHECK" );
    
    delete_option( "MG_SUBSCRIPTION_REQUEST_ALERT_EMAIL" );
    
    delete_option( "MG_BOUNCE_CHECK" );
    
    delete_option( "MG_BOUNCE_CHECK_ALERT_TIMES" );
    
    delete_option( "MG_BOUNCE_CHECK_ALERT_EMAIL" );
    
    delete_option( "MG_CUSTOM_STYLESHEET" );
    
    delete_option( "MG_WEBSITE_URL" );
    
    delete_option( "MG_CONTACT_ADDRESS" );
    
    delete_option( "MG_SUPPORT_EMAIL" );
    
    delete_option( "MG_SUPPORT_PHONE" );*/
}
function wpmg_mailing_group_uninstall()
{
    global $wpdb, $table_name_group, $table_name_message, $table_name_requestmanager, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_parsed_emails, $table_name_sent_emails;
    $sql = "DROP TABLE `$table_name_group`, `$table_name_message`, `$table_name_requestmanager`, `$table_name_requestmanager_taxonomy`, `$table_name_user_taxonomy`, `$table_name_parsed_emails`, `$table_name_sent_emails`";
    /* //$wpdb->query($sql); // comment this if you want to keep the database tables after installation */
}
/*uninstall and deactivate code*/
/* hook to delete user taxonomy on deleting from wordpress */
add_action('delete_user', 'wpmg_delete_user_taxonomy');
function wpmg_delete_user_taxonomy($user_id)
{
    global $wpdb, $objMem, $table_name_user_taxonomy, $table_name_requestmanager, $table_name_requestmanager_taxonomy;
    $user_obj = get_userdata($user_id);
    $email    = $user_obj->user_email;
    $wpdb->query("delete from " . $table_name_user_taxonomy . " where user_id=" . $user_id);
    $get_subscription_taxonomy = $objMem->selectRows($table_name_requestmanager, "", " where email = '" . $email . "'");
    $subscriptoinid            = $get_subscription_taxonomy[0]->id;
    $wpdb->query("delete from " . $table_name_requestmanager_taxonomy . " where user_id = " . $subscriptoinid);
    $wpdb->query("delete from " . $table_name_requestmanager . " where id = " . $subscriptoinid);
}
function wpmg_custom_menu_hack()
{
    /* Custom menu hack */
	if(isset($_GET['page']) && !empty($_GET['page'])){
    $pagename  = sanitize_text_field($_GET['page']);
    $pageArray = array(
        "wpmg_mailinggroup_list",
        "wpmg_mailinggroup_add",
        "wpmg_mailinggroup_adminmessagelist",
        "wpmg_mailinggroup_memberlist",
        "wpmg_mailinggroup_memberadd",
        "wpmg_mailinggroup_requestmanagerlist",
        "wpmg_mailinggroup_requestmanageradd",
        "wpmg_mailinggroup_sendmessage",
        "wpmg_mailinggroup_intro",
        "wpmg_mailinggroup_messagelist",
        "wpmg_mailinggroup_messageadd",
        "wpmg_mailinggroup_importuser",
        "wpmg_mailinggroup_viewmessage",
        "wpmg_mailinggroup_style",
        "wpmg_mailinggroup_contact"
    );
	if ($pagename != "" && (in_array($pagename, $pageArray))) {
        wp_register_script('custommenu.js', plugin_dir_url(__FILE__) . 'js/custommenu.js', array(
            'jquery'
        ));
        wp_enqueue_script('custommenu.js');
    }
	}
}
add_action('admin_menu', 'wpmg_custom_menu_hack');
function wpmg_print_message($message, $is_error = false)
{
    if ($is_error)
        echo '<div id="message" class="error">';
    else
        echo '<div id="message" class="updated fade">';
    echo "<p><strong>Mailing Group Manager: $message</strong></p></div>";
}

function wpmg_get_user_role()
{
    global $current_user;
    $user_roles = $current_user->roles;
    $user_role  = array_shift($user_roles);
    return $user_role;
}
function wpmg_add_menu_icons_styles()
{
?>

<style>

#adminmenu .toplevel_page_mailinggroup_intro div.wp-menu-image:before { 

content: '\f237';

}

</style>
<?php
}
add_action('admin_head', 'wpmg_add_menu_icons_styles');
add_filter('authenticate', 'wpmg_bainternet_allow_email_login', 20, 3);
function wpmg_bainternet_allow_email_login($user, $username, $password)
{
    if (is_email($username)) {
        $user = get_user_by_email($username);
        if ($user)
            $username = $user->user_login;
    }
    return wp_authenticate_username_password(null, $username, $password);
}


add_action( 'wpmg_cron_task_send_email', 'wpmg_cron_send_email' );
require_once("crons/wpmg_cron_send_email.php");

add_action( 'wpmg_cron_task_parse_email', 'wpmg_cron_parse_email' );
require_once("crons/wpmg_cron_parse_email.php");

add_action( 'wpmg_cron_task_bounced_email', 'wpmg_cron_bounced_email' );
require_once("crons/wpmg_cron_bounced_email.php");

add_action( 'wpmg_cron_auto_delete_attachments', 'wpmg_auto_delete_attachments' );
include_once("crons/wpmg_cron_attachments.php");
?>