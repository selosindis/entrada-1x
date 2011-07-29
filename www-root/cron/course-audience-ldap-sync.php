<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 *
 * $Id: community-quota.php 1103 2010-04-05 15:20:37Z simpson $
*/

@set_time_limit(0);
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
require_once("dbconnection.inc.php");

$ldap = NewADOConnection("ldap");
$ldap->SetFetchMode(ADODB_FETCH_ASSOC);
$ldap->debug = false;
$query = "	SELECT `course_code`,`course_id` 
			FROM `courses` 
			WHERE `course_active` = 1";
$results = $db->GetAll($query);
if ($results) {
	foreach ($results as $course) {
		//create LDAP connection
		if ($ldap->Connect(LDAP_HOST, LDAP_SEARCH_DN,LDAP_SEARCH_DN_PASS, LDAP_GROUPS_BASE_DN)) {
			//get the course information, in particular the list of unique members
			if (($result = $ldap->GetRow("cn=".$course["course_code"]."*"))) {
				$ldap->Close();
				//make new connection with the base set to people to get user information
				if ($ldap->Connect(LDAP_HOST, LDAP_SEARCH_DN,LDAP_SEARCH_DN_PASS, LDAP_PEOPLE_BASE_DN)) {
					//for each user in the unique member list get their queensuCaPkey
					foreach ($result["uniqueMember"] as $key=>$member) {
						$member_path = explode(',',$member);
						$uniUid = trim(str_replace('QueensuCaUniUid=', '', $member_path[0]));

						//there should always be a result, if not the LDAP server has a student enrolled with no LDAP entry
						if (($result = $ldap->GetRow("QueensuCaUniUid=".$uniUid."*"))) {
							//echo $uniUid."'s student number is ".$result["queensuCaPKey"]."        ";
							
							$pKey = str_replace("S","",$result["queensuCaPKey"]);
							$query = "	SELECT `id` 
										FROM `".AUTH_DATABASE."`.`user_data` 
										WHERE `number` = ".$db->qstr($pKey);
							//if there is a record, the student is created inside Entrada, no result means there is no linked Entrada account
							if ($id = $db->GetOne($query)) {
								$query = "	SELECT * FROM `course_audience` 
											WHERE b.`audience_value` = ".$db->qstr($id)."
											AND b.`audience_type` = 'proxy_id' 
											AND b.`course_id` = ".$db->qstr($course["course_id"]);

								//if no result, insert into the course audience, otherwise do nothing
								if (!$result=$db->GetAll($query)) {
									//insert into audience
									echo $pKey." WAS SUCCESSFULLY REGISTERED INTO THE COURSE: ".$course["course_code"]."    ";
								}
							} else {
								echo 'Student found in course on LDAP server who is not registered in Entrada.';
							}
							
						} else {
							echo 'LDAP records out of date, inform LDAP admin.';
						}

					}
					$ldap->Close();
				} else {
					echo 'Could not connect to get student information';
				}

			} else {
				echo 'No results from LDAP server for Entrada course. Check that course code is valid. ';
			}
		} else {
			echo 'Could not connect to get course information.';
		}
	}
} else {
	echo 'No courses found in system.';
}
?>