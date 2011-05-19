<h1>Delete Event Types</h1>
<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/configuration/organisations/manage/eventtypes?section=delete&amp;id=".$ORGANISATION['organisation_id'], "title" => "Delete Eventtypes");

if(isset($_POST["remove_ids"]) && count($_POST["remove_ids"]) > 0){
	foreach($_POST["remove_ids"] as $id){
		
		$query = "SELECT COUNT(*) FROM `eventtype_organisation` WHERE `eventtype_id` = ".$db->qstr($id);
		
		$num_uses = $db->GetOne($query);
		
		$query = "DELETE FROM `eventtype_organisation` WHERE `eventtype_id` = ".$db->qstr($id);
		if($num_uses > 1)
			$query .= " AND	`organisation_id` = ".$db->qstr($ORGANISATION_ID);
		if($db->Execute($query)){
			$SUCCESS++;
			$SUCCESSSTR[] = "Successfully removed Event Type [".$id."] from your organisation.<br/>";
		}
		if($num_uses > 1){
			$NOTICE++;
			$NOTICESTR[] = "This Event Type still exists in the system because other Organisations were using it.<br/>You will now be redirected to the Event Type index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/configuration/organisations/manage/eventtypes/?id=".$ORGANISATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
		}
		else{
			$query = "UPDATE `events_lu_eventtypes` SET	`eventtype_active`=0 WHERE `eventtype_id` = ".$db->qstr($id);
			if($db->Execute($query)){
				$SUCCESS++;
				$SUCCESSSTR[] = "Successfully removed Event Type [".$id."] from your the system.<br/>You will now be redirected to the Event Type index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/configuration/organisations/manage/eventtypes/?id=".$ORGANISATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
			}
			else{
				$ERROR++;
				$ERRORSTR[] = "An error occurred while removing the Event Type [".$id."] from the system. The system administrator has been notified.You will now be redirected to the Event Type index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/configuration/organisations/manage/eventtypes/?id=".$ORGANISATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
				application_log("error", "An error occurred while removing the Event Type [".$id."] from the system. ");
			}
		}
	}


	if($SUCCESS)
		echo display_success();
	if($NOTICE)
		echo display_notice();
}
else{
	$ERROR++;
	$ERRORSTR[] = "No Event Types were selected to be deleted. You will now be redirected to the Event Type index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/configuration/organisations/manage/eventtypes/?id=".$ORGANISATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
	
	echo display_error();
}
	$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/configuration/organisations/manage/eventtypes/?id=".$ORGANISATION_ID."\\'', 5000)";
?>
