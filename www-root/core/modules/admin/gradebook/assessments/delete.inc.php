
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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_GRADEBOOK"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("assessment", "delete", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if ($COURSE_ID) {
		$query			= "	SELECT * FROM `courses` 
							WHERE `course_id` = ".$db->qstr($COURSE_ID)."
							AND `course_active` = '1'";
		$course_details	= $db->GetRow($query);
		if ($course_details && $ENTRADA_ACL->amIAllowed(new GradebookResource($course_details["course_id"], $course_details["organisation_id"]), "update")) {		
			
			echo "<div class=\"no-printing\">\n";
			echo "	<div style=\"float: right\">\n";
			if($ENTRADA_ACL->amIAllowed(new CourseResource($course_details["course_id"], $course_details["organisation_id"]), "read")) {
				echo "		<a href=\"".ENTRADA_URL."/admin/courses?".replace_query(array("section" => "edit", "id" => $course_details["course_id"], "step" => false))."\"><img src=\"".ENTRADA_URL."/images/event-details.gif\" width=\"16\" height=\"16\" alt=\"Edit course details\" title=\"Edit course details\" border=\"0\" style=\"vertical-align: middle; margin-bottom: 2px;\" /></a> <a href=\"".ENTRADA_URL."/admin/courses?".replace_query(array("section" => "edit", "id" => $course_details["course_id"], "step" => false))."\" style=\"font-size: 10px; margin-right: 8px\">Edit course details</a><br/>\n";
			}
			if($ENTRADA_ACL->amIAllowed(new CourseContentResource($course_details["course_id"], $course_details["organisation_id"]), "read")) {
				echo "		<a href=\"".ENTRADA_URL."/admin/courses?".replace_query(array("section" => "content", "id" => $COURSE_ID, "step" => false))."\"><img src=\"".ENTRADA_URL."/images/event-contents.gif\" width=\"16\" height=\"16\" alt=\"Manage course content\" title=\"Manage course content\" border=\"0\" style=\"vertical-align: middle; margin-bottom: 2px;\" /></a> <a href=\"".ENTRADA_URL."/admin/courses?".replace_query(array("section" => "content", "id" => $COURSE_ID, "step" => false))."\" style=\"font-size: 10px; margin-right: 8px;\">Manage course content</a><br/>\n";
			}
			echo "<a href=\"".ENTRADA_URL."/admin/gradebook?section=edit&amp;id=".$COURSE_ID."\" style=\"font-size: 10px;\"><img src=\"".ENTRADA_URL."/images/book_go.png\" width=\"16\" height=\"16\" alt=\"Manage course content\" title=\"Manage course content\" border=\"0\" style=\"vertical-align: middle\" />&nbsp;Manage course gradebook</a>";				
			echo "	</div>\n";
			echo "</div>\n";
			
			$curriculum_path = curriculum_hierarchy($COURSE_ID);
			if ((is_array($curriculum_path)) && (count($curriculum_path))) {
				echo "<h1>" . implode(": ", $curriculum_path) . " Gradebook </h1>";
			}
			echo "<br/>";
			$ASSESSMENT_IDS	= array();
			$INDEX_URL = ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("section" => "index"));
			// Error Checking
			switch($STEP) {
				case 2 :
				case 1 :
				default :
					if((!isset($_POST["delete"])) || (!is_array($_POST["delete"])) || (!@count($_POST["delete"]))) {
						$ERROR++;
						$ERRORSTR[] = "You must select at least 1 assessment to delete by checking the checkbox to the left the assessment.";

						application_log("notice", "Assessment delete page accessed without providing any assessment id's to delete.");
					} else {
						foreach($_POST["delete"] as $assessment_id) {
							$assessment_id = (int) trim($assessment_id);
							if($assessment_id) {
								$ASSESSMENT_IDS[] = $assessment_id;
							}
						}

						if(!@count($ASSESSMENT_IDS)) {
							$ERROR++;
							$ERRORSTR[] = "There were no valid assessment identifiers provided to delete. Please ensure that you access this section through the assessment index.";
						}
					}

					if($ERROR) {
						$STEP = 1;
					}
				break;
			}

			// Display Page
			switch($STEP) {
				case 2 :
					$query = "DELETE FROM `assessments` WHERE `assessment_id` IN (".implode(", ", $ASSESSMENT_IDS).")";
					if($db->Execute($query)) {
						$ONLOAD[]	= "setTimeout('window.location=\\'".$INDEX_URL."\\'', 5000)";

						if($total_removed = $db->Affected_Rows()) {
							$query = "DELETE FROM `assessment_grades` WHERE `assessment_id` IN (".implode(", ", $ASSESSMENT_IDS).")";
							if($db->Execute($query)) {
								application_log("success", "Successfully removed assessment ids: ".implode(", ", $ASSESSMENT_IDS));
							} else {
								application_log("error", "Successfully removed assessment ids: ".implode(", ", $ASSESSMENT_IDS), "but was unable to remove the grades pertaining to them.");
							}
							
							$SUCCESS++;
							$SUCCESSSTR[]  = "You have successfully removed ".$total_removed." assessment".(($total_removed != 1) ? "s" : "")." from the system.<br /><br />You will be automatically redirected to the event index in 5 seconds, or you can <strong><a href=\"".$INDEX_URL."\">click here</a></strong> to go there now.";

							echo display_success();
							
							
					} else {
							$ERROR++;
							$ERRORSTR[] = "We were unable to remove the requested assessments from the system. The MEdTech Unit has been informed of this issue and will address it shortly; please try again later.";

							echo display_error();

							application_log("error", "Failed to remove any assessment ids: ".implode(", ", $ASSESSMENT_IDS).". Database said: ".$db->ErrorMsg());
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "We were unable to remove the requested assessments from the system. The MEdTech Unit has been informed of this issue and will address it shortly; please try again later.";

						echo display_error();

						application_log("error", "Failed to execute remove query for assessment ids: ".implode(", ", $ASSESSMENT_IDS).". Database said: ".$db->ErrorMsg());
					}
				break;
				case 1 :
				default :
			
					// Fetch all associated assessments
					$query = "SELECT `assessment_id`,`grad_year`,`name`,`type`  FROM `assessments` WHERE `course_id` = ".$db->qstr($COURSE_ID)."
					AND `assessment_id` IN (".implode(", ", $ASSESSMENT_IDS).") ORDER BY `name` ASC";
					$assessments = 	$db->GetAll($query);
					if($assessments) {
						echo display_notice(array("Please review the following notices to ensure that you wish to permanently delete them. This action cannot be undone."));
						echo "<form action=\"".ENTRADA_URL . "/admin/gradebook/assessments?".replace_query(array("section" => "delete", "step"=>2))."\" method=\"post\">";
						
						?>
						
						<table class="tableList" cellspacing="0" summary="List of Assessments">
						<colgroup>
							<col class="modified" />
							<col class="title" />
							<col class="general" />
							<col class="general" />
							<col class="general" />
						</colgroup>
						<thead>
							<tr>
								<td class="modified">&nbsp;</td>
								<td class="title sortedASC">Name</td>
								<td class="general">Graduating Year</td>
								<td class="general">Assessment Type</td>
								<td class="general">Grades Entered</td>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td></td>
								<td colspan="4" style="padding-top: 10px">
									<input type="submit" class="button" value="Delete Selected" />
								</td>
							</tr>
						</tfoot>
						<tbody>
							<?php
							foreach($assessments as $key => $assessment) {
								$url = ENTRADA_URL."/admin/gradebook/assessments?section=edit&amp;id=".$COURSE_ID."&amp;assessment_id=".$assessment["assessment_id"];
						
								echo "<tr id=\"assessment-".$assessment["assessment_id"]."\">";
								echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"delete[]\" checked=\"checked\" value=\"".$assessment["assessment_id"]."\" /></td>\n";
								echo "	<td class=\"title\"><a href=\"$url\">".$assessment["name"]."</a></td>";
								echo "	<td class=\"general\"><a href=\"$url\">".$assessment["grad_year"]."</a></td>";
								echo "	<td class=\"general\"><a href=\"$url\">".$assessment["type"]."</a></td>";
								echo "	<td class=\"general\">"."&nbsp;"."</td>";
								echo "</tr>";
							}
							?>
						</tbody>
					</table>
					</form>
					<?php
					} else {
						// No assessments in this course.
						?>
						<div class="display-notice">
							<h3>No Assessments to delete for <?php echo $course_details["course_name"]; ?></h3>
							You must select some assessments to delete for this course
						</div>
						<?php
					}
				break;
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to edit a course you must provide a valid course identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide a valid course identifer when attempting to view a gradebook");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit a course you must provide the courses identifier.";

		echo display_error();

		application_log("notice", "Failed to provide course identifer when attempting to view a gradebook");
	}
}
?>
