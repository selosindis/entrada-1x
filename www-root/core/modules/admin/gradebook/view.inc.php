
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
} elseif (!$ENTRADA_ACL->amIAllowed("gradebook", "read", false) || !$ENTRADA_ACL->amIAllowed("assessment", "read", false)) {
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
				if (@in_array(trim($_GET["sb"]), array("name", "year", "type", "scheme"))) {
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
					$sort_by	= "`assessments`.`name` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]).", `assessments`.`grad_year` ASC";
					break;
				case "year" :
					$sort_by	= "`assessments`.`grad_year` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
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
			
			 if ($ENTRADA_ACL->amIAllowed("assessment", "create", false)) { ?>
				<div style="float: right">
					<ul class="page-action">
						<li><a id="gradebook_assessment_add" href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE . "/assessments/?" . replace_query(array("section" => "add", "step" => false)); ?>" class="strong-green">Add New Assessment</a></li>
					</ul>
				</div>
				<div style="clear: both"><br/></div>
			<?php
			}
			
			// Fetch all associated assessments
			$query = "	SELECT `assessments`.`assessment_id`,`assessments`.`grad_year`,`assessments`.`name`,`assessments`.`type`, `assessment_marking_schemes`.`name` as 'marking_scheme_name'
						FROM `assessments`
						LEFT JOIN `assessment_marking_schemes` ON `assessments`.`marking_scheme_id` = `assessment_marking_schemes`.`id`
						WHERE `course_id` = ".$db->qstr($COURSE_ID)."
						ORDER BY %s
						LIMIT %s, %s";
						
			$query = sprintf($query, $sort_by, $limit_parameter, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
			$assessments = 	$db->GetAll($query);
			if($assessments) {
				if ($total_pages > 1) {
					echo "<div id=\"pagination-links\">\n";
					echo "Pages: ".$pagination->GetPageLinks();
					echo "</div>\n";
				}
				if ($ENTRADA_ACL->amIAllowed("assessment", "delete", false)) {
					echo "<form action=\"".ENTRADA_URL . "/admin/gradebook/assessments?".replace_query(array("section" => "delete", "step"=>1))."\" method=\"post\">";
				}
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
						<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "name") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("name", "Name", "assessments"); ?></td>
						<td class="general<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "year") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("year", "Graduating Year", "assessments"); ?></td>
						<td class="general<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "type") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("type", "Assessment Type", "assessments"); ?></td>
						<td class="general<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "scheme") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("scheme", "Marking Scheme", "assessments"); ?></td>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td></td>
						<td colspan="3" style="padding-top: 10px">
							<input type="submit" class="button" value="Delete Selected" />
						</td>
						<td><a id="fullscreen-edit" class="button" href="<?php echo ENTRADA_URL . "/admin/gradebook?" . replace_query(array("section" => "api-edit")); ?>"><div>Fullscreen</div></a>
					</tr>
				</tfoot>
				<tbody>
					<?php
					foreach($assessments as $key => $assessment) {
						$url = ENTRADA_URL."/admin/gradebook/assessments?section=grade&amp;id=".$COURSE_ID."&amp;assessment_id=".$assessment["assessment_id"];
						
						echo "<tr id=\"assessment-".$assessment["assessment_id"]."\">";
						if ($ENTRADA_ACL->amIAllowed("assessment", "delete", false)) {
							echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"delete[]\" value=\"".$assessment["assessment_id"]."\" /></td>\n";
						} else {
							echo "	<td class=\"modified\"><img src=\"".ENTRADA_URL."/images/pixel.gif\" width=\"19\" height=\"19\" alt=\"\" title=\"\" /></td>";
						}
						echo "	<td class=\"title\"><a href=\"$url\">".$assessment["name"]."</a></td>";
						echo "	<td class=\"general\"><a href=\"$url\">".$assessment["grad_year"]."</a></td>";
						echo "	<td class=\"general\"><a href=\"$url\">".$assessment["type"]."</a></td>";
						echo "	<td class=\"general\"><a href=\"$url\">".$assessment["marking_scheme_name"]."</a></td>";
						echo "</tr>";
					}
					?>
				</tbody>
			</table>
			<div class="gradebook_edit" style="display: none;"></div>
				<?php
				if ($ENTRADA_ACL->amIAllowed("assessment", "delete", false)) {
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
