<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to delete comments on a file within a folder. This action may be used by
 * either the original comment poster or by any community administrator.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if (!defined("IN_PUBLIC_ASSIGNMENTS")) {
	exit;
}
$ASSIGNMENT_ID = false;
if ($RECORD_ID) {
	$query			= "	SELECT * FROM `assignment_comments` WHERE `acomment_id` = ".$db->qstr($RECORD_ID)." AND `proxy_id` = ".$db->qstr($ENTRADA_USER->getProxyId());
			
	$comment_record	= $db->GetRow($query);
	if ($comment_record) {
		$ASSIGNMENT_ID = $comment_record["assignment_id"];
		if ((int) $comment_record["comment_active"]) {
			if ($comment_record["proxy_id"] === $ENTRADA_USER->getProxyId()) {
				if ($db->AutoExecute("assignment_comments", array("comment_active" => 0, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getProxyId()), "UPDATE", "`acomment_id` = ".$db->qstr($RECORD_ID))) {
					delete_notifications("assignments:file_comment:$RECORD_ID");
					add_statistic("assignment:".$comment_record["assignment_id"], "comment_delete", "acomment_id", $RECORD_ID);
					
				} else {
					application_log("error", "Failed to deactivate [".$RECORD_ID."] file comment from assignment. Database said: ".$db->ErrorMsg());
				}
			}
		} else {
			application_log("error", "The provided file comment id [".$RECORD_ID."] is already deactivated.");
		}		
		$query = "SELECT * FROM `assignment_files` WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getProxyId())." AND `afile_id` = ".$db->qstr($comment_record["afile_id"]);
		if ($db->GetRow($query)) {
			header("Location: ".ENTRADA_URL."/profile/gradebook/assignments?section=view&id=".$ASSIGNMENT_ID);
		} else {
			$query = "SELECT a.* FROM `assignment_files` AS a JOIN `assignment_contacts` AS b ON a.`assignment_id` = b.`assignment_id` WHERE b.`proxy_id` = ".$db->qstr($ENTRADA_USER->getProxyId())." AND a.`afile_id` = ".$db->qstr($comment_record["afile_id"]);
			if ($file_record = $db->GetRow($query)) {
				header("Location: ".ENTRADA_URL."/profile/gradebook/assignments?section=view&id=".$ASSIGNMENT_ID."&pid=".$file_record["proxy_id"]);
			}
		}
		exit;
	} else {
		application_log("error", "The provided file comment id [".$RECORD_ID."] was invalid.");
	}
} else {
	application_log("error", "No file comment id was provided for deactivation.");
}
if($ASSIGNMENT_ID){
	header("Location: ".ENTRADA_URL."/profile/gradebook/assignments?section=view&amp;id=".$ASSIGNMENT_ID);
}else{
	header("Location: ".ENTRADA_URL."/profile/gradebook/assignments");	
}
exit;
?>