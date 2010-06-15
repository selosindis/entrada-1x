<?php

require_once("Collection.class.php");
require_once("ResearchCitation.class.php");

class ResearchCitations extends Collection {
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$query		= "SELECT * FROM `student_research` WHERE `user_id` = ".$db->qstr($user_id)." ORDER BY `priority` ASC";
		$results = $db->getAll($query);
		$citations = array();
		if ($results) {
			foreach ($results as $result) {
				$citation =  new ResearchCitation($result['id'], $result['user_id'], $result['citation'], $result['priority'], $result['approved']);
				$citations[] = $citation;
			}
		}
		return new self($citations);
	}
	
	/**
	 * User is included to prevent tampering with another user's sequence.
	 * @param User $user
	 * @param array $ids
	 */
	public static function Resequence(User $user, $ids) {
		global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
		$user_id = $user->getID();
		$stmt = $db->Prepare('update `student_research` set `priority`=? where `user_id`=? and `id`=?');
		foreach($ids as $priority=>$id) {
			if (!$db->Execute($stmt,array($priority, $user_id, $id))) {
				$ERROR++;
				$ERRORSTR[] = "Failed to re-sequence Research Citations.";
				application_log("error", "Unable to modify a student_research record. Database said: ".$db->ErrorMsg());
				break;
			}
		}
	}
}