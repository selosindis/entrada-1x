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

/**
 * @todo This should be a list of all of the quizzes the person was *supposed* to do
 * so it should go through each semester and each course and show all events that have
 * quizzes attached and then show what grade they got on the attempts they were
 * supposed to list.
 */
$query		= "	SELECT * FROM `evaluations` AS a
				JOIN `evaluation_evaluators` AS b
				ON a.`evaluation_id` = b.`evaluation_id`
				WHERE (
					b.`evaluator_type` = 'proxy_id'
					AND b.`evaluator_value` = ".$db->qstr($_SESSION["details"]["id"])."
				)".($_SESSION["details"]["group"] == "student" ? " OR (
					b.`evaluator_type` = 'grad_year'
					AND b.`evaluator_value` = ".$db->qstr($_SESSION["details"]["role"])."
				)" : "")."
				AND a.`evaluation_start` < ".$db->qstr(time())."
				AND a.`evaluation_finish` > ".$db->qstr(time())."
				GROUP BY a.`evaluation_id`
				ORDER BY a.`evaluation_finish` ASC";
$results	= $db->GetAll($query);
if ($results) {
	$evaluation_id			= 0;
	?>
	<table class="tableList" cellspacing="0" summary="List of Evaluations">
	<colgroup>
		<col class="modified" />
		<col class="general" />
		<col class="general" />
		<col class="date" />
		<col class="title" />
		<col class="responses" />
	</colgroup>
	<thead>
		<tr>
			<td class="modified">&nbsp;</td>
			<td class="general">Evaluation Type</td>
			<td class="general">Evaluation Target</td>
			<td class="date">Close Date</td>
			<td class="title">Evaluation Title</td>
			<td class="responses">Progress</td>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ($results as $result) {
		$query = "	SELECT * FROM `evaluation_targets` AS a
					JOIN `evaluations_lu_targets` AS b
					ON a.`target_id` = b.`target_id`
					WHERE a.`evaluation_id` = ".$result["evaluation_id"];
		$evaluation_target = $db->GetRow($query);

		$query = "	SELECT COUNT(`efquestion_id`) FROM `evaluation_form_questions`
					WHERE `eform_id` = ".$db->qstr($result["eform_id"])."
					GROUP BY `eform_id`";
		$evaluation_questions = $db->GetOne($query);
		
		$query = "	SELECT a.*, COUNT(b.`eresponse_id`) AS `responses` FROM `evaluation_progress` AS a
					LEFT JOIN `evaluation_responses` AS b
					ON a.`eprogress_id` = b.`eprogress_id`
					WHERE a.`evaluation_id` = ".$db->qstr($result["evaluation_id"])."
					AND a.`proxy_id` = ".$db->qstr($_SESSION["details"]["id"])."
					GROUP BY b.`eprogress_id`
					ORDER BY `responses` ASC";
		$evaluation_progress = $db->GetRow($query);
	
		$query = "	SELECT COUNT(`eresponse_id`) FROM `evaluation_responses` 
					WHERE `eprogress_id` = ".$db->qstr($evaluation_progress["eprogress_id"])."
					GROUP BY `eprogress_id`";
		$evaluation_responses = $db->GetOne($query);
		
		
		
		$click_url = ENTRADA_URL."/evaluations?section=attempt&id=".$result["evaluation_id"];
		
		echo "<tr>\n";
		echo "	<td>&nbsp;</td>\n";
		echo "	<td><a href=\"".$click_url."\">".(!empty($evaluation_target["target_title"]) ? $evaluation_target["target_title"] : "No Type Found")."</a></td>\n";
		echo "	<td><a href=\"".$click_url."\">".(!empty($evaluation_target["target_value"]) ? fetch_evaluation_target_title($evaluation_target) : "No Target")."</a></td>\n";
		echo "	<td><a href=\"".$click_url."\">".date(DEFAULT_DATE_FORMAT, $result["evaluation_finish"])."</a></td>\n";
		echo "	<td><a href=\"".$click_url."\">".html_encode($result["evaluation_title"])."</a></td>\n";
		echo "	<td><a href=\"".$click_url."\">".($evaluation_progress["responses"] ? ((int)$evaluation_progress["responses"]) : "0")."/".($evaluation_questions ? ((int)$evaluation_questions) : "0")."</a></td>\n";
		echo "</tr>\n";
	}
	?>
	</tbody>
	</table>
	<?php
}
?>
