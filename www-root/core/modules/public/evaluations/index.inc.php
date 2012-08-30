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
 * This is the default section that is loaded when the quizzes module is
 * accessed without a defined section.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_PUBLIC_EVALUATIONS"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
}
?>
<h1>My Evaluations</h1>
<?php

ob_start();
clerkship_display_available_evaluations();
$clerkship_evaluations = trim(ob_get_clean());

echo $clerkship_evaluations;

$cohort = groups_get_cohort($ENTRADA_USER->getID());

$query = "SELECT a.`cgroup_id` FROM `course_group_audience` AS a
			JOIN `course_groups` AS b
			ON a.`cgroup_id` = b.`cgroup_id`
			WHERE a.`proxy_id` = ".$db->qstr($ENTRADA_USER->getID())."
			AND a.`active` = 1
			AND b.`active` = 1";
$course_groups = $db->GetAll($query);

$cgroup_ids_string = "";
if (isset($course_groups) && is_array($course_groups)) {
	foreach ($course_groups as $course_group) {
		if ($cgroup_ids_string) {
			$cgroup_ids_string .= ", ".$db->qstr($course_group["cgroup_id"]);
		} else {
			$cgroup_ids_string = $db->qstr($course_group["cgroup_id"]);
		}
	}
}

$query = "	SELECT * FROM `evaluations` AS a
			JOIN `evaluation_evaluators` AS b
			ON a.`evaluation_id` = b.`evaluation_id`
			JOIN `evaluation_forms` AS c
			ON a.`eform_id` = c.`eform_id`
			JOIN `evaluations_lu_targets` AS d
			ON c.`target_id` = d.`target_id`
			WHERE
			(
				(
					b.`evaluator_type` = 'proxy_id'
					AND b.`evaluator_value` = ".$db->qstr($ENTRADA_USER->getID())."
				)
				OR
				(
					b.`evaluator_type` = 'organisation_id'
					AND b.`evaluator_value` = ".$db->qstr($_SESSION["details"]["organisation_id"])."
				)".($_SESSION["details"]["group"] == "student" ? " OR (
					b.`evaluator_type` = 'cohort'
					AND b.`evaluator_value` = ".$db->qstr($cohort["group_id"])."
				)" : "").($cgroup_ids_string ? " OR (
					b.`evaluator_type` = 'cgroup_id'
					AND b.`evaluator_value` IN (".$cgroup_ids_string.")
				)" : "")."
			)
			AND a.`evaluation_start` < ".$db->qstr(time())."
			AND a.`evaluation_active` = 1
			GROUP BY a.`evaluation_id`
			ORDER BY a.`evaluation_finish` DESC";
$results = $db->GetAll($query);
if ($results) {
	require_once("Models/evaluation/Evaluation.class.php");
	$evaluation_id = 0;
	?>
	<table class="tableList" cellspacing="0" summary="List of Evaluations">
	<colgroup>
		<col class="modified" />
		<col class="general" />
		<col class="general" />
		<col class="date" />
		<col class="title" />
		<col class="general" />
	</colgroup>
	<thead>
		<tr>
			<td class="modified">&nbsp;</td>
			<td class="general">Evaluation Type</td>
			<td class="general">Evaluation Target</td>
			<td class="date">Close Date</td>
			<td class="title">Evaluation Title</td>
			<td class="general">Evaluations Submitted</td>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ($results as $result) {
		$evaluation_targets_list = Evaluation::getTargetsArray($result["evaluation_id"], $result["eevaluator_id"], $ENTRADA_USER->getID());
		if ($evaluation_targets_list) {
			$evaluation_targets_count = count($evaluation_targets_list);
			if (array_search($result["target_shortname"], array("preceptor", "rotation_core", "rotation_elective")) !== false && $result["max_submittable"]) {
				$result["max_submittable"] = ($evaluation_targets_count * (int) $result["max_submittable"]);
			}
			$evaluation_target_title = fetch_evaluation_target_title($evaluation_targets_list[0], $evaluation_targets_count, $result["target_shortname"]);
			if ($result["target_shortname"] == "peer" && $result["max_submittable"] == 0) {
				$result["max_submittable"] = $evaluation_targets_count;
			}
		}

		$query = "	SELECT COUNT(`efquestion_id`) FROM `evaluation_form_questions`
					WHERE `eform_id` = ".$db->qstr($result["eform_id"])."
					GROUP BY `eform_id`";
		$evaluation_questions = $db->GetOne($query);
		
		$query = "	SELECT * FROM `evaluation_progress`
					WHERE `evaluation_id` = ".$db->qstr($result["evaluation_id"])."
					AND `proxy_id` = ".$db->qstr($ENTRADA_USER->getID())."
					AND `progress_value` = 'complete'";
		$evaluation_progress = $db->GetRow($query);
		
		$query = "	SELECT COUNT(`eprogress_id`) FROM `evaluation_progress`
					WHERE `evaluation_id` = ".$db->qstr($result["evaluation_id"])."
					AND `proxy_id` = ".$db->qstr($ENTRADA_USER->getID())."
					AND `progress_value` = 'complete'";
		$completed_attempts = $db->GetOne($query);
		
		if (($result["release_date"] <= time() || !$result["release_date"])) {
			$click_url = ENTRADA_URL."/evaluations?section=attempt&id=".$result["evaluation_id"];
		} else {
			$click_url = "";
		}
		
		if ($click_url) {
			echo "<tr>\n";
			echo "	<td>&nbsp;</td>\n";
			echo "	<td><a href=\"".$click_url."\">".(!empty($result["target_title"]) ? $result["target_title"] : "No Type Found")."</a></td>\n";
			echo "	<td><a href=\"".$click_url."\">".(!empty($evaluation_target_title) ? $evaluation_target_title : "No Target")."</a></td>\n";
			echo "	<td><a href=\"".$click_url."\">".date(DEFAULT_DATE_FORMAT, $result["evaluation_finish"])."</a></td>\n";
			echo "	<td><a href=\"".$click_url."\">".html_encode($result["evaluation_title"])."</a></td>\n";
			echo "	<td><a href=\"".$click_url."\">".($completed_attempts ? ((int)$completed_attempts) : "0")."/".($result["max_submittable"] ? ((int)$result["max_submittable"]) : "0")."</a></td>\n";
			echo "</tr>\n";
		} else {
			echo "<tr>\n";
			echo "	<td class=\"content-small\">&nbsp;</td>\n";
			echo "	<td class=\"content-small\">".(!empty($evaluation_target["target_title"]) ? $evaluation_target["target_title"] : "No Type Found")."</td>\n";
			echo "	<td class=\"content-small\">".(!empty($evaluation_target_title) ? $evaluation_target_title : "No Target")."</td>\n";
			echo "	<td class=\"content-small\">".date(DEFAULT_DATE_FORMAT, $result["evaluation_finish"])."</td>\n";
			echo "	<td class=\"content-small\">".html_encode($result["evaluation_title"])."</td>\n";
			echo "	<td class=\"content-small\">".($completed_attempts ? ((int)$completed_attempts) : "0")."/".($result["max_submittable"] ? ((int)$result["max_submittable"]) : "0")."</td>\n";
			echo "</tr>\n";
		}

	}
	?>
	</tbody>
	</table>
	<?php
} else {
	if (!$clerkship_evaluations) {
		?>
		<div class="display-generic">
			There are no evaluations or assessments <strong>assigned to you</strong> in the system at this time.
		</div>
		<?php
	}
}