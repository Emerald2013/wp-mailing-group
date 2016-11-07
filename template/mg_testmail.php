<?php


/* get all variables */
	$_POST = stripslashes_deep( $_POST );
		$group_name=$_POST['group_name'];
		$email_format="";
		$table_name_group = $wpdb->prefix . "mailing_group";
		$result_groups = $objMem->selectRows($table_name_group, "", " order by id asc");
		$disabled = '';
		if(empty($result_groups)){$disabled = 'disabled'; $message = 'Please create a mailing group first.';}

if($_POST){

	if(($_POST['testmessage'])&& $_POST['subject'] && $_POST['group_name']) {
	
    	$from_email      = get_test_email();
		$noreply_email    = get_noreply_email();
		$receiverGroupId   = (isset($_REQUEST["group_name"])? sanitize_text_field($_REQUEST["group_name"]): '');
	
	/* get group details */
		$resultGroup = $objMem->selectRows($table_name_group, "",  " where id = '".$receiverGroupId."' order by id desc");
		$resultGroup = $resultGroup[0];

		$groupTitle = $resultGroup->title;
		$groupEmail = $resultGroup->email;
		$useinSubject = $resultGroup->use_in_subject;			
		$mail_type = $resultGroup->mail_type;
		$sendtouserId = $memberstoSent->user_id;
		$sendtouserEmailFormat = $memberstoSent->group_email_format;
		$sendToEmail = $groupEmail;

		
		
	$subject = sanitize_text_field($_REQUEST['subject']);


	$body = sanitize_text_field($_REQUEST['testmessage']);



	if($mail_type == 'smtp'){
								require_once(WPMG_PLUGIN_PATH.'/lib/class.phpmailer.php');						
								$mail = new PHPMailer();	
								$mail->IsSMTP(); 				
								$mail->SMTPDebug = 1; 		
		
								if($resultGroup->smtp_username!='' && $resultGroup->smtp_password!='') {	
									$mail->Username   = $resultGroup->smtp_username; 	
									$mail->Password   = $resultGroup->smtp_password; 
									$mail->SMTPAuth   = true;								
									$mail->SMTPSecure = "ssl";	
																	
								} else {				
									$mail->Username   = $resultGroup->email; 	
									$mail->Password   = $resultGroup->password; 
									$mail->SMTPAuth   = false;								
								}	
								$mail->Host    = $resultGroup->smtp_server; 		
								$mail->Port    = $resultGroup->smtp_port; 							
								$mail->Sender  = $resultGroup->email; 	
								$mail->SetFrom($from_email, "Wp Mailing Group");	
								/* reply to */
								$mail->AddReplyTo($noreply_email, $groupTitle);		
						
								if($useinSubject) {			
									$mail->Subject = "[".$groupTitle."] ".$subject;	
								} else {					
									$mail->Subject = $subject;
								}					
								if($sendtouserEmailFormat=='1') {	
									$mail->IsHTML(true);				
								} else {				
									$mail->IsHTML(false);	
								}						
								$mail->MsgHTML($body);			
								$mail->AddAddress($sendToEmail, $sendToName);	
					
						if(!$mail->Send()) {				
									$_ARRDB['status']    = "0";	
									$_ARRDB['error_msg'] = $mail->ErrorInfo;	
								} else {
									wpmg_showmessages("updated", __( "Test Mail Sent Succesfully!", 'mailing-group-module') );								
									$_ARRDB['status'] = "1";	
								}						
							}      			
							if($mail_type == 'php'){	
								if($useinSubject) {				
									$mail_Subject = "[".$groupTitle."] ".$subject;	
								} else {								
									$mail_Subject = $subject;	
								}	
								
								$to = $sendToEmail;	
								$subject = $mail_Subject;
							   
								$headers = 'From: Wp Mailing Group Test <'.$from_email.'>'."\r\n"; 
								$headers .= 'Reply-To: '.$groupTitle .' <'.$noreply_email.'>'."\r\n";  
								/* $headers .= 'Cc: '. $sendToName .'<'.$sendToEmail.'>'."\r\n"; */
								$headers .= 'X-Mailer: PHP' . phpversion() . "\r\n";
								$headers .= 'MIME-Version: 1.0'."\r\n";
								$headers .= 'Content-Type: ' . get_bloginfo('html_type') . '; charset=\"'. get_bloginfo('charset') . '\"'."\r\n";
								if($sendtouserEmailFormat=='1') {	
								   $headers .= 'Content-type: text/html'."\r\n";				
								}else{
								   $headers .= 'Content-type: text/plain'."\r\n";
								} 	
								
								$php_sent = mail($to, $subject, $body, $headers);

								if($php_sent) {				

								wpmg_showmessages("updated", __( "Test Mail Sent Succesfully!", 'mailing-group-module') );	
								}




							}
							
				if($mail_type == 'wp'){	
								if($useinSubject) {				
								$mail_Subject = "[".$groupTitle."] ".$subject;		
								} else {						
								$mail_Subject = $subject;	
								}	
								
								$to = $sendToEmail;	
								$subject = $mail_Subject;
								$headers[] = 'From: Wp Mailing Group Test <'.$from_email.'>'."\r\n";
								$headers[] = 'Reply-To: '. $groupTitle .' <'.$noreply_email.'>'."\r\n";
								/* $headers[] = 'Cc: '. $sendToName .'<'.$sendToEmail.'>'."\r\n"; */
								$headers[] = 'X-Mailer: PHP' . phpversion() . "\r\n";
								$headers[] = 'MIME-Version: 1.0'."\r\n";
								$headers[] = 'Content-Type: ' . get_bloginfo('html_type') . '; charset=\"'. get_bloginfo('charset') . '\"'."\r\n";
								if($sendtouserEmailFormat=='1') {	
								   $headers[] = 'Content-type: text/html;  charset=utf-8'."\r\n";				
								}else{
								   $headers[] = 'Content-type: text/plain;  charset=utf-8'."\r\n";
								} 	
								
												
								$wp_sent = wp_mail($to,$mail_Subject,$body,$headers);

							
								if($wp_sent) {	
							wpmg_showmessages("updated", __( "Test Mail Sent Succesfully!", 'mailing-group-module') );							
								$_ARRDB['status'] = "1";		
								} else {						
								$_ARRDB['status'] = "0";	
								$_ARRDB['error_msg'] = $mail->ErrorInfo;	
								}							
							}			
							
							
						}else{
		
							
							wpmg_showmessages('error', __( "Please fill all fields!", 'mailing-group-module') );
							
						}
						
						}
