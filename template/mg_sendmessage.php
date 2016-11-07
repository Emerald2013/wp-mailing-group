<?php
/* get all variables */
$actreq = sanitize_text_field($_REQUEST["act"]);
$gid = sanitize_text_field($_REQUEST['gid']);
$addme = sanitize_text_field($_REQUEST["addme"]);
$id = sanitize_text_field($_REQUEST['id']);$_POST = stripslashes_deep( $_POST );
/* get all variables */
if($actreq == 'getMess') {
	$get_message = $objMem->selectRows($table_name_message, "", " where id='".$gid."'");
	foreach($get_message as $messg) {
		$response = array("id"=>wpmg_dbStripslashes($messg->id),"title"=>wpmg_dbStripslashes($messg->title),"description"=>wpmg_dbStripslashes($messg->description));
	}
	wp_send_json($response);
	exit;
}
if($addme==1) {
	$sql = "UPDATE `$table_name_requestmanager` SET message_sent = message_sent + 1 WHERE id = '".$id."'";
	$wpdb->query($sql);
	wpmg_sendmessagetoSubscriber($gid, $id, $_POST);
	if($_POST['savetopreset']=='1') {
		$myFields=array("id","title","description","status");
		$objMem->addNewRow($table_name_message,$_POST, $myFields);
		wpmg_showmessages("updated", __("Message has been sent to user successfully", 'mailing-group-module'));
		/* //wpmg_redirectTo("wpmg_mailinggroup_requestmanagerlist&info=sent");
		//exit; */
	} else {
		wpmg_showmessages("updated", __("Message has been sent to user successfully.", 'mailing-group-module'));
		/* //wpmg_redirectTo("wpmg_mailinggroup_requestmanagerlist&info=sent");
		//exit; */
	}
} 
?>
<script type="application/javascript">
	jQuery(document).ready(function() {jQuery('#selectmessage').change(function(){
		var thisId = this.value;
		if(thisId=="0" || thisId=="") {
			jQuery("#title").val("");
			jQuery("#description").val("");
			jQuery("#title").focus();
			return false;
		}
		var data = { action: 'wpmg_sendmessage', page: 'wpmg_mailinggroup_sendmessage',gid:thisId,act:"getMess",dataType:"json"}; jQuery.post(ajaxurl, data, function(response) {
				if(response!='' && response!=null) {
					jQuery("#title").val(response.title);
					jQuery("#description").val(response.description);
				}
			});
		});
	});
</script>
<?php $result_message = $objMem->selectRows($table_name_message, "", " where status='1' order by id asc"); ?>
<style>
	body {
		min-width:495px !important;
	}
	#adminmenu {
		display:none !important;
	}
	#adminmenuback {
		display:none !important;
	}
	#wpadminbar {
		display:none !important;
	}
	#wpfooter {
		display:none !important;
	}
	#wpcontent {
		margin-left:10px !important;
	}
	.form-table th, .form-wrap label {
		width:90px !important;
	}
	.marginleft {
		margin-left: 115px !important;
	}
	#wpbody-content {
		padding-bottom:0px !important;
	}
	.wrap {
		margin: 0 15px 0 0 !important;
	}
	.marginbottom {
		margin-bottom:10px;
	}
	.form-field input[type="text"], .form-field textarea {
		width:335px !important;
	}
	.form-field input[type="checkbox"] {
		height:22px !important;
		width:24px !important;
	}
</style>
<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub" id="inline_form">
	<div class="icon32" id="icon-edit"><br/></div>
    <h2><?php _e("Send Message", 'mailing-group-module'); ?></h2>
    <div id="col-left-pop">
        <div class="col-wrap">
            <div>
                <div class="form-wrap">
                    <form class="validate" action="" method="post" id="sendmessage">
                        <div class="form-field">
                            <label for="tag-name"><?php _e("Message", 'mailing-group-module'); ?> : </label>
                            <select name="selectmessage" id="selectmessage">
                            	<option value=""><?php _e("Select Message", 'mailing-group-module'); ?></option>
                                <option value="0"><?php _e("New Message", 'mailing-group-module'); ?></option>
                                <?php foreach($result_message as $message) { ?>
                                	<option value="<?php echo $message->id; ?>"><?php echo wpmg_dbStripslashes($message->title); ?></option>
                                <?php } ?>
                            </select>
                        </div>
    					<div class="form-field marginbottom">
                            <label for="tag-name"><?php _e("Title", 'mailing-group-module'); ?> : </label>
                            <input type="text" size="40" id="title" name="title" value="<?php echo $title; ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="tag-name"><?php _e("Description", 'mailing-group-module'); ?> : </label>
                            <textarea name="description" rows="8" cols="50" id="description"><?php echo $description; ?></textarea>
                        </div>
                        <div class="form-field marginleft">
                            <input type="checkbox" name="savetopreset" value="1" />&nbsp;<?php _e("Save to Preset Messages", 'mailing-group-module'); ?>
                        </div>
                        <div class="clearbth"></div>
                        <div class="variableslist_pop">
                            <p>{%name%} = <?php _e("User's Name", 'mailing-group-module'); ?>,</p>
                            <p>{%email%} = <?php _e("User's Email", 'mailing-group-module'); ?></p>
                            <p>{%site_email%} = <?php _e("Site's Email", 'mailing-group-module'); ?></p>
                            <p>{%site_title%} = <?php _e("Site's Title", 'mailing-group-module'); ?></p>
                            <p>{%site_url%} = <?php _e("Site's Web Address", 'mailing-group-module'); ?></p>
                            <p>{%group_name%} = <?php _e("Current Group Name", 'mailing-group-module'); ?></p>
                        </div>
                        <p class="submit">
                            <input type="submit" value="Submit" class="button" id="submit" name="submit"/>
                            <input type="hidden" name="addme" value="1" >
                            <input type="hidden" name="id" value="<?php echo $id; ?>" >
                            <input type="hidden" name="status" value="1" >
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>