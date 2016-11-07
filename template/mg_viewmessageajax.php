<?php
/* get all variables */
$id = sanitize_text_field($_REQUEST['id']);
/* get all variables */
$mailresult = $objMem->selectRows($table_name_parsed_emails, "",  " where id = '".$id."'");
?>
<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub" id="inline_form">
	<div class="icon32" id="icon-edit"><br/></div>
    <h2><?php _e("View Message", 'mailing-group-module'); ?></h2>
    <div id="col-left-pop">
        <div class="col-wrap">
            <div style="width:495px;">
                <div class="form-wrap">
                    <?php
					/*echo "Subjects :: ".$mailresult[0]->email_subject."<br>";
					echo "TO :: ".$mailresult[0]->email_to."<br>";
					echo "To Other :: ".$mailresult->email_to."<br>";
					echo "ToName Other :: ".$mailresult[0]->email_to."<br>";
					echo "From :: ".$mailresult[0]->email_from."<br>";
					echo "FromName :: ".$mailresult[0]->email_from_name."<br>";
					echo "<br><br>";
					echo "<br>*******************************************************************************************<BR>";*/
					echo $mailresult[0]->email_content;  /* // Get Body Of Mail number Return String Get Mail id in interger */
					?>
                </div>
            </div>
        </div>
    </div>
</div>