?>

<style>


.dataTables_info {


	display:none;


}


.check_div {


	width:400px;


}


.col-left-2 {


	width:100% !important;


}


</style>


<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub">


	<div class="icon32" id="icon-edit"><br/></div>


    <h2><?php _e("Test Email", 'mailing-group-module'); ?></h2>
<div class="div800">
<?php _e("You can test the 'Send Email' function here, to verify that your mailing group’s outgoing mail settings are functioning correctly. It will use the exact settings you have input for the mailing group you select below, and this test email will be sent to all subscribers of the selected mailing group.
If you have not yet set up a mailing group, please ensure you do so, and add at least one member to it before you run this test.", 'mailing-group-module'); ?>
</div>
    <div id="col-left-2">


        <div class="col-wrap">


            <div>



                <div class="form-wrap">


                    <form class="validate" action="" method="post" id="testmail">



    					<div class="form-field" id="gen_username">


                            <label for="tag-name"><?php _e("Subject", 'mailing-group-module'); ?> : </label>


                            <input type="text" size="40" id="subject" name="subject"  value=""/>


                        </div>


                        <div class="form-field">


                            <label for="tag-name"><?php _e("Message", 'mailing-group-module'); ?> : </label>


                            <textarea size="40" id="testmessage" name="testmessage" cols="38"></textarea>


                        </div>


                       

                        <div class="form-field">


                            <label for="tag-name"><?php _e("Group Name", 'mailing-group-module'); ?> : </label>


                            <div class="check_div">


                            	<table class="wp-list-table widefat fixed" id="memberaddedit">


                                	<thead>


                                        <tr role="row" class="topRow">


                                            <th class="sort topRow_messagelist"><?php _e("Mailing Group Name", 'mailing-group-module'); ?></th>




                                        </tr>


                                    </thead>


                                    <tbody>


									<?php


									foreach($result_groups as $group) {


										$checkSelected = false;


									?>


                                        <tr>


                                        	<td><input type="radio" name="group_name" id="selector" value="<?php echo $group->id; ?>" <?php echo ($checkSelected?"checked":($gid==$group->id?"checked":"")) ?> />&nbsp;<?php echo $group->title; ?>


                                            </td>



                                        </tr>


                                    <?php } ?>


                                    	</tbody>


                            	</table>


                            </div>


                        </div>


       


                        <div class="clearbth"></div>




                     


                        <div class="clearbth"></div>


                        <p class="submit">


                            <input type="submit" id="test_email" value="Send Email" class="button" <?php echo $disabled; ?>  name="submit"/>
                            <?php if(!empty($message)){ ?>
					<br/><?php wpmg_showmessages('error',$message); ?>
                    <?php } ?>

  
                        </p>


                    </form>
              <div>
<h2>PHP Settings</h2>
PHP Version : <?php echo phpversion(); ?><br/>
Function imap_open(): <?php 
/* get all variables */
if (function_exists('imap_open')) {
    echo "Available.<br />\n";
} else {
    echo "Not Available.<br />\n";
}

?>
</div>
                    
                   
<div class="div800" style="margin-top:20px;">                      
More Troubleshooting functions will be added here, as the plugin is developed further. 
If you have specific requests for this, please let us know on the Contact page at: <a target='_blank' href='http://www.wpmailinggroup.com'>www.wpmailinggroup.com</a></div>


                </div>


            </div>


        </div>


    </div>


</div>