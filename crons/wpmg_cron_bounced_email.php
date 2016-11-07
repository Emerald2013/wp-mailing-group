<?php

defined('ABSPATH') or die("Cannot access pages directly.");

/*

 * Description: Cron to notify admin about bounced emails

 * Created: 08/2013

 * Author: axactsoft.com

 * Website: http://www.wpmailinggroup.com

 */

function wpmg_cron_bounced_email() {

global $wpdb, $objMem, $table_name_group, $table_name_message, $table_name_requestmanager, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_parsed_emails, $table_name_sent_emails, $table_name_crons_run, $memberLimit, $table_name_users, $table_name_usermeta;

 

require_once(WPMG_PLUGIN_PATH.'/lib/mailinggroupclass.php');

$objMem = new mailinggroupClass();



$WPMG_SETTINGS = get_option("WPMG_SETTINGS");

$mailresult = $objMem->selectRows($table_name_parsed_emails, "",  " where status = '0' and type='bounced' order by id desc limit 0, 1");

if(count($mailresult)>0) {

	

	/* get admin bounce settings */

	$bouncecheck           = $WPMG_SETTINGS["MG_BOUNCE_CHECK"];

	$bouncealerttimes      = $WPMG_SETTINGS["MG_BOUNCE_CHECK_ALERT_TIMES"];

	$bouncecheckalertemail = $WPMG_SETTINGS["MG_BOUNCE_CHECK_ALERT_EMAIL"];

	/* get admin bounce settings */

	

	foreach($mailresult as $emailParsed) {

		$bouncedGroupId = $emailParsed->email_group_id;

		$bouncedMailId = $emailParsed->id;

		$emailType = $emailParsed->type;

		$emailBounced = $emailParsed->email_bounced;

		

		$bouncedUser = $objMem->selectRows($table_name_users, "",  " where user_email='$emailBounced'");

		/* $bouncedUser = get_user_by("email", $emailBounced); */

		$bouncedUserId = $bouncedUser[0]->ID;

		if(is_numeric($bouncedUserId)) {

			

			/* entry in db for bounced emails */

			$myFields=array("id","user_id","email_id","group_id","sent_date","status");

			$_ARRDB['user_id'] = $bouncedUserId;

			$_ARRDB['email_id'] = $bouncedMailId;

			$_ARRDB['group_id'] = $bouncedGroupId;

			$_ARRDB['sent_date'] = date("Y-m-d H:i:s");

			$_ARRDB['status'] = "2";

			$objMem->addNewRow($table_name_sent_emails,$_ARRDB, $myFields);

			

			$fields = array("id","status");

			$grpinfo['id'] = $bouncedMailId;

			$grpinfo['status'] = "1";

			$objMem->updRow($table_name_parsed_emails,$grpinfo,$fields);

			/* entry in db for bounced emails */

			

			/*get user total bounced email count till now*/

			$mailresult = $objMem->selectRows($table_name_sent_emails, "",  " where user_id = '".$bouncedUserId."' and status = '2'");



			/*Notify to admin on crossing defined limits of bounce emails*/

			if($bouncecheck && count($mailresult) >= $bouncealerttimes) {

				

				$user_login = stripslashes( $bouncedUser[0]->user_login );

				$user_email = stripslashes( $bouncedUser[0]->user_email );

				

				$subject = "Bounced Email Alert from ".get_bloginfo('name');

				$message  = __('Dear Admin,','mailing-group-module') . "\r\n\r\n";

				$message .= sprintf( __("A user has exceeded the bounced email limit on %s! Here are the user details:",'mailing-group-module'), get_option('blogname')) . "\r\n\r\n";

				$message .= sprintf( __('Username: %s','mailing-group-module'), $user_login ) . "\r\n";

				$message .= sprintf( __('Email Address: %s','mailing-group-module'), $user_email ) . "\r\n\r\n";

				$message .= __('Thank you!','mailing-group-module');

				$headers = 'From: '.get_bloginfo('name').' <'.get_bloginfo('admin_email').'>' . "\r\n";

				mail(

					$bouncecheckalertemail,

					$subject,

					$message,

					$headers

				);

			}

			

		} else {

			

			$log_msg ="No user found by bounced email address";

		

		}

	}

} else {

	$log_msg ="No Bounced Email found!";

}

}