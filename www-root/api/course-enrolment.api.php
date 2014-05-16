<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * This file looks a bit different because it is called only by AJAX requests
 * and returns the members relevant to the requested group and role.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Josh Dillon <jdillon@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
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

if ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
    header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('course', 'update', false)) {
    $ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
    
    $request = strtoupper(clean_input($_SERVER['REQUEST_METHOD'], "alpha"));
	
    $request_var = "_".$request;
	
	$method = clean_input(${$request_var}["method"], array("trim", "striptags"));
    
    if (isset(${$request_var}["course_id"]) && $tmp_input = clean_input(${$request_var}["course_id"], "int")) {
		$course_id = $tmp_input;
	}
    
    if (isset(${$request_var}["cperiod_id"]) && $tmp_input = clean_input(${$request_var}["cperiod_id"], "int")) {
		$cperiod_id = $tmp_input;
	}
    
    if (isset(${$request_var}["search_term"]) && $tmp_input = clean_input(${$request_var}["search_term"], array("trim", "striptags"))) {
		$search_term = $tmp_input;
	}
    
    if (isset(${$request_var}["enrolment_view"]) && $tmp_input = clean_input(${$request_var}["enrolment_view"], array("trim", "striptags"))) {
		$enrolment_view = $tmp_input;
	}
    
    switch ($request) {
        case "POST" :
        break;
        case "GET" :
            switch ($method) {
                case "sync" :
                    $ldap = new Entrada_Sync_Course_Ldap($course_id, $cperiod_id);
                    $audience = new Models_Course_Audience();
                    $a = $audience->fetchRowByCourseIDCperiodID($course_id, $cperiod_id);
                    $ldap_sync_date = false;
                    $period = Models_CurriculumPeriod::fetchRowByID($cperiod_id);
                    if ($period->getStartDate() < strtotime("-2 weeks", time())) {
                        $next_ldap_sync_date = date("Y-m-d", strtotime("-2 weeks", $period->getStartDate()));
                    } else {
                        $next_ldap_sync_date = "Tonight at midnight";
                    }
                    
                    if ($a) {
                        $ldap_sync_date = $a->getLdapSyncDate();
                        echo json_encode(array("status" => "success", "data" => array("sync_date" => "Successfully synchronized enrolment <strong>" . date("Y-m-d H:i", $ldap_sync_date). "</strong>", "next" => $next_ldap_sync_date)));
                    }
                break;
                case "sync_date" :
                    $audience = new Models_Course_Audience();
                    $a = $audience->fetchRowByCourseIDCperiodID($course_id, $cperiod_id);
                    $ldap_sync_date = false;
                    $period = Models_CurriculumPeriod::fetchRowByID($cperiod_id);
                    if ($a) {
                        $ldap_sync_date = $a->getLdapSyncDate();
                        if ($ldap_sync_date) {
                            if ($period->getStartDate() > strtotime("-2 weeks", time())) {
                                $next_ldap_sync_date = date("Y-m-d", strtotime("-2 weeks", $period->getStartDate()));
                            } else {
                                $next_ldap_sync_date = "Tonight at midnight";
                            }
                            echo json_encode(array("status" => "success", "data" => array("ldap_sync_date" => "Successfully synchronized enrolment <strong>" . date("Y-m-d H:i", $ldap_sync_date). "</strong>", "next_sync_date" => "The next synchornization will occur on " . $next_ldap_sync_date)));
                        } else {
                            if ($period) {
                                echo json_encode(array("status" => "success", "data" => array("ldap_sync_date" => "Enrolment will be synchronized on <strong>" . date("Y-m-d", strtotime("-2 weeks", $period->getStartDate()). "</strong>."))));
                            }
                        }
                    } else {
                        echo json_encode(array("status" => "error", "data" => array("No audience found.")));
                    }
                break;
                case "list" :
                    $course = Models_Course::get($course_id);
                    if ($course) {
                        $course_audience = $course->getMembers($cperiod_id, $search_term);
                        if ($course_audience) {
                            $enrolment = array();
                            foreach ($course_audience as $audience_type => $audience_type_members) {
                                if ($audience_type == "groups") {
                                    foreach ($audience_type_members as $group_name => $audience) {
                                        foreach ($audience as $audience_member) {
                                            $enrolment["groups"][$group_name][] = $audience_member->toArray();
                                        }
                                    }
                                } else if ($audience_type == "individuals") {
                                    foreach ($audience_type_members as $audience_member) {
                                        $enrolment["individuals"][] = $audience_member->toArray();
                                    }
                                }
                            }
                            echo json_encode(array("status" => "success", "data" => $enrolment));
                            if (isset($enrolment_view)) {
                                $_SESSION[APPLICATION_IDENTIFIER]["courses"]["enrolment_view"] = $enrolment_view;
                            }
                        } else {
                            echo json_encode(array("status" => "error", "data" => array("No enrolments found.")));
                        }
                    } else {
                       echo json_encode(array("status" => "error", "data" => array("No course found with the provided ID."))); 
                    }
                break;
            }
        break;
        
    }
}
