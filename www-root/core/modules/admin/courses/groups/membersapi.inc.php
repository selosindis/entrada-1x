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
 * @author Organisation: University of Calgary
 * @author Unit: School of Medicine
 * @author Developer: Howard Lu <yhlu@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
 * @version $Id: add.inc.php 317 2009-01-19 19:26:35Z simpson $
 *
 */

if((!defined("PARENT_INCLUDED")) || (!defined("IN_COURSE_GROUPS"))) {
/**
 * @exception 0: Unable to start processing request.
 */
	echo "Authentication error!";
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
/**
 * @exception 0: Unable to start processing request.
 */
	echo "Authentication error!";
	exit;
}

ob_clear_open_buffers();
$GROUP_ID		= 0;

if((isset($_GET["group"])) && ((int) trim($_GET["group"]))) {
	$GROUP_ID	= (int) trim($_GET["group"]);
} elseif((isset($_POST["group_id"])) && ((int) trim($_POST["group_id"]))) {
	$GROUP_ID	= (int) trim($_POST["group_id"]);
}

if((isset($_GET["course"])) && ((int) trim($_GET["course"]))) {
	$COURSE_ID	= (int) trim($_GET["course"]);
} elseif((isset($_POST["course_id"])) && ((int) trim($_POST["course_id"]))) {
	$COURSE_ID	= (int) trim($_POST["course_id"]);
}

if((isset($_GET["action"])) && ($tmp_action_type = clean_input(trim($_GET["action"]), "alphanumeric"))) {
	$ACTION	= strcmp($tmp_action_type,'all') ? 0 : 1;
} elseif((isset($_POST["action"])) && ($tmp_action_type = clean_input(trim($_POST["action"]), "alphanumeric"))) {
	$ACTION	= strcmp($tmp_action_type,'all') ? 0 : 1;
} else {
	$ACTION = 0;
}

unset($tmp_action_type);

if(isset($GROUP_ID)) {
	ob_clear_open_buffers();

	//Figure out the organisation, group, and role requested
	if(isset($_POST["ogr"])) {
		$pieces = explode('|', $_POST["ogr"]);
		if(isset($pieces[0])) {
			$ORGANISATION_ID = clean_input($pieces[0], array("trim", "int"));
		}
		if(isset($pieces[1])) {
			$GROUP = clean_input($pieces[1], array("trim", "alphanumeric"));
		}
		if(isset($pieces[2])) {
			$ROLE = clean_input($pieces[2], array("trim", "alphanumeric"));
		}
	}

	if((isset($_POST["added_ids"])) && (is_array($_POST["added_ids"])) && count($_POST["added_ids"])) {
		$previously_added_ids = array();
		foreach ($_POST["added_ids"] as $id) {
			$previously_added_ids[] = (int) trim($id);
		}
	}
	
	if(isset($ORGANISATION_ID) && isset($GROUP) && isset($ROLE)) {
		$query			= "SELECT * FROM `course_groups` WHERE `cgroup_id` = ".$db->qstr($GROUP_ID)." AND `active` = '1'";
		$group_details	= $db->GetRow($query);
		if($group_details) {
			if($ENTRADA_ACL->amIAllowed('course', 'update')) {
				//Groups  exists and is editable by the current users
				$nmembers_results		= false;				

				//Fetch list of current members
				$current_member_list	= array();
				$query		= "SELECT `proxy_id` FROM `course_group_audience` WHERE `cgroup_id` = ".$db->qstr($GROUP_ID)." AND `active` = '1'";
				$results	= $db->GetAll($query);
				if($results) {
					foreach($results as $result) {
						if($proxy_id = (int) $result["proxy_id"]) {
							$current_member_list[] = $proxy_id;
						}
					}
				}
				

				//$nmembers_results = $db->GetAll($nmembers_query);
				$role = ($ROLE != 'all' ?$ROLE:false);
				$nmembers_results = course_fetch_course_audience($COURSE_ID,$ORGANISATION_ID,$GROUP,$role);
				if($nmembers_results) {
					$members = array(array('text' => "$GROUP > $ROLE", 'value'=>$GROUP.$ROLE, 'options'=>array(), 'disabled'=>false, 'category'=>'true'));
					foreach($nmembers_results as $member) {
						if(in_array($member['proxy_id'], $current_member_list)) {
							$registered = true;
						} else {
							$registered = false;				
						}
						$members[0]['options'][] = array('text' => $member['fullname'].($registered ? ' (already a member)' : ''), 'value' => $member['proxy_id'], 'disabled' => $registered, "checked" => (isset($previously_added_ids) && in_array(((int)$member["proxy_id"]), $previously_added_ids) ? "checked=\"checked\"" : ""));
//							$members[0]['options'][] = array('text' => $member['fullname'], 'value' => $member['proxy_id'], 'disabled' => false, 'checked' => ($registered ? "checked=\"checked\"" : ""));	
					}
					foreach($members[0]['options'] as $key => $member) {
						if(isset($member['options']) && is_array($member['options']) && !empty($member['options'])) {
							//Alphabetize members
							sort($members[0]['options'][$key]['options']);
						}
					}
					echo '<table cellspacing="0" cellpadding="0" class="select_multiple_table" width="100%">';
					echo lp_multiple_select_table($members, 0, 0, true);
					echo '</table>';
					
				} else {
					add_notice("No course members could be found matching your request.");
					echo display_notice();
				}				
			} else {
				add_error("You are not authorized to access this content.");
				echo display_error();
			}
		} else {
			add_error("This is an inactive group.");
			echo display_error();
		}
	}
}
exit();