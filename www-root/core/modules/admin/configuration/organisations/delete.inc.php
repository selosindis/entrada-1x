<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if((isset($_POST["remove_ids"])) && (count($_POST["remove_ids"])>0)){

	foreach($_POST["remove_ids"] as $id){
		$query = "DELETE FROM `".AUTH_DATABASE."`.`organisations` WHERE `organisation_id` = ".$db->qstr($id);
		if ($db->Execute($query) && ($db->Affected_Rows() == 1)) {
			$query = "DELETE FROM `eventtype_organisation` WHERE `organisation_id` = ".$db->qstr($id);
			if ($db->Execute($query)) {
				$query = "DELETE FROM `objective_organisation` WHERE `organisation_id` = ".$db->qstr($id);
				if ($db->Execute($query)) {
					$SUCCESS++;
					$SUCCESSSTR[] = "You have successfully removed organisation <strong>".$id."</strong> from the system.<br /><br />You will now be redirected to the organisations index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/configuration/organisations\" style=\"font-weight: bold\">click here</a> to continue.";
					$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/configuration/organisations/\\'', 5000)";
					application_log("success", "Removed information for the organisation [".$id."].");
				} else {
				$ERROR++;
				$ERRORSTR[]	= "We were unable to remove organisation ".$id." from the system at this time.<br /><br />The system administrator has been notified of this issue, please try again later.";
				application_log("error", "Failed to remove organisation from the database. Database said: ".$db->ErrorMsg());
				}
			} else {
			$ERROR++;
			$ERRORSTR[]	= "We were unable to remove organisation ".$id." from the system at this time.<br /><br />The system administrator has been notified of this issue, please try again later.";
			application_log("error", "Failed to remove organisation from the database. Database said: ".$db->ErrorMsg());
			}
		} else {
			$ERROR++;
			$ERRORSTR[]	= "We were unable to remove organisation ".$id." from the system at this time.<br /><br />The system administrator has been notified of this issue, please try again later.";
			application_log("error", "Failed to remove organisation from the database. Database said: ".$db->ErrorMsg());
		}
	}
}else{
	$ERROR++;
	$ERRORSTR[] = "No organisations were selected to be deleted. You will be redirected back to the organisations index <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/configuration/organisations\" style=\"font-weight: bold\">click here</a> to continue. ";
	$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/configuration/organisations/\\'', 5000)";	
}



if ($ERROR) {
	echo display_error();
}
if ($NOTICE) {
	echo display_notices();
}
if ($SUCCESS) {
	echo display_success();
}



?>
