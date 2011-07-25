<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Serves a particular calendar in either JSON or ICS depending on the extension of the $_GET["request"];
 * http://www.yourschool.ca/calendars/username.json
 * http://www.yourschool.ca/calendars/username.ics
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2009 Queen's University. All Rights Reserved.
 *
 */

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

$options_for = false;

if (isset($_GET["options_for"])) {
    $options_for = clean_input($_GET["options_for"], array("trim"));
}

$organisation_id = 0;

$organisation_id = $user->getActiveOrganisation();

if (($options_for) && ($organisation_id) && (isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {
    $query = "SELECT `organisation_id`,`organisation_title` FROM `".AUTH_DATABASE."`.`organisations` WHERE `organisation_id` = " . $organisation_id;
    $organisation_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query); //will always be one record since the filter should be org based.
    $organisation_ids_string = "";
    if ($organisation_results) {
        $organisations = array();
        foreach ($organisation_results as $result) {			
            if($ENTRADA_ACL->amIAllowed("resourceorganisation".$result["organisation_id"], "read")) {
                if (!$organisation_ids_string) {
                    $organisation_ids_string = $db->qstr($result["organisation_id"]);
                } else {
                    $organisation_ids_string .= ", ".$db->qstr($result["organisation_id"]);					
                }
                if (isset($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["organisation"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["organisation"]) && (in_array($result["organisation_id"], $_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["organisation"]))) {
                    $checked = 'checked="checked"';
                } else {
                    $checked = '';
                }
                $organisations[$result["organisation_id"]] = array('text' => $result["organisation_title"], 'value' => 'organisation_'.$result["organisation_id"], 'checked' => $checked);
                $organisation_categories[$result["organisation_id"]] = array('text' => $result["organisation_title"], 'value' => 'organisation_'.$result["organisation_id"], 'category'=>true);
            }
        }
    }
    if (!$organisation_ids_string) {
        $organisation_ids_string = $db->qstr($ORGANISATION_ID);
    }
	
    switch($options_for) {
    case "teacher":
        // Get the possible teacher filters
        $query = "	SELECT a.`id` AS `proxy_id`, a.`organisation_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`
            FROM `".AUTH_DATABASE."`.`user_data` AS a
            JOIN `".AUTH_DATABASE."`.`user_access` AS b
            ON b.`user_id` = a.`id`
            JOIN `".DATABASE_NAME."`.`event_contacts` AS c
            ON c.`proxy_id` = a.`id`
			JOIN `".DATABASE_NAME."`.`events` AS d
			ON d.`event_id` = c.`event_id`
			JOIN `".DATABASE_NAME."`.`courses` AS e
			ON e.`course_id` = d.`course_id`
            WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
            AND (b.`group` = 'faculty' OR (b.`group` = 'resident' AND b.`role` = 'lecturer'))
            AND a.`id` IN (SELECT `proxy_id` FROM `event_contacts`)
			AND e.`organisation_id` =  " . $organisation_ids_string . "
            GROUP BY a.`id`
            ORDER BY `fullname` ASC";
        $teacher_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);

        if ($teacher_results) {

			$teachers = $organisation_categories;
			
            foreach ($teacher_results as $r) {
                if (isset($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["teacher"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["teacher"]) && (in_array($r['proxy_id'], $_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]['teacher']))) {
                    $checked = 'checked="checked"';
                } else {
                    $checked = '';
                }

                $teachers[$r["organisation_id"]]['options'][] = array('text' => $r['fullname'], 'value' => 'teacher_'.$r['proxy_id'], 'checked' => $checked);
            }

            echo lp_multiple_select_popup('teacher', $teachers, array('title'=>'Select Teachers:', 'submit_text'=>'Apply', 'cancel'=>true, 'submit'=>true));
        }
        break;
    case "student":
        // Get the possible Student filters
        $query = "	SELECT a.`id` AS `proxy_id`, a.`organisation_id`, b.`role`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`
            FROM `".AUTH_DATABASE."`.`user_data` AS a
            LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
            ON a.`id` = b.`user_id`
            WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
            AND a.`organisation_id` IN (".$organisation_ids_string.")
            AND b.`account_active` = 'true'
            AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
            AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
            AND b.`group` = 'student'
            AND b.`role` >= ".$db->qstr((fetch_first_year() - 4)).
            (($_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"] == "student") ? " AND a.`id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]) : "")."
            GROUP BY a.`id`
            ORDER BY b.`role` DESC, a.`lastname` ASC, a.`firstname` ASC";
        $student_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
        if ($student_results) {
            $students = $organisation_categories;
            foreach ($student_results as $r) {
                if (isset($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["student"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["student"]) && (in_array($r['proxy_id'], $_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["student"]))) {
                    $checked = 'checked="checked"';
                } else {
                    $checked = '';
                }
                $students[$r["organisation_id"]]['options'][] = array('text' => $r['fullname'], 'value' => 'student_'.$r['proxy_id'], 'checked' => $checked);
            }

            echo lp_multiple_select_popup('student', $students, array('title'=>'Select Students:', 'submit_text'=>'Apply', 'cancel'=>true, 'submit'=>true));
        }
        break;
    case "course":
        // Get the possible courses filters
        $query = "	SELECT `course_id`, `course_name` 
            FROM `".DATABASE_NAME."`.`courses` 
            WHERE `organisation_id` IN (".$organisation_ids_string.")
            ORDER BY `course_name` ASC";
        $courses_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
        if ($courses_results) {
            $courses = array();
            foreach ($courses_results as $c) {
                if (isset($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["course"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["course"]) && (in_array($c['course_id'], $_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["course"]))) {
                    $checked = 'checked="checked"';
                } else {
                    $checked = '';
                }

                $courses[] = array('text' => $c['course_name'], 'value' => 'course_'.$c['course_id'], 'checked' => $checked);
            }

            echo lp_multiple_select_popup('course', $courses, array('title'=>'Select Courses:', 'submit_text'=>'Apply', 'cancel'=>true, 'submit'=>true));
        }
        break;
    case "smallgroup":
        // Get the possible small group filters
        $query = "	SELECT * FROM `".DATABASE_NAME."`.`student_groups` 
            WHERE `group_active` = 1
            ORDER BY `group_name` ASC";
        $groups_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
        if ($groups_results) {
            $groups = array();
            foreach ($groups_results as $sg) {
                if (isset($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["smallgroup"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["smallgroup"]) && (in_array($sg['sgroup_id'], $_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["smallgroup"]))) {
                    $checked = 'checked="checked"';
                } else {
                    $checked = '';
                }

                $groups[] = array('text' => $sg['group_name'], 'value' => 'smallgroup_'.$sg['sgroup_id'], 'checked' => $checked);
            }

            echo lp_multiple_select_popup('smallgroup', $groups, array('title'=>'Select Small Groups:', 'submit_text'=>'Apply', 'cancel'=>true, 'submit'=>true));
        }
        break;
    case "eventtype":
        // Get the possible event type filters
        $query = "	SELECT a.`eventtype_id`, a.`eventtype_title` FROM `events_lu_eventtypes` AS a 
					LEFT JOIN `eventtype_organisation` AS c 
					ON a.`eventtype_id` = c.`eventtype_id` 
					LEFT JOIN `".AUTH_DATABASE."`.`organisations` AS b
					ON b.`organisation_id` = c.`organisation_id` 
					WHERE b.`organisation_id` = ".$db->qstr($user->getActiveOrganisation())."
					AND a.`eventtype_active` = '1' 
					ORDER BY a.`eventtype_order`
					";
        $eventtype_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
        if ($eventtype_results) {
            $eventtypes = array();
            foreach ($eventtype_results as $result) {
                if (isset($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["eventtype"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["eventtype"]) && (in_array($result["eventtype_id"], $_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["eventtype"]))) {
                    $checked = 'checked="checked"';
                } else {
                    $checked = '';
                }
                $eventtypes[] = array('text' => $result["eventtype_title"], 'value' => 'eventtype_'.$result["eventtype_id"], 'checked' => $checked);
            }

            echo lp_multiple_select_popup('eventtype', $eventtypes, array('title'=>'Select Event Types:', 'submit_text'=>'Apply', 'cancel'=>true, 'submit'=>true));
        }

        break;
    case "grad":
        $syear		= (date("Y", time()) - 1);
        $eyear		= (date("Y", time()) + 4);
        $gradyears = array();
        for ($year = $syear; $year <= $eyear; $year++) {
            if (isset($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["grad"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["grad"]) && (in_array($year, $_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["grad"]))) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }
            $gradyears[] = array('text' => "Graduating in $year", 'value' => "grad_".$year, 'checked' => $checked);
        }

        echo lp_multiple_select_popup('grad', $gradyears, array('title'=>'Select Gradutating Years:', 'submit_text'=>'Apply', 'cancel'=>true, 'submit'=>true));

        break;
    case "phase":
        $phases = array(
            array('text'=>'Term 1', 'value'=>'phase_1', 'checked'=>''),
            array('text'=>'Term 2', 'value'=>'phase_2', 'checked'=>''),
            array('text'=>'Term 3', 'value'=>'phase_t3', 'checked'=>''),
            array('text'=>'Term 4', 'value'=>'phase_t4', 'checked'=>''),
            array('text'=>'Phase 2A', 'value'=>'phase_2a', 'checked'=>''),
            array('text'=>'Phase 2B', 'value'=>'phase_2b', 'checked'=>''),
            array('text'=>'Phase 2C', 'value'=>'phase_2c', 'checked'=>''),
            array('text'=>'Phase 2E', 'value'=>'phase_2e', 'checked'=>''),
            array('text'=>'Phase 3', 'value'=>'phase_3', 'checked'=>'')
        );

        for ($i = 0; $i < 6; $i++) {
            if (isset($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["phase"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["phase"])) {
                $pieces = explode('_', $phases[$i]['value']);
                if (in_array($pieces[1], $_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]['phase'])) {
                    $phases[$i]['checked'] = 'checked="checked"';
                }
            }
        }

        echo lp_multiple_select_popup('phase', $phases, array('title'=>'Select Phases / Terms:', 'submit_text'=>'Apply', 'cancel'=>true, 'submit'=>true));
        break;
    case "clinical_presentation":
        $clinical_presentations = fetch_mcc_objectives();
        foreach ($clinical_presentations as &$clinical_presentation) {
            $clinical_presentation["value"] = "objective_".$clinical_presentation["objective_id"];
            $clinical_presentation["text"] = $clinical_presentation["objective_name"];
            $clinical_presentation["checked"] = "";
            if (isset($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["clinical_presentations"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["clinical_presentations"])) {						
                if (in_array($clinical_presentation["value"], $_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"]["clinical_presentations"])) {
                    $clinical_presentation["checked"] = "checked=\"checked\"";
                }
            }
        }

        echo lp_multiple_select_popup('clinical_presentation', $clinical_presentations, array('title'=>'Select Clinical Presentations:', 'submit_text'=>'Apply', 'cancel'=>true, 'submit'=>true));
        break;
    }   
}
?>
