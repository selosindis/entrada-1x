
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
 * @version $Id: edit.inc.php 1169 2010-05-01 14:18:49Z simpson $
 */

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_GRADEBOOK"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("gradebook", "read", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	ob_clear_open_buffers();
	if ($COURSE_ID) {
		$query			= "	SELECT * FROM `courses` 
							WHERE `course_id` = ".$db->qstr($COURSE_ID)."
							AND `course_active` = '1'";
		$course_details	= $db->GetRow($query);
		
		if ($course_details && $ENTRADA_ACL->amIAllowed(new GradebookResource($course_details["course_id"], $course_details["organisation_id"]), "read")) {
						
			if (isset($_GET["year"]) && ($tmp_input = clean_input($_GET["year"], array("nows", "int")))) {
				$GRAD_YEAR = $tmp_input;
			} else {
				$GRAD_YEAR = (int)(date("Y"));
			}			
				
			?>
			<div id="toolbar" style="display: none;">
			<select id="filter_grad_year" name="filter_grad_year" style="width: 203px, float: right;">
				<?php
				for($year = (date("Y", time()) + 4); $year >= (date("Y", time()) - 1); $year--) {
					echo "<option value=\"".(int) $year."\"".(($GRAD_YEAR == $year) ? " selected=\"selected\"" : "").">Class of ".html_encode($year)."</option>\n";
				}
				?>
			</select>
			</div>
			<?php
			$query = "	SELECT `assessments`.*,`assessment_marking_schemes`.`handler`
						FROM `assessments`
						LEFT JOIN `assessment_marking_schemes` ON `assessment_marking_schemes`.`id` = `assessments`.`marking_scheme_id`
						WHERE `assessments`.`course_id` = ".$db->qstr($COURSE_ID)."
						AND `assessments`.`grad_year` = ".$db->qstr($GRAD_YEAR);
			$assessments = $db->GetAll($query);
			if($assessments) {
				$query	= 	"SELECT b.`id` AS `proxy_id`, CONCAT_WS(', ', b.`lastname`, b.`firstname`) AS `fullname`, b.`number`, c.`role`";
				foreach($assessments as $key => $assessment) {
					$query 	.= ", g$key.`grade_id` AS `grade_".$key."_id`, g$key.`value` AS `grade_".$key."_value`";
				}
				$query 	.=" FROM `".AUTH_DATABASE."`.`user_data` AS b
							LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS c
							ON c.`user_id` = b.`id` AND c.`app_id`=".$db->qstr(AUTH_APP_ID)."
							AND c.`account_active`='true'
							AND (c.`access_starts`='0' OR c.`access_starts`<=".$db->qstr(time()).")
							AND (c.`access_expires`='0' OR c.`access_expires`>=".$db->qstr(time()).") ";
				foreach($assessments as $key => $assessment) {
					$query .= "LEFT JOIN `".DATABASE_NAME."`.`assessment_grades` AS g$key ON b.`id` = g$key.`proxy_id` AND g$key.`assessment_id` = ".$db->qstr($assessment["assessment_id"])."\n";
				}
				
				$query .= 	" WHERE c.`group` = 'student' AND c.`role` = ".$db->qstr($GRAD_YEAR);
				$query .=	" GROUP BY b.`id`";
				
				$students = $db->GetAll($query); 
				$editable = $ENTRADA_ACL->amIAllowed(new GradebookResource($course_details["course_id"], $course_details["organisation_id"]), "update") ? "gradebook_editable" : "gradebook_not_editable";
				?>
				<table class="gradebook <?php echo $editable; ?>">
					<thead>
						<tr>
							<th style="width: 200px;">Student</th>
							<th>Number</th>
							<th>Graduating Class</th>
							<?php foreach($assessments as $assessment){
								echo "<th>{$assessment["name"]}</th>\n";
							} ?>
						</tr>
					</thead>
					<tbody>
					<?php foreach($students as $key => $student): ?>
						<tr id="grades<?php echo $student["proxy_id"]; ?>">
							<td><?php echo $student["fullname"]; ?></td>
							<td><?php echo $student["number"]; ?></td>
							<td><?php echo $student["role"]; ?></td>
							<?php foreach($assessments as $key2 => $assessment): 
							if(isset($student["grade_".$key2."_id"])) {
								$grade_id = $student["grade_".$key2."_id"];
							} else {
								$grade_id = "";
							}
							if(isset($student["grade_".$key2."_value"])) {
								$grade_value = format_retrieved_grade($student["grade_".$key2."_value"], $assessment);
							} else {
								$grade_value = "-";
							} ?>
								<td>
									<span class="grade" 
										data-grade-id="<?php echo $grade_id; ?>"
										data-assessment-id="<?php echo $assessment["assessment_id"]; ?>"
										data-proxy-id="<?php echo $student["proxy_id"] ?>"
									><?php echo $grade_value; ?></span>
									<span class="gradesuffix" <?php echo (($grade_value === "-") ? "style=\"display: none;\"" : "") ?>>
										<?php echo assessment_suffix($assessment); ?>
									</span>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php if(count($students) === 0):	?>
				<div class="display-notice">There are no students in the system for this Graduating Year <strong><?php echo $GRAD_YEAR; ?></strong>.</div>
				<?php endif; ?>
			<?php
			} else {
				echo "<table class=\"gradebook\"></table>";
				$NOTICE++;
				$NOTICESTR[] = "No assessments could be found for this gradebook for the graduating class of $GRAD_YEAR.";

				echo display_notice();
			
			}

		} else {
			echo "<table class=\"gradebook\"></table>";
			
			$ERROR++;
			$ERRORSTR[] = "In order to edit a course you must provide a valid course identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide a valid course identifer when attempting to view a gradebook");
		}
	} else {
		echo "<table class=\"gradebook\"></table>";
		
		$ERROR++;
		$ERRORSTR[] = "In order to edit a course you must provide the courses identifier.";

		echo display_error();

		application_log("notice", "Failed to provide course identifer when attempting to view a gradebook");
	}
}
exit;
?>