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
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_GRADEBOOK"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("gradebook", "update", false)) {
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
		if ($course_details && $ENTRADA_ACL->amIAllowed(new GradebookResource($course_details["course_id"], $course_details["organisation_id"]), "read")) {
			$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/gradebook?".replace_query(array("section" => "view", "id" => $COURSE_ID, "step" => false)), "title" => "Assessments");
			
			/**
			 * Update requested column to sort by.
			 * Valid: director, name
			 */
			if (isset($_GET["sb"])) {
				if (@in_array(trim($_GET["sb"]), array("name", "type", "scheme"))) {
					$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= trim($_GET["sb"]);
				} else {
					$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "name";
				}

				$_SERVER["QUERY_STRING"]	= replace_query(array("sb" => false));
			} else {
				if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"])) {
					$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "name";
				}
			}

			/**
			 * Update requested order to sort by.
			 * Valid: asc, desc
			 */
			if (isset($_GET["so"])) {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = ((strtolower($_GET["so"]) == "desc") ? "desc" : "asc");

				$_SERVER["QUERY_STRING"] = replace_query(array("so" => false));
			} else {
				if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"])) {
					$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = "asc";
				}
			}

			/**
			 * Update requsted number of rows per page.
			 * Valid: any integer really.
			 */
			if ((isset($_GET["pp"])) && ((int) trim($_GET["pp"]))) {
				$integer = (int) trim($_GET["pp"]);

				if (($integer > 0) && ($integer <= 250)) {
					$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] = $integer;
				}

				$_SERVER["QUERY_STRING"] = replace_query(array("pp" => false));
			} else {
				if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"])) {
					$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] = DEFAULT_ROWS_PER_PAGE;
				}
			}
			
			/**
			 * Check if preferences need to be updated on the server at this point.
			 */
			preferences_update($MODULE, $PREFERENCES);

			/**
			 * Provide the queries with the columns to order by.
			 */
			switch($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) {
				case "name" :
					$sort_by	= "`assessments`.`name` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]).", `assessments`.`cohort` ASC";
					break;
				case "type" :
					$sort_by	= "`assessments`.`type` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
				break;
				case "scheme" :
					$sort_by	= "`assessment_marking_schemes`.`name` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
				break;
				default :
					$sort_by	= "`assessments`.`name` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
				break;
			}
			
			$query	= "	SELECT COUNT(*) AS `total_rows` FROM FROM `assessments` WHERE `course_id` = ".$db->qstr($COURSE_ID);			
			$result	= $db->GetRow($query);
			if ($result) {
				$total_rows	= $result["total_rows"];

				if ($total_rows <= $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) {
					$total_pages = 1;
				} elseif (($total_rows % $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == 0) {
					$total_pages = (int) ($total_rows / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
				} else {
					$total_pages = (int) ($total_rows / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) + 1;
				}
			} else {
				$total_rows		= 0;
				$total_pages	= 1;
			}

			/**
			 * Check if pv variable is set and see if it's a valid page, other wise page 1 it is.
			 */
			if (isset($_GET["pv"])) {
				$page_current = (int) trim($_GET["pv"]);

				if (($page_current < 1) || ($page_current > $total_pages)) {
					$page_current = 1;
				}
			} else {
				$page_current = 1;
			}

			if ($total_pages > 1) {
				$pagination = new Pagination($page_current, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"], $total_rows, ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "edit", "id" => $COURSE_ID, "step" => false)), replace_query());
			}
			
			$limit_parameter = (int) (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] * $page_current) - $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
						
			courses_subnavigation($course_details);
			
			$curriculum_path = curriculum_hierarchy($COURSE_ID);
			if ((is_array($curriculum_path)) && (count($curriculum_path))) {
				echo "<h1>" . implode(": ", $curriculum_path) . " Gradebook </h1>";
			}
			
			 if ($ENTRADA_ACL->amIAllowed("gradebook", "create", false)) { ?>
				<div style="float: right">
					<ul class="page-action">
						<li><a id="gradebook_assessment_add" href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE . "/assessments/?" . replace_query(array("section" => "add", "step" => false)); ?>" class="strong-green">Add New Assessment</a></li>
					</ul>
				</div>
				<div style="clear: both"><br/></div>
			<?php
			}
			
			$query =  "SELECT DISTINCT `assessments`.`course_id`, `assessments`.`cohort` FROM `assessments`
					   WHERE `course_id` =". $db->qstr($COURSE_ID)."
					   ORDER BY `cohort`";
			$cohorts = $db->GetAll($query);
			if($cohorts) {
				if ($total_pages > 1) {
					echo "<div id=\"pagination-links\">\n";
					echo "Pages: ".$pagination->GetPageLinks();
					echo "</div>\n";
				}
				if ($ENTRADA_ACL->amIAllowed("gradebook", "delete", false)) {
					echo "<form action=\"".ENTRADA_URL . "/admin/gradebook/assessments?".replace_query(array("section" => "delete", "step"=>1))."\" method=\"post\">";
				}
				?>
				
				<table class="tableList" cellspacing="0" summary="List of Assessments" id="assessment_list">			
					<tfoot>
						<tr>
							<td style="padding-top: 10px; border-bottom:0;"colspan="2">
								<script type="text/javascript" charset="utf-8">
									function exportSelected() {
										var ids = [];
										$$('#assessment_list .modified input:checked').each(function(checkbox) {
											ids.push($F(checkbox));
										});
										if(ids.length > 0) {
											window.location = '<?php echo ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "io", "download" => "csv", "assessment_ids" => false)); ?>&assessment_ids='+ids.join(',');
										} else {
											alert("You must select some assessments to export.");
										}
										return false;
									}
								</script>
								<input type="submit" class="button" value="Delete Selected" />
								<input type="submit" class="button" value="Export Selected" onclick="exportSelected(); return false;"/>
							</td>
							<td colspan="2" style="padding-top: 10px; border-bottom: 0; "><a id="fullscreen-edit" class="button" style="float:right;" href="<?php echo ENTRADA_URL . "/admin/gradebook?" . replace_query(array("section" => "api-edit")); ?>"><div>Fullscreen</div></a></td>
						</tr>
						<tr>
							<td style="border-bottom:0;"></td>
						</tr>
					</tfoot>
					<tbody>
					
					<?php
					if ($cohorts) {
						foreach ($cohorts as $cohort) {
							echo "<tr>";
							echo "<td style=\"width: 20px;\"></td>";
							echo "<td style=\"width: 400px;\"><h2 style=\"border-bottom: 0;\">".groups_get_name($cohort["cohort"])."</h2></td>";
							echo "<td colspan=\"2\"><h2 style=\"border-bottom: 0;\">Grade Weighting</h2></td>";
							echo "</tr>";
							
							$query =  "SELECT `assessments`.`course_id`, `assessments`.`assessment_id`, `assessments`.`name`, `assessments`.`grade_weighting` FROM `assessments`
									   WHERE `cohort` =" . $db->qstr($cohort["cohort"])."
									   AND `course_id` =". $db->qstr($COURSE_ID);
							
							$results = $db->GetAll($query);
							if ($results) {
								$query =  "SELECT `assessments`.`course_id`, SUM(`assessments`.`grade_weighting`) AS `grade_weighting` FROM `assessments`
										   WHERE `cohort` =". $db->qstr($cohort["cohort"])." 
										   AND `course_id` =". $db->qstr($COURSE_ID);
								
								$total_grade_weights = $db->GetAll($query);
								foreach ($results as $result) {
									$url = ENTRADA_URL."/admin/gradebook/assessments?section=grade&amp;id=".$COURSE_ID."&amp;assessment_id=".$result["assessment_id"];
									echo "<tr id=\"assessment-".$result["assessment_id"]."\">";
									if ($ENTRADA_ACL->amIAllowed("gradebook", "delete", false)) {
										echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"delete[]\" value=\"".$result["assessment_id"]."\" /></td>\n";
									} else {
										echo "	<td class=\"modified\" width=\"20\"><img src=\"".ENTRADA_URL."/images/pixel.gif\" width=\"19\" height=\"19\" alt=\"\" title=\"\" /></td>";
									}
									echo "<td><a href=\"$url\" width=\"367\">".$result["name"]."</a></td>";
									echo "<td colspan=\"2\"><a href=\"$url\">".$result["grade_weighting"]. "%</a></td>"; 
									echo "</tr>";
								}
								echo "<tr>";
								echo "<td style=\"border-bottom: 0\"></td>";
								echo "<td style=\"border-bottom: 0\"></td>";
								foreach ($total_grade_weights as $total_grade_weight) {
									if ($total_grade_weight["grade_weighting"] < '100') {
										echo "<td style=\"color: #ff2431; border-bottom: 0\">". $total_grade_weight["grade_weighting"]."%</td>";
									} else {
										echo "<td style=\"border-bottom: 0\">". $total_grade_weight["grade_weighting"]."%</td>";
									}
								}
								echo "</tr>";
							}
						}
					}
					?>
					</tbody>
				</table>
				<div class="gradebook_edit" style="display: none;"></div>
				<?php
				if ($ENTRADA_ACL->amIAllowed("gradebook", "delete", false)) {
					echo "</form>";
				}
			} else {
				// No assessments in this course.
				?>
				<div class="display-notice">
					<h3>No Assessments for <?php echo $course_details["course_name"]; ?></h3>
					There are no assessments in the system for this course. You can create new ones by clicking the <strong>Add New Assessment</strong> link above.
				</div>
				<?php
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
