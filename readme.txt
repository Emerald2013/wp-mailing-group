=== Mailing Group Listserv ===
Contributors: Yumna Tatheer.
Donate link: http://www.wpmailinggroup.com
Tags: mailing group, user subscription, cross mailing, listserv
Requires at least: 3.0.1
Tested up to: 4.6.1
Stable tag: 1.3.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Creating a Mailing Group on your WP site to which users can be subscribed, and any messages sent to the group's email address from group members will automatically be forwarded to all the members.

== Description ==

The WP MailingGroup plugin allows you to run a Mailing Group, also known as a Listserv in web speak, right from your WordPress website. This means you can sign up your users, friends, neighbours, family and whoever else you want, directly from your WordPress administration area, and they can then all exchange emails via their favourite email software. It relies on the WordPress Cron, and can also be configured to email checking  sending frequencies of your choice if you have access to an actual Cron manager on your web server (outside of WordPress).This is a true Mailing Group, just like on Yahoo Groups or Google Groups, where there is an email address to send messages to, and everyone who is subscribed to the mailing group gets the message. They can then click Reply, and the whole list will receive their response. This is NOT a one-way Announcement list where only YOU can email everyone else. This plugin is to help you and your groups stay connected!

See www.WPMailingGroup.com for FAQs and more.

== Installation ==
1. Unzip / Unrar the plugin folder and copy all the files to YOUR_SITE/wp-content/plugins/ folder.
2. You can also use the WordPress plugin uploader to do so via Plugins > Add new
3. Activate the plugin through the Plugins page in WordPress
4. Add a new Mailing Group from the plugin's control panel, inserting the relevant mail box details. 
5. Insert the registration form shortcode on a page or widget on your website if you wish for visitors to be able to subscribe to Mailing Groups via your website:

[mailing_group_form]
6. Optional (for low to medium traffic websites only): 
Paste the following command into the cron script manager on your server:
wget http://www.YOUR_SITE.com/wp-cron.php

On some systems, you may need to use a curl function instead: 
curl -s http://www.YOUR_SITE.com/wp-cron.php

For either function, the suggested frequency is every 2 minutes.
You will need to adjust the path according to where your WordPress installation is located. The above paths are for root level installations.

Full information on this can be found in the plugin’s General Settings > Help panel.

There are various methods to set the crons on your server. Here are a few for your reference:

From cPanel:http://docs.cpanel.net/twiki/bin/view/AllDocumentation/CpanelDocs/CronJobs

From Plesk:http://www.hosting.com/support/plesk/crontab/

From Telnet / Putty / Command line (for Advanced Users):

http://www.web-site-scripts.com/knowledge-base/article/AA-00484/0/Setup-Cron-job-on-Linux-UNIX-via-command-line.html

== Frequently Asked Questions === 

How many subscribers can I have in the mailing group? =You can add up to 20 subscribers in ONE mailing group using the free plugin.

= Can I create my own customised messages to send to prospective subscribers? = 

Yes, you can: go to Mailing Group Manager > General Settings, and select the Custom Messages tab. There you can input your custom message (using the listed variables, if you are technical!). 
You can also go to Mailing Group Manager > Subscription Requests, and click the message icon next to a subscription request. This opens up a popup window where you can type in a custom message, and check the box at the bottom that allows you to save it for repeated use.

= I do not have access to cron scripts on my shared server. Can I still use the plugin? =

If you do not have cron access, then you will have to rely on visitors to your site to trigger the in-built WP-cron, which checks for new messages and distributes them to the list of subscribers. For example, if you have a visitor on average every 10 minutes, then the Mailing Group messages will be received and sent every 10 minutes. If you have Cron access, you can set a higher frequency of 2 minutes to keep the Mailing Group updated more often (see Installation instructions above), but without cron access, it may just run more slowly.  

== Screenshots ==

1. Screenshot-1.png - Your Mailing Group can be added and configured and only one Mailing Group is available in this Free plugin.
2. Screenshot-2.png - Add subscribers to the mailing group.
3. Screenshot-3.png - Shows the list of subscribers in the mailing group and buttons to activate their membership.
4. Screenshot-4.png - Import subscribers to the mailing group from Excel (VCF import is available in the Premium plugin).

== Changelog ==

= 1.3.7 =

* Removed sql query for setting utf-8 because not wokred on majority servers.
* Fixed a critical bug related to user import.

= 1.3.6 =

* Added filter to modify mailbox connection string.
* Ensure the connection to sql is utf-8.
* SMTP mail troubleshooting fix.

= 1.3.5 =

* Added import users function for each group individually
* Added option to delete users from mailing group only, or totally from WP system
* Fixed attachment name bug for invalid attachment names
* Fixed foreign language issues for subject and email body
* Changed subject and email character and transfer encoding to UTF-8
* Added a test email page to test group email

= 1.3.4 =

* Fixed import duplicate user issue.
* Updated languages files. 
* Updated character issues.
* Resolved attachment file url issue.

= 1.3.3 =
* Fixed plugin parsed email bug.
* Implement German language translation.

= 1.3.2 =

* Fixed import duplicate user issue.
* Updated languages files. 
* Resolved free to premium update bug.
* Updated character issues.
* Resolved attachment file url issue.

= 1.3.1 =
* Fixed plugin update bug.

= 1.3 =
* Updated save attachment module.
* Resolved bug fixes and formatting.

= 1.2.2 =
* Resolved updated plugin version issue.
* Resolved attachment issue and added attachment settings.
* Fixed mailing charactor issue.

= 1.2.1 =
* Resolved import csv file issue.
* Fixed plugin update bug.

= 1.2 =
* Fixed mailing reply-to Group email issue. 
* Resolved Group member delete issue.

= 1.1 =
* Additional settings for Mail checking and sending: separate field for username.
* Fix for “has_cap Deprecated” notices.

= 1.0 =
* First version of the plugin released.

