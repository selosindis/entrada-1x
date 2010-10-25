<?php
require_once("ClerkshipRotation.class.php");
require_once("ClerkshipElective.class.php");
require_once("Models/utility/Collection.class.php");

class ClerkshipRotations extends Collection {
}

class ClerkshipCoreCompleted extends ClerkshipRotations {
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$completed_cutoff = strtotime(CLERKSHIP_COMPLETED_CUTOFF.", ".date("Y"));
		$query		= "	SELECT a.`event_title`, a.`event_start`, a.`event_finish`, a.`category_id`, c.`category_name`, d.`rotation_title`
										FROM `".CLERKSHIP_DATABASE."`.`events` AS a
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
										ON b.`event_id` = a.`event_id`
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`categories` as c
										ON a.`category_id` = c.`category_id`
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`global_lu_rotations` as d
										ON a.`rotation_id` = d.`rotation_id`
										WHERE a.`event_type` <> 'elective'
										AND b.`econtact_type` = 'student'
										AND b.`etype_id` = ".$db->qstr($user_id)."
										AND a.`event_finish` < ".$db->qstr($completed_cutoff)." 
										ORDER BY a.`event_start` ASC";
		$results	= $db->GetAll($query);
		$rotations = array();
		if($results) {
			foreach($results as $result) {
				
				$title_parts = array();
				$title_parts[] = str_replace(" and ", " & ", trim($result['rotation_title']));
				$title_parts[] = str_replace(" and ", " & ", trim($result['category_name']));
				
				$title_parts = array_unique($title_parts);
				$title_parts = array_filter($title_parts);
				
				$title = implode(" / " ,$title_parts);
				
				$rotation = new ClerkshipRotation($title, $result['event_start'], $result['event_finish'], true);
				$rotations[] = $rotation;
			}
		}
		return new self($rotations);		
	} 
}

class ClerkshipCorePending extends ClerkshipRotations {
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$completed_cutoff = strtotime(CLERKSHIP_COMPLETED_CUTOFF.", ".date("Y"));

		$query		= "	SELECT a.`event_title`, a.`event_start`, a.`event_finish`, a.`category_id`, c.`category_name`, d.`rotation_title`
										FROM `".CLERKSHIP_DATABASE."`.`events` AS a
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
										ON b.`event_id` = a.`event_id`
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`categories` as c
										ON a.`category_id` = c.`category_id`
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`global_lu_rotations` as d
										ON a.`rotation_id` = d.`rotation_id`
										WHERE a.`event_type` <> 'elective'
										AND b.`econtact_type` = 'student'
										AND b.`etype_id` = ".$db->qstr($user_id)."
										AND a.`event_finish` >= ".$db->qstr($completed_cutoff)." 
										ORDER BY a.`event_start` ASC";
		
		$results	= $db->GetAll($query);
		$rotations = array();
		if($results) {
			foreach($results as $result) {
				$title_parts = array();
				$title_parts[] = str_replace(" and ", " & ", trim($result['rotation_title']));
				$title_parts[] = str_replace(" and ", " & ", trim($result['category_name']));
				
				$title_parts = array_unique($title_parts);
				$title_parts = array_filter($title_parts);
				
				$title = implode(" / " ,$title_parts);
				
				$rotation = new ClerkshipRotation($title, $result['event_start'], $result['event_finish'], false);
				$rotations[] = $rotation;
			}
		}
		return new self($rotations);
	} 
}

class ClerkshipElectivesCompleted extends ClerkshipRotations {
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$completed_cutoff = strtotime(CLERKSHIP_COMPLETED_CUTOFF.", ".date("Y"));
		$query		= "	SELECT a.`event_title`, a.`event_start`, a.`event_finish`, c.`preceptor_first_name`, c.`preceptor_last_name`, c.`city`, c.`prov_state`, d.`category_name` AS `department_title`, c.`sub_discipline`, e.`discipline`, f.`school_title`
										FROM `".CLERKSHIP_DATABASE."`.`events` AS a
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
										ON b.`event_id` = a.`event_id`
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`electives` AS c
										ON c.`event_id` = a.`event_id`
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`categories` AS d
										ON d.`category_id` = c.`department_id`
										LEFT JOIN `".DATABASE_NAME."`.`global_lu_disciplines` AS e
										ON e.`discipline_id` = c.`discipline_id`
										LEFT JOIN `".DATABASE_NAME."`.`global_lu_schools` AS f
										ON f.`schools_id` = c.`schools_id`
										WHERE a.`event_type` = 'elective'
										AND b.`econtact_type` = 'student'
										AND a.`event_status` = 'published'
										AND b.`etype_id` = ".$db->qstr($user_id)."
										AND a.`event_finish` < ".$db->qstr($completed_cutoff)." 
										ORDER BY a.`event_start` ASC";
		$results	= $db->GetAll($query);
		$electives = array();
		$ugly	= array();
		$ugly[]	= "‰";
		$ugly[]	= "Û";
		$ugly[]	= "?";
		
		if($results) {
			foreach($results as $result) {
				$school_title = (($result["school_title"]) ? $result["school_title"].", " : "");
				$city =  (($result["city"]) ? $result["city"].", " : "");
				$prov_state = (($result["prov_state"]) ? $result["prov_state"] : "");
				$location = $school_title.$city.$prov_state;
				
				$supervisor = trim((($result["preceptor_first_name"]) ? $result["preceptor_first_name"]." " : "").(($result["preceptor_last_name"]) ? $result["preceptor_last_name"]." " : ""));
				
				if (preg_match("/\b[Dd][Rr]\./", $supervisor) == 0) {
					$supervisor = "Dr. ".$supervisor;
				}
				$title_parts = array();
				$title_parts[] = str_replace($ugly, "", $result["department_title"]);
				if (trim($result["discipline"]) != "") {
					$title_parts[] = str_replace($ugly, "", $result["discipline"]);
				}
				if (trim($result['sub_discipline']) != "") {
					$title_parts[] = str_replace($ugly, "", $result["sub_discipline"]);
				}
				
				$title = implode(" / ", $title_parts);
				
				
				$elective = new ClerkshipElective($title, $location, $supervisor, $result['event_start'], $result['event_finish'], true);
				$electives[] = $elective;
			}
		}
		return new self($electives);
	} 
}