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
<h1>My Evaluations and Assessments</h1>
<?php

require_once("Models/evaluation/Evaluation.class.php");
$evaluations = Evaluation::getEvaluatorEvaluations();

if ($evaluations) {
	$evaluation_id = 0;
	echo "<div class=\"no-printing\">\n";
    echo "    <ul class=\"nav nav-tabs\">\n";
	echo "		<li".($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"] == "available" ? " class=\"active\"" : "")." style=\"width:25%;\"><a id=\"available\" onclick=\"loadTab(this.id)\">Display Available</a></li>\n";
	echo "		<li".($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"] == "overdue" ? " class=\"active\"" : "")." style=\"width:25%;\"><a id=\"overdue\" onclick=\"loadTab(this.id)\">Display Overdue</a></li>\n";
	echo "		<li".($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"] == "complete" ? " class=\"active\"" : "")." style=\"width:25%;\"><a id=\"complete\" onclick=\"loadTab(this.id)\">Display Completed</a></li>\n";
	echo "		<li".($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"] == "all" ? " class=\"active\"" : "")." style=\"width:25%;\"><a id=\"all\" onclick=\"loadTab(this.id)\">Display All</a></li>\n";
	echo "	</ul>\n";
	echo "</div>\n";
	echo "<br />";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/jquery/jquery.dataTables.min.js\"></script>";
	$HEAD[] = "<script type=\"text/javascript\">
	var eTable;
	jQuery(document).ready(function() {
		eTable = jQuery('#evaluations').dataTable(
			{    
				'sPaginationType': 'full_numbers',
				'aoColumns' : [ 
						null,
						null,
						null,
						{'sType': 'alt-string'},
						null,
						null,
                        { 'bVisible' : false }
					],
				'bInfo': false
			}
		);
		eTable.fnFilter('".($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"] == "all" ? "" : $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"])."', 6);
	});
	
	function loadTab (value) {
		if (!$(value).hasClassName('active')) {
			new Ajax.Request('".ENTRADA_URL."/evaluations', {
				method: 'get',
				parameters: {
								'view_type': value,
								'ajax': 1
							}
			});
			var filterval = (value == 'all' ? '' : value)
			eTable.fnFilter(filterval, 6);
			$$('li.active').each(function (e) {
				e.removeClassName('active');
			});
			$(value).parentNode.addClassName('active');
		}
	}
	</script>";
	?>
	<table id="evaluations" class="tableList" cellspacing="0" summary="List of Evaluations and Assessments">
	<colgroup>
		<col class="modified" />
		<col class="general" />
		<col class="general" />
		<col class="date" />
		<col class="title" />
		<col class="general" />
		<col class="general" />
	</colgroup>
	<thead>
		<tr>
			<td class="modified">&nbsp;</td>
			<td class="general">Type</td>
			<td class="general">Target(s)</td>
			<td class="date-small">Close Date</td>
			<td class="title">Title</td>
			<td class="date-smallest">Submitted</td>
			<td class="general">Status</td>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ($evaluations as $evaluation) {
		if ($evaluation["click_url"]) {
			echo "<tr>\n";
			echo "	<td>&nbsp;</td>\n";
			echo "	<td><a href=\"".$evaluation["click_url"]."\">".(!empty($evaluation["target_title"]) ? $evaluation["target_title"] : "No Type Found")."</a></td>\n";
			echo "	<td><a href=\"".$evaluation["click_url"]."\">".(!empty($evaluation["evaluation_target_title"]) ? $evaluation["evaluation_target_title"] : "No Target")."</a></td>\n";
			echo "	<td><a href=\"".$evaluation["click_url"]."\" alt=\"".$evaluation["evaluation_finish"]."\">".date(DEFAULT_DATE_FORMAT, $evaluation["evaluation_finish"])."</a></td>\n";
			echo "	<td><a href=\"".$evaluation["click_url"]."\">".html_encode($evaluation["evaluation_title"])."</a></td>\n";
			echo "	<td><a href=\"".$evaluation["click_url"]."\">".($evaluation["completed_attempts"] ? ((int)$evaluation["completed_attempts"]) : "0")."/".($evaluation["max_submittable"] ? ((int)$evaluation["max_submittable"]) : "0")."</a></td>\n";
			echo "	<td>".($evaluation["max_submittable"] > $evaluation["completed_attempts"] && $evaluation["evaluation_finish"] < time() ? "overdue available" : ($evaluation["max_submittable"] > $evaluation["completed_attempts"] ? "available" : "complete"))."</td>";
			echo "</tr>\n";
		} else {
			echo "<tr>\n";
			echo "	<td class=\"content-small\">&nbsp;</td>\n";
			echo "	<td class=\"content-small\">".(!empty($evaluation["target_title"]) ? $evaluation["target_title"] : "No Type Found")."</td>\n";
			echo "	<td class=\"content-small\">".(!empty($evaluation["evaluation_target_title"]) ? $evaluation["evaluation_target_title"] : "No Target")."</td>\n";
			echo "	<td class=\"content-small\"><span alt=\"".$evaluation["evaluation_finish"]."\">".date(DEFAULT_DATE_FORMAT, $evaluation["evaluation_finish"])."</span></td>\n";
			echo "	<td class=\"content-small\">".html_encode($evaluation["evaluation_title"])."</td>\n";
			echo "	<td class=\"content-small\">".($evaluation["completed_attempts"] ? ((int)$evaluation["completed_attempts"]) : "0")."/".($evaluation["max_submittable"] ? ((int)$evaluation["max_submittable"]) : "0")."</td>\n";
			echo "	<td>".($evaluation["max_submittable"] > $evaluation["completed_attempts"] && $evaluation["evaluation_finish"] < time() ? "overdue available" : ($evaluation["max_submittable"] > $evaluation["completed_attempts"] ? "available" : "complete"))."</td>";
			echo "</tr>\n";
		}
	}
	?>
	</tbody>
	</table>
	<?php
} else {
	if (!isset($evaluations) || !$evaluations) {
		?>
		<div class="display-generic">
			There are no evaluations or assessments <strong>assigned to you</strong> in the system at this time.
		</div>
		<?php
	}
}