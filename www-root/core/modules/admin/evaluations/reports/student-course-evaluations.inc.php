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
 * This file is used to add events to the entrada.events table.
 *
 * @author Organisation: University of Calgary
 * @author Unit: Undergraduate Medical Education
 * @author Developer: Doug Hall <hall@ucalgary.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluations", "update", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if ($STEP == 1) {
		$BREADCRUMB[]	= array("url" => "", "title" => "Students' Course Evaluations" );
		$query = "	SELECT e.`evaluation_id`, e.`evaluation_title`, e.`evaluation_description`, e.`evaluation_start`,
					e.`evaluation_finish`, e.`min_submittable`,  count(distinct(`course_id`)) `evals`
					FROM `evaluations` e 
					INNER JOIN `evaluation_evaluators` ev ON e.`evaluation_id` = ev.`evaluation_id`
					INNER JOIN `evaluation_targets` t ON e.`evaluation_id` = t.`evaluation_id`
					INNER JOIN `evaluations_lu_targets` elt ON t.`target_id` = elt.`target_id`
					LEFT JOIN `courses` c ON t.`target_value` = c.`course_id`
					INNER JOIN `".AUTH_DATABASE."`.`user_access` a ON ev.`evaluator_value` = a.`user_id`
					WHERE elt.`target_shortname` = 'course' and elt.`target_active` = 1 
					and (ev.`evaluator_type` = 'grad_year' or ev.`evaluator_type` = 'proxy_id' and a.`group`= 'student')
					GROUP BY `evaluation_id`";
		$results	= $db->GetAll($query);			
	?>

		<h1>Students' Course Evaluations</h1>

		<div class="no-printing">
			<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Events">
				<colgroup>
					<col style="width: 2%" />
					<col style="width: 19%" />
					<col style="width: 34%" />
					<col style="width: 14%" />
					<col style="width: 14%" />
					<col style="width: 7%" />
					<col style="width: 10%" />
				</colgroup>
				<thead>
					<tr>
						<td class="modified" />
						<td class="title">Evaluation Title</td>
						<td class="date">Description</td>
						<td class="date"><div class="noLink">Start Date</div></td>
						<td class="date"><div class="noLink">Finish Date</div></td>
						<td class="date"><div class="noLink">Courses</div></td>
						<td class="date"><div class="noLink">Complete</div></td>
					</tr>
				</thead>
				<tbody>
			<?php
				foreach ($results as $result) {
					
					$query = "	SELECT COUNT(DISTINCT(`evaluator`)) FROM
								(
									SELECT ev.`evaluator_value` `evaluator`
									FROM `evaluation_evaluators` ev
									WHERE ev.`evaluator_type` = 'proxy_id'
									AND ev.`evaluation_id` = ".$db->qstr($result["evaluation_id"])."
									UNION
									SELECT a.`user_id` `evaluator`
									FROM `".AUTH_DATABASE."`.`user_access` a , `evaluation_evaluators` ev
									WHERE ev.`evaluator_type` = 'grad_year' AND ev.`evaluator_value` = a.`role`
									AND ev.`evaluation_id` = ".$db->qstr($result["evaluation_id"])."
								) t";
					$evaluators	= $db->GetOne($query);	

					$query = "	SELECT COUNT(`eprogress_id`) FROM `evaluation_progress` 
								WHERE `evaluation_id` = ".$db->qstr($result["evaluation_id"])."
								AND `progress_value` = 'complete'";
					$complete	= $db->GetOne($query) / $result["min_submittable"];	

					$url = ENTRADA_URL."/admin/evaluations/reports?section=${SECTION}&amp;step=2&amp;id=".$result["evaluation_id"];
					echo "	<tr><td class=\"modified\" />";
					echo "	<td class=\"title".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Evaluation Title: ".html_encode($result["evaluation_title"])."\">" : "").html_encode(limit_chars($result["evaluation_title"],27)).(($url) ? "</a>" : "")."</td>\n";
					echo "	<td class=\"teacher".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Course: ".html_encode($result["evaluation_description"])."\">" : "").html_encode(limit_chars($result["evaluation_description"],48)).(($url) ? "</a>" : "")."</td>\n";
					echo "	<td class=\"date".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Start Date\">" : "").date("M j, Y", $result["evaluation_start"]).(($url) ? "</a>" : "")."</td>\n";
					echo "	<td class=\"date".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Finish Date\">" : "").date("M j, Y", $result["evaluation_finish"]).(($url) ? "</a>" : "")."</td>\n";
					echo "	<td class=\"date".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Courses\">" : ""). ($result["evals"]?$result["evals"]:"").(($url) ? "</a>" : "")."</td>\n";
					echo "	<td class=\"date".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Completion\">" : ""). ($evaluators?round($complete/($evaluators*$result["evals"])*100)."% of $evaluators":"").(($url) ? "</a>" : "")."</td></tr>\n";
				}
				?>
			</tbody>
			</table>
		</div>
	<?php
	}
	if ($STEP == 2) {
		$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/evaluations/reports?section=student-course-evaluations".replace_query(array("step" => 1)), "title" => "Students Course Evaluations" );
		if(isset($_GET["id"])) {
			$evaluation_id = clean_input($_GET["id"], array("trim", "int")) ;
		}
		
		$query = "	SELECT e.*, c.`course_id`, c.`organisation_id`, c.`course_name`, c.`course_code`, t.`etarget_id` FROM `evaluations` e 
					INNER JOIN `evaluation_targets` t ON e.`evaluation_id` = t.`evaluation_id`
					INNER JOIN `evaluations_lu_targets` elt ON t.`target_id` = elt.`target_id`
					LEFT JOIN `courses` c ON t.`target_value` = c.`course_id`
					WHERE e.`evaluation_id` = ".$db->qstr($evaluation_id)."
					AND elt.`target_shortname` = 'course' and elt.`target_active` = 1";
		$results	= $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);

		$single = count($results)==1;

		$query = "	SELECT COUNT(DISTINCT(`evaluator`)) FROM
					(
						SELECT ev.`evaluator_value` `evaluator`
						FROM `evaluation_evaluators` ev
						WHERE ev.`evaluator_type` = 'proxy_id'
						AND ev.`evaluation_id` = ".$db->qstr($evaluation_id)."
						UNION
						SELECT a.`user_id` `evaluator`
						FROM `".AUTH_DATABASE."`.`user_access` a , `evaluation_evaluators` ev
						WHERE ev.`evaluator_type` = 'grad_year' AND ev.`evaluator_value` = a.`role`
						AND ev.`evaluation_id` = ".$db->qstr($evaluation_id)."
					) t";
		$evaluators	= $db->GetOne($query);	

		echo "<h1>".$results[0]["evaluation_title"]."</h1>";

		echo "<div style=\"float: left; width: 520px\">\n";
		echo "	<a name=\"evaluation-details-section\"></a>\n";
		echo "	<h2 title=\"Evaluation Details Section\">Evaluation Details</h2>\n";
		echo "	<div id=\"evaluation-details-section\" class=\"section-holder\">\n";
		echo "		<table style=\"width: 100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" summary=\"Detailed Evaluation Information\">\n";
		echo "		<colgroup>\n";
		echo "			<col style=\"width: 25%\" />\n";
		echo "			<col style=\"width: 75%\" />\n";
		echo "		</colgroup>\n";
		echo "		<tbody>\n";
		echo "			<tr>\n";
		echo "				<td>Description:</td>\n";
		echo "				<td>".(($results[0]["evaluation_description"]) ? $results[0]["evaluation_description"] : " ")."</td>\n";
		echo "			</tr>\n";
		echo "			<tr><td colspan='2'>&nbsp;</td></tr>\n";
		echo "			<tr>\n";
		echo "				<td>Type:</td>\n";
		echo "				<td>Student's Course Evaluations</td>\n";
		echo "			</tr>\n";
		echo "			<tr><td colspan='2'>&nbsp;</td></tr>\n";
		echo "			<tr>\n";
		echo "				<td>Start:</td>\n";
		echo "				<td>".date(DEFAULT_DATE_FORMAT, $results[0]["evaluation_start"])."</td>\n";
		echo "			</tr>\n";
		echo "			<tr>\n";
		echo "				<td>Finish:</td>\n";
		echo "				<td>".date(DEFAULT_DATE_FORMAT, $results[0]["evaluation_finish"])."</td>\n";
		echo "			</tr>\n";
		echo "			<tr><td colspan='2'>&nbsp;</td></tr>\n";
		echo "			<tr>\n";
		echo "				<td>Release Date:</td>\n";
		if ($results[0]["release_date"]) {
			echo "			<td>".date(DEFAULT_DATE_FORMAT, $results[0]["release_date"])."</td>\n";
		} else {
			echo "			<td />\n";			
		}
		echo "			</tr>\n";
		echo "			<tr>\n";
		echo "				<td>Release Until:</td>\n";
		if ($results[0]["release_until"]) {
			echo "			<td>".date(DEFAULT_DATE_FORMAT, $results[0]["release_until"])."</td>\n";
		} else {
			echo "			<td />\n";			
		}
		echo "			</tr>\n";
		if	($results[0]["min_submittable"] <> 1 or $results[0]["max_submittable"] <> 1) {
			echo "		<tr><td colspan='2'>&nbsp;</td></tr>\n";
			echo "		<tr>\n";
			echo "			<td>Submittable:</td>\n";
			echo "			<td><table>
								<tr><td>".$results[0]["min_submittable"]."</td><td align='right'>minimum<td style=\"width: 25%\"  />
								<td>".$results[0]["max_submittable"]."</td><td align='right'>maximum</td></tr>
							</table></td>\n";
			echo "		</tr>\n";
		}
	?>	
					</table>
				</div>
			</div>
			<div style="float: left">
			<a name="course-evaluation-section"></a>
			<h2 title="Evaluated Courses Section">Courses Evaluated in this Evaluation</h2>
				<form name="frmReport" action="<?php echo ENTRADA_URL; ?>/admin/evaluations/reports?section=reports" method="post">
					<div id="course-evaluation-section" class="section-holder">
						<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Evaluated Courses">
							<colgroup>
								<col style="width: 3%" />
								<col style="width: 32&" />
								<col style="width: 17%" />
								<col style="width: 15%" />
								<col style="width: 15%" />
								<col style="width: 15%" />
								<col style="width: 3%" />
							</colgroup>
							<thead>
								<tr>
									<td class="modified" />
									<td class="general borderl">Name</td>
									<td class="general">Code</td>
									<td class="date"><div class="noLink">In Progress</div></td>
									<td class="date"><div class="noLink">Complete</div></td>
									<td class="title"><div class="noLink">Updated</div></td>
									<td class="attachment">&nbsp;</td>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<td></td>
									<td colspan='6'style="text-align: right; padding-top: 30px">
										<input type="submit" class="button" value="Create Report<?php echo $single?"":"(s)"; ?>" />
									</td>
								</tr>
							</tfoot>
							<tbody>
	<?php						
		foreach ($results as $result) {
			$administrator = $ENTRADA_ACL->amIAllowed(new CourseContentResource($result["course_id"], $result["organisation_id"]), 'update');
			$query = "	SELECT MAX(p.`updated_date`) FROM `evaluation_progress` p
						INNER JOIN `evaluation_targets` t ON p.`etarget_id` = t.`etarget_id`
						WHERE t.`evaluation_id` = ".$db->qstr($result["evaluation_id"])."
						AND t.`target_value` = ".$db->qstr($result["course_id"])." AND t.`target_active` = 1
						AND p.`progress_value` <> 'cancelled'";
			$updated	= $db->GetOne($query);
				
			$query = "	SELECT COUNT(p.`eprogress_id`) FROM `evaluation_progress` p
						INNER JOIN `evaluation_targets` t ON p.`etarget_id` = t.`etarget_id`
						WHERE t.`evaluation_id` = ".$db->qstr($result["evaluation_id"])."
						AND t.`target_value` = ".$db->qstr($result["course_id"])." AND t.`target_active` = 1
						AND p.`progress_value` = 'inprogress'";
			$progress	= $db->GetOne($query);
				
			$query = "	SELECT COUNT(p.`eprogress_id`) FROM `evaluation_progress` p
						INNER JOIN `evaluation_targets` t ON p.`etarget_id` = t.`etarget_id`
						WHERE t.`evaluation_id` = ".$db->qstr($result["evaluation_id"])."
						AND t.`target_value` = ".$db->qstr($result["course_id"])." AND t.`target_active` = 1
						AND p.`progress_value` = 'complete'";
			$completed	= $db->GetOne($query);

			$url = $administrator ? ENTRADA_URL."/admin/evaluations/reports?section=reports&amp;evaluation=s:$result[etarget_id]" :"";
			echo "			<tr><td class=\"modified\">".(($administrator) ? "<input type=\"checkbox\" name=\"checked[]\" value=\"s:$result[etarget_id]\"".($single?"checked=\"checked\"":"")." />" : "<img src=\"".ENTRADA_URL."/images/pixel.gif\" width=\"19\" height=\"19\" alt=\"\" title=\"\" />")."</td>\n";
			echo "				<td class=\"general".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Course name \">" : "").html_encode($result["course_name"]).(($url) ? "</a>" : "")."</td>\n";
			echo "				<td class=\"teacher".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Code code \">" : "").html_encode($result["course_code"]).(($url) ? "</a>" : "")."</td>\n";
			echo "				<td class=\"date".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Progress\">" : ""). ($evaluators?round($progress/$evaluators*100)."% of $evaluators":"").(($url) ? "</a>" : "")."</td>\n";
			echo "				<td class=\"report-hours".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Completion\">" : ""). ($evaluators?round($completed/$evaluators*100)."% of $evaluators":"").(($url) ? "</a>" : "")."</td>\n";
			echo "				<td class=\"date".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Updated\">" : "").($updated?date("M j, Y", $updated):"").(($url) ? "</a>" : "")."</td>\n";
			echo "				<td class=\"attachment\" /></tr>\n";
		}
		echo "			</tbody>\n";
		echo "		</table>\n";
		echo "	</div>\n";
		echo "  </form>";
		echo "</div>\n";
	}
}
