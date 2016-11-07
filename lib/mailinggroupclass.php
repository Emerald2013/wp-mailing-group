<?php	class mailinggroupClass	{		function addNewRow($tblname,$grpinfo, $fields)		{			global $wpdb;

			$count = sizeof($grpinfo);

			if($count>0)

			{

				$id=0;

				$field="";

				$vals="";



				foreach($fields as $key)

				{

					if(is_array($grpinfo[$key])) {

						$exp = implode(",", $grpinfo[$key]);

						if($field=="")

						{

							$field="`".$key."`";

							$vals=$vals.",'".wpmg_dbAddslashes($exp)."'";

						}

						else

						{

							$field=$field.",`".$key."`";

							$vals=$vals.",'".wpmg_dbAddslashes($exp)."'";

						}

					} else {

						if($field=="")

						{

							$field="`".$key."`";

							$vals="'".wpmg_dbAddslashes(wpmg_trimVal($grpinfo[$key]))."'";

						}

						else

						{

							$field=$field.",`".$key."`";

							$vals=$vals.",'".wpmg_dbAddslashes(wpmg_trimVal($grpinfo[$key]))."'";

						}

					}

				}



				$sSQL = "INSERT INTO ".$tblname." ($field) values ($vals)";

				/* mysql_query($sSQL) or die (mysql_error().'Error, query failed'); */

				$wpdb->query($sSQL);

				return $lastid = $wpdb->insert_id;

			}

			else

			{

				return false;

			}

		}



		function updRow($tblname,$grpinfo,$fields)

		{

			global $wpdb;

			$count = sizeof($grpinfo);

			if($count>0)

			{

				$field="";

				$vals="";

				foreach($fields as $key)

				{

					if(is_array($grpinfo[$key])) {

						$exp = implode(",", $grpinfo[$key]);

						if($field=="" && $key!="id")

						{

							$field="`".$key."` = '".wpmg_dbAddslashes(wpmg_trimVal($exp))."'";

						}

						else if($key!="id")

						{

							$field=$field.",`".$key."` = '".wpmg_dbAddslashes(wpmg_trimVal($exp))."'";

						}

					} else {

						if($field=="" && $key!="id")

						{

							$field="`".$key."` = '".wpmg_dbAddslashes(wpmg_trimVal($grpinfo[$key]))."'";

						}

						else if($key!="id")

						{

							$field=$field.",`".$key."` = '".wpmg_dbAddslashes(wpmg_trimVal($grpinfo[$key]))."'";

						}

					}

				}



				$sSQL = "update ".$tblname." set $field where id='".$grpinfo["id"]."'";

				/* mysql_query($sSQL) or die (mysql_error().'Error, query failed'); */

				$wpdb->query($sSQL);

				return true;

			}

			else

			{

				return false;

			}

		}

		function selectRows($tblname,$id="",$extra="")

		{

			global $wpdb;

			$subStr ="";

			if($id>0)

			{

				$subStr =  " where id='$id'";

			}


			
			$sSQL = "select * from ".$tblname . $subStr . $extra; 

			$res = $wpdb->get_results($sSQL);

			return $res;

		}

		function selectRowsCompleteQuery($query)

		{

			global $wpdb;

			$res = $wpdb->get_results($query);

			return $res;

		}

		function selectRowsbyField($tblname,$by,$id="",$extra="")

		{

			global $wpdb;

			$subStr ="";

			if($id!='')

			{

				$subStr =  " where $by='$id'";

			}

			$sSQL = "select * from ".$tblname . $subStr . $extra;

			$res = $wpdb->get_results($sSQL);

			return $res;

		}

		

		function checkRowExists($tblname, $field, $grpinfo, $extracheck="") {

			global $wpdb;

			if($field!="")

			{

				$substr = "";

				if($extracheck="idCheck") {

					$substr = " and id!='".$grpinfo['id']."'";

				}

				$sSQL = "select * from ".$tblname." where ".$field."='".wpmg_dbAddslashes(wpmg_trimVal($grpinfo[$field]))."' $substr";

				$res = $wpdb->get_results($sSQL);

				if(sizeof($res)) {

					return true;

				} else {

					return false;

				}

			} else {

				return false;

			}

		}

		

		function getUserGroup($tblname,$id,$type='0') {

			global $wpdb;

			$sSQL = "select * from ".$tblname." where user_id='".$id."'";

			$res = $wpdb->get_results($sSQL);

			if(count($res)>0) {

				foreach($res as $resg) {

					$arrresult[$resg->group_id] = $resg->group_email_format;

				}

				return $arrresult;

			}

		}

		

		function getGroupUserCount($tblname,$id) {

			global $wpdb;

			$sSQL = "select * from ".$tblname." where group_id='".$id."'";

			return $res = $wpdb->get_results($sSQL);

		}

		

		function getCompleteUserGroups($tblname, $tblnameuser,$id) {

			global $wpdb;

			$sSQL = "select t1.*,t2.* from ".$tblname." t1 inner join ".$tblnameuser." t2 on t1.group_id = t2.id and t1.user_id='".$id."'";

			$res = $wpdb->get_results($sSQL);

			if(count($res)>0) {

				foreach($res as $resg) {

					$arrresult[] = $resg;

				}

				return $arrresult;

			}

		}

		function addUserGroup($tblname,$id,$grpinfo) {

			global $wpdb;

			$myFields="id,user_id,group_id,group_email_format";

			if(count($grpinfo['group_name'])>0) {

				foreach($grpinfo['group_name'] as $key => $group_id) {

					$emailformat = $grpinfo['email_format_'.$group_id];

					$sSQL = "INSERT INTO ".$tblname." ($myFields) VALUES ('',$id,'$group_id','$emailformat')";

					/* mysql_query($sSQL) or die (mysql_error().'Error, query failed'); */

					$wpdb->query($sSQL);

				}

			}

			return true;

		}

		function getGroupSerialized($grpinfo) {

			global $wpdb;

			if(count($grpinfo['group_name'])>0) {

				foreach($grpinfo['group_name'] as $key => $group_id) {

					$emailformat = $grpinfo['email_format_'.$group_id];

					$arrresult[$group_id] = $emailformat;

				}

			}

			return $arrresult;

		}

		function deleteUserGroup($tblname,$groupid,$userid) {

			global $wpdb;

			if($groupid!='' && $userid!='') {

				$sSQL = "DELETE FROM ".$tblname." WHERE user_id = '".$userid."' and group_id = '".$groupid."'";

				/* mysql_query($sSQL) or die (mysql_error().'Error, query failed'); */

				$wpdb->query($sSQL);

			}

			return true;

		}

		function updUserGroup($tblname,$id,$grpinfo) {

			global $wpdb;

			$myFields="id,user_id,group_id,group_email_format";

			$getCurrentGroups = $this->getUserGroup($tblname,$id,'1');

			if(count($grpinfo['group_name'])>0 && $getCurrentGroups) {

				foreach($grpinfo['group_name'] as $key => $group_id) {

					$emailformat = $grpinfo['email_format_'.$group_id];

					if(!in_array($group_id,$getCurrentGroups)) {

						$sSQL = "INSERT INTO ".$tblname." ($myFields) values ('',$id,'$group_id','$emailformat')";

						/* mysql_query($sSQL) or die (mysql_error().'Error, query failed'); */

						$wpdb->query($sSQL);

					}

				}

			} else {

				$this->addUserGroup($tblname,$id,$grpinfo);

			}

			return true;

		}

		function addUserGroupTaxonomy($tblname, $id, $arrtoInsert) {
			global $wpdb;
			if(count($arrtoInsert)>0) {

				$myFields="id,user_id,group_id,group_email_format";

				foreach($arrtoInsert as $group_id => $emailformat) {
				
				    $sSQL = "select * from ".$tblname." where user_id='".$id."' and group_id='".$group_id."'";
                    $res = $wpdb->get_results($sSQL);
					if(count($res)>0){
					    $sSQLdel = "DELETE FROM ".$tblname." WHERE user_id = '".$id."'  and group_id='".$group_id."'";
						$wpdb->query($sSQLdel);
						
						$uSQL = "INSERT INTO ".$tblname." ($myFields) values ('',$id,'$group_id','$emailformat')";
						$wpdb->query($uSQL);						
				 	}else{
						$sSQL = "INSERT INTO ".$tblname." ($myFields) values ('',$id,'$group_id','$emailformat')";
						$wpdb->query($sSQL);					
					}
				}

			}

		}

		function updUserGroupTaxonomy($tblname, $id, $arrtoInsert) {

			global $wpdb;

			$sSQLdel = "DELETE FROM ".$tblname." WHERE user_id = '".$id."'";

			$wpdb->query($sSQLdel);

			if(count($arrtoInsert)>0) {

				$myFields="id,user_id,group_id,group_email_format";

				foreach($arrtoInsert as $group_id => $emailformat) {

					$sSQL = "INSERT INTO ".$tblname." ($myFields) values ('',$id,'$group_id','$emailformat')";

					$wpdb->query($sSQL);

				}

			}

		}

	}

?>