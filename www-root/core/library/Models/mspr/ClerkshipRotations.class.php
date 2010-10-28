<?php
require_once("ClerkshipRotation.class.php");
require_once("ClerkshipElective.class.php");
require_once("Models/utility/Collection.class.php");

class ClerkshipRotations extends Collection {
	/**
	 * 
	 * @param mixed $rotations can be an array or a ClerkshipRotations Collection 
	 * @param string $distance e.g. "+1 week" format sensitive. 
	 */
	public static function merge_clerkship_rotations($rotations, $distance) {
		if ($rotations instanceof Collection) {
			$original = $rotations;
			$rotations = $rotations->container;
			$revert = true;
			
		}
		if ($rotations && count($rotations) > 1) {
			for ($i = 0; $i < count($rotations) - 1; $i++) { //not caching the length as it may change, doing -1 in the condition as we're comparing consecutive elements
				$element = $rotations[$i];
				$next_element = $rotations[$i+1];
				if ($element->getTitle() == $next_element->getTitle()) { //only continue if the titles are the same
					$cur_end = $element->getFinish();
					$next_start = $element->getStart();
					$cur_end_mod = strtotime(date(DATE_RFC2822, $cur_end) . " " . $distance);
					if ($cur_end_mod >= $next_start) { //overlapping or meeting
						//merge
						$title = $element->getTitle();
						$start = $element->getStart();
						$finish = $next_element->getFinish();
						$user = $element->getUser();
						$user_id = $user->getID(); 
						$new_element = new ClerkshipRotation($user_id, $title, $start, $finish, true);
						array_splice($rotations, $i, 2, array($new_element));
						$i--;
					}
				} 
			}
		}
		
		if ($revert) {
			$original->container = $rotations;
		}
		
		return $rotations;
	}
}

class ClerkshipCoreCompleted extends ClerkshipRotations {
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$completed_cutoff = strtotime(CLERKSHIP_COMPLETED_CUTOFF.", ".date("Y"));
		$query		= "	SELECT a.`event_title`, a.`event_start`, a.`event_finish`, a.`category_id`, c.`category_name` as cat1, d.`category_name` as cat2, e.`category_name` as cat3
										FROM `".CLERKSHIP_DATABASE."`.`events` AS a
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
										ON b.`event_id` = a.`event_id`
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`categories` as c
										ON a.`category_id` = c.`category_id`
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`categories` AS d
										ON c.`category_parent` = d.`category_id`
										AND d.`category_parent` != 5986
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`categories` AS e
										ON d.`category_parent` = e.`category_id`
										AND e.`category_parent` != 5986
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
				$title_parts[] = str_replace(" and ", " & ", trim($result['cat3']));
				$title_parts[] = str_replace(" and ", " & ", trim($result['cat2']));
				$title_parts[] = str_replace(" and ", " & ", trim($result['cat1']));
				
				$title_parts = array_unique($title_parts);
				$title_parts = array_filter($title_parts);
				
				$title = implode(" / " ,$title_parts);
				
				$rotation = new ClerkshipRotation($user_id, $title, $result['event_start'], $result['event_finish'], true);
				$rotations[] = $rotation;
			}
		}
		if (MSPR_CLERKSHIP_MERGE_NEAR) {
			$rotations = ClerkshipRotations::merge_clerkship_rotations($rotations, MSPR_CLERKSHIP_MERGE_DISTANCE);
		}
		return new self($rotations);		
	} 
}

class ClerkshipCorePending extends ClerkshipRotations {
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$completed_cutoff = strtotime(CLERKSHIP_COMPLETED_CUTOFF.", ".date("Y"));

		$query		= "	SELECT a.`event_title`, a.`event_start`, a.`event_finish`, a.`category_id`, c.`category_name` as cat1, d.`category_name` as cat2, e.`category_name` as cat3
										FROM `".CLERKSHIP_DATABASE."`.`events` AS a
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
										ON b.`event_id` = a.`event_id`
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`categories` as c
										ON a.`category_id` = c.`category_id`
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`categories` AS d
										ON c.`category_parent` = d.`category_id`
										AND d.`category_parent` != 5986
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`categories` AS e
										ON d.`category_parent` = e.`category_id`
										AND e.`category_parent` != 5986
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
				$title_parts[] = str_replace(" and ", " & ", trim($result['cat3']));
				$title_parts[] = str_replace(" and ", " & ", trim($result['cat2']));
				$title_parts[] = str_replace(" and ", " & ", trim($result['cat1']));
				
				$title_parts = array_unique($title_parts);
				$title_parts = array_filter($title_parts);
				
				$title = implode(" / " ,$title_parts);
				
				$rotation = new ClerkshipRotation($user_id, $title, $result['event_start'], $result['event_finish'], false);
				$rotations[] = $rotation;
			}
		}
		if (MSPR_CLERKSHIP_MERGE_NEAR) {
			$rotations = ClerkshipRotations::merge_clerkship_rotations($rotations, MSPR_CLERKSHIP_MERGE_DISTANCE);
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
				
				
				$elective = new ClerkshipElective($user_id, $title, $location, $supervisor, $result['event_start'], $result['event_finish'], true);
				$electives[] = $elective;
			}
		}
		return new self($electives);
	} 
}