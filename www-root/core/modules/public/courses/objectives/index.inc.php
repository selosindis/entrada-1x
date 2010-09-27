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
 * This file displays the list of objectives pulled 
 * from the entrada.global_lu_objectives table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_OBJECTIVES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
		header("Location: ".ENTRADA_URL);
		exit;
} elseif (!$ENTRADA_ACL->amIAllowed($MODULES["objectives"]["resource"], "read", false)) {
	$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
		echo "<h1>Competencies by Course</h1>";
		$objectives = objectives_build_course_competencies_array();
		?>
		<style type="text/css">
		.title {
			vertical-align: bottom;
		}
		.vertical {
			-moz-transform:rotate(-90deg); 
			-webkit-transform: rotate(-90deg);
			-o-transform: rotate(-90deg);
			writing-mode: tb-rl;
			filter: flipv fliph;
			overflow: visible;
			white-space: nowrap;
		}
		.term {
			background-color: #EBEBEB;
		}
		.middle {
			vertical-align: middle;
			width: 25px;
		}
		table.tableList tbody tr td.bottom {
			vertical-align: bottom;
			padding-bottom: 15px;
		}
		.title {
			border-right: 1px solid #EBEBEB;
		}
		.objectives {
			border-right: 1px solid #EBEBEB;
		}
		li {
			margin-top: 10px;
		}
		</style>
		<table class="tableList" cellspacing="0" summary="List of Objectives">
			<tbody>
				<tr style="height: 200px;">
					<td class="modified">&nbsp;</td>
					<td class="title" style="padding-bottom: 20px; border-right: none;"><h3>Courses</h3></td>
					<td class="title" style="padding: 0 0 80px 50px;"><h3 class="vertical">Competencies</h3></td>
					<?php
						foreach ($objectives["competencies"] as $competency_id => $competency) {
							?>
							<td class="title middle bottom"><div class="vertical"><?php echo "<a href=\"".ENTRADA_URL."/courses/objectives?section=competency-courses&id=".$competency_id."\" style=\"text-decoration: none;\">".$competency."</a>"; ?></div></td>
							<?php
						}
					?>
				</tr>
				<?php
				foreach ($objectives["courses"] as $course_id => $course) {
					if (isset($course["new_term"]) && $course["new_term"]) {
						echo "<tr style=\"border-top: 5px solid #EBEBEB;\">";
					} else {
						echo "<tr>";
					}
					if (isset($course["total_in_term"]) && $course["total_in_term"]) {
						echo "<td class=\"term\" style=\"border-bottom: 5px solid white;\" rowspan=\"".$course["total_in_term"]."\"><div class=\"vertical\">".$course["term_name"]."</div></td>";
					}
					?>
						<td class="objectives" colspan="2"><?php echo "<a href=\"".ENTRADA_URL."/courses/objectives?section=course-objectives&cid=".$course_id."\" style=\"text-decoration: none;\">".html_encode($course["course_name"])."</a>"; ?></td>
						<?php
						foreach ($course["competencies"] as $COMPETENCY_ID => $competency) {
							?>
							<td class="objectives" style="text-align: center;">
							<?php
							if ($competency) {
								echo "<a href=\"".ENTRADA_URL."/courses/objectives?section=course-competency-objectives&id=".$COMPETENCY_ID."&cid=".$course_id."\" style=\"text-decoration: none;\">".html_encode($competency)."</a>";
							} else {
								echo "&nbsp;";
							}
							?>
							</td>
							<?php
						}
						?>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php
}