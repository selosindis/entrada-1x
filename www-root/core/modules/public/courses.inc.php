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
 * Module:	Registered Courses
 * Area:		Public
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 *
 * $Id: courses.inc.php 1171 2010-05-01 14:39:27Z ad29 $
*/

if (!defined("PARENT_INCLUDED")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("course", "read", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	$COURSE_ID			= 0;
	$ORGANISATION_ID	= $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"];

	if ((isset($_GET["id"])) && ((int) trim($_GET["id"]))) {
		$COURSE_ID = (int) trim($_GET["id"]);
	}

	if ((isset($_GET["org"])) && ((int) trim($_GET["org"]))) {
		$ORGANISATION_ID = (int) trim($_GET["org"]);
	}

	/**
	 * Check for groups which have access to the administrative side of this module
	 * and add the appropriate toggle sidebar item.
	 */
	if ($ENTRADA_ACL->amIAllowed(new CourseContentResource(null, null, false), 'update')) {
		switch ($_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]) {
			case "admin" :
				$admin_wording	= "Administrator View";
				$admin_url		= ENTRADA_URL."/admin/".$MODULE.(($COURSE_ID) ? "?".replace_query(array("section" => "edit", "id" => $COURSE_ID)) : "");
			break;
			case "pcoordinator" :
				$admin_wording	= "Coordinator View";
				$admin_url		= ENTRADA_URL."/admin/".$MODULE.(($COURSE_ID) ? "?".replace_query(array("section" => "content", "id" => $COURSE_ID)) : "");
			break;
			case "director" :
				$admin_wording	= "Director View";
				$admin_url		= ENTRADA_URL."/admin/".$MODULE.(($COURSE_ID) ? "?".replace_query(array("section" => "content", "id" => $COURSE_ID)) : "");
			break;
			default :
				$admin_wording	= "";
				$admin_url		= "";
			break;
		}

		$sidebar_html  = "<ul class=\"menu\">\n";
		$sidebar_html .= "	<li class=\"on\"><a href=\"".ENTRADA_URL."/".$MODULE.(($COURSE_ID) ? "?".replace_query(array("id" => $COURSE_ID, "action" => false)) : "")."\">Student View</a></li>\n";
		if (($admin_wording) && ($admin_url)) {
			$sidebar_html .= "<li class=\"off\"><a href=\"".$admin_url."\">".html_encode($admin_wording)."</a></li>\n";
		}
		$sidebar_html .= "</ul>\n";

		new_sidebar_item("Display Style", $sidebar_html, "display-style", "open");
	}

	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/".$MODULE, "title" => "View Courses");

	$COURSE_LIST	= array();

	$query		= "	SELECT * FROM `courses` 
					WHERE `organisation_id`=".$db->qstr($ORGANISATION_ID)." 
					AND `course_active` = '1'
					ORDER BY `course_name` ASC";
	$results	= $db->GetAll($query);
	if ($results) {
		foreach ($results as $result) {
			if ($ENTRADA_ACL->amIAllowed(new CourseResource($result["course_id"], $result["organisation_id"]), 'read')) {
				$COURSE_LIST[$result["course_id"]] = html_encode($result["course_name"].(($result["course_code"]) ? ": ".$result["course_code"] : ""));
			}
		}
	}

	$ORGANISATION_LIST	= array();
	$query		= "SELECT `organisation_id`, `organisation_title` FROM `".AUTH_DATABASE."`.`organisations` ORDER BY `organisation_title` ASC";
	$results	= $db->GetAll($query);
	if ($results) {
		foreach ($results as $result) {
			if ($ENTRADA_ACL->amIAllowed("resourceorganisation".$result["organisation_id"], "read")) {
				$ORGANISATION_LIST[$result["organisation_id"]] = html_encode($result["organisation_title"]);
			}
		}
	}

	/**
	 * If we were going into the $COURSE_ID
	 */
	if ($COURSE_ID) {
		$course_community = $db->GetOne("	SELECT b.`community_url` FROM `community_courses` AS a
											JOIN `communities` AS b
											ON a.`community_id` = b.`community_id`
											WHERE a.`course_id` = ".$db->qstr($COURSE_ID));
		if ($course_community) {
			header("Location: ".ENTRADA_URL."/community".$course_community);
			exit;
		}

		$query			= "	SELECT * FROM `courses` 
							WHERE `course_id`=".$db->qstr($COURSE_ID)."
							AND `course_active` = '1'";
		$course_details	= ((USE_CACHE) ? $db->CacheGetRow(CACHE_TIMEOUT, $query) : $db->GetRow($query));
		if (!$course_details) {
			$ERROR++;
			$ERRORSTR[] = "The course identifier that was presented to this page currently does not exist in the system.";

			echo display_error();
		} else {
			if ($ENTRADA_ACL->amIAllowed(new CourseResource($COURSE_ID, $result['organisation_id']), 'read')) {
				add_statistic($MODULE, "view", "course_id", $COURSE_ID);

				$BREADCRUMB[]		= array("url" => ENTRADA_URL."/".$MODULE."?".replace_query(array("id" => $course_details["course_id"])), "title" => $course_details["course_name"].(($course_details["course_code"]) ? ": ".$course_details["course_code"] : ""));

				$OTHER_DIRECTORS	= array();

				$sub_query		= "SELECT `proxy_id` FROM `course_contacts` WHERE `course_contacts`.`course_id`=".$db->qstr($COURSE_ID)." AND `course_contacts`.`contact_type` = 'director' ORDER BY `contact_order` ASC";
				$sub_results	= $db->GetAll($sub_query);
				if ($sub_results) {
					foreach ($sub_results as $sub_result) {
						$OTHER_DIRECTORS[] = $sub_result["proxy_id"];
					}
				}

				// Meta information for this page.
				$PAGE_META["title"]			= $course_details["course_name"].(($course_details["course_code"]) ? ": ".$course_details["course_code"] : "")." - ".APPLICATION_NAME;
				$PAGE_META["description"]	= trim(str_replace(array("\t", "\n", "\r"), " ", html_encode(strip_tags($course_details["course_description"]))));
				$PAGE_META["keywords"]		= "";

				$course_details_section			= true;
				$course_description_section		= false;
				$course_objectives_section		= false;
				$course_assessment_section		= false;
				$course_textbook_section		= false;
				$course_message_section			= false;
				$course_resources_section		= true;
				?>
				<div class="no-printing" style="text-align: right">
					<form>
					<label for="course-quick-select" class="content-small">Course Quick Select:</label>
					<select id="course-quick-select" name="course-quick-select" style="width: 300px" onchange="window.location='<?php echo ENTRADA_URL; ?>/courses?id='+this.options[this.selectedIndex].value">
					<option value="">-- Select a Course --</option>
					<?php
					foreach ($COURSE_LIST as $key => $course_name) {
						echo "<option value=\"".$key."\"".(($key == $COURSE_ID) ? " selected=\"selected\"" : "").">".$course_name."</option>\n";
					}
					?>
					</select>
					</form>
				</div>
				<div>
					<div class="no-printing" style="float: right; margin-top: 8px">
						<a href="<?php echo ENTRADA_URL."/".$MODULE."?id=".$course_details["course_id"]; ?>"><img src="<?php echo ENTRADA_URL; ?>/images/page-link.gif" width="16" height="16" alt="Link to this page" title="Link to this page" border="0" style="margin-right: 3px; vertical-align: middle" /></a> <a href="<?php echo ENTRADA_URL."/".$MODULE."?id=".$course_details["course_id"]; ?>" style="font-size: 10px; margin-right: 8px">Link to this page</a>
						<a href="javascript:window.print()"><img src="<?php echo ENTRADA_URL; ?>/images/page-print.gif" width="16" height="16" alt="Print this page" title="Print this page" border="0" style="margin-right: 3px; vertical-align: middle" /></a> <a href="javascript: window.print()" style="font-size: 10px; margin-right: 8px">Print this page</a>
					</div>

					<h1><?php echo html_encode($course_details["course_name"].(($course_details["course_code"]) ? ": ".$course_details["course_code"] : "")); ?></h1>
				</div>

				<a name="course-details-section"></a>
				<h2 title="Course Details Section">Course Details</h2>
				<div id="course-details-section">
					<?php
					echo "<table summary=\"Course Details\">\n";
					echo "	<colgroup>\n";
					echo "		<col style=\"width: 22%\" />\n";
					echo "		<col style=\"width: 78%\" />\n";
					echo "	</colgroup>\n";
					echo "	<tbody>\n";

					if ($course_url = clean_input($course_details["course_url"], array("notags", "nows"))) {
						echo "	<tr>\n";
						echo "		<td>External Website</td>\n";
						echo "		<td><a href=\"".html_encode($course_url)."\" target=\"_blank\">View <strong>".html_encode($course_details["course_name"])."</strong> Website</a></td>\n";
						echo "	</tr>\n";
						echo "	<tr>\n";
						echo "		 <td colspan=\"2\">&nbsp;</td>\n";
						echo "	</tr>\n";
					}

					echo "		<tr>\n";
					echo "			<td style=\"vertical-align: top\">Course Directors</td>\n";
					echo "			<td>\n";
										$squery = "	SELECT a.`proxy_id`, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, b.`email`
													FROM `course_contacts` AS a
													LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
													ON b.`id` = a.`proxy_id`
													WHERE a.`course_id` = ".$db->qstr($course_details["course_id"])."
													AND a.`contact_type` = 'director'
													AND b.`id` IS NOT NULL
													ORDER BY a.`contact_order` ASC";
										$results = $db->GetAll($squery);
										if ($results) {
											foreach ($results as $key => $sresult) {
												echo "<a href=\"mailto:".html_encode($sresult["email"])."\">".html_encode($sresult["fullname"])."</a><br />\n";
											}
										} else {
											echo "To Be Announced";
										}
					echo "			</td>\n";
					echo "		</tr>\n";
					echo "		<tr>\n";
					echo "			<td style=\"vertical-align: top\">Curriculum Coordinators</td>\n";
					echo "			<td>\n";
										$squery = "	SELECT a.`proxy_id`, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, b.`email`
													FROM `course_contacts` AS a
													LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
													ON b.`id` = a.`proxy_id`
													WHERE a.`course_id` = ".$db->qstr($course_details["course_id"])."
													AND a.`contact_type` = 'ccoordinator'
													AND b.`id` IS NOT NULL
													ORDER BY a.`contact_order` ASC";
										$results = $db->GetAll($squery);
										if ($results) {
											foreach ($results as $key => $sresult) {
												echo "<a href=\"mailto:".html_encode($sresult["email"])."\">".html_encode($sresult["fullname"])."</a><br />\n";
											}
										} else {
											echo "To Be Announced";
										}
					echo "			</td>\n";
					echo "		</tr>\n";
					if((int) $course_details["pcoord_id"]) {
						echo "	<tr>\n";
						echo "		<td>Program Coordinator</td>\n";
						echo "		<td><a href=\"mailto:".get_account_data("email", $course_details["pcoord_id"])."\">".get_account_data("fullname", $course_details["pcoord_id"])."</a></td>\n";
						echo "	</tr>\n";
					}

					if((int) $course_details["evalrep_id"]) {
						echo "	<tr>\n";
						echo "		<td>Evaluation Rep</td>\n";
						echo "		<td><a href=\"mailto:".get_account_data("email", $course_details["evalrep_id"])."\">".get_account_data("fullname", $course_details["evalrep_id"])."</a></td>\n";
						echo "	</tr>\n";
					}

					if((int) $course_details["studrep_id"]) {
						echo "	<tr>\n";
						echo "		<td>Student Rep</td>\n";
						echo "		<td><a href=\"mailto:".get_account_data("email", $course_details["studrep_id"])."\">".get_account_data("fullname", $course_details["studrep_id"])."</a></td>\n";
						echo "	</tr>\n";
					}

					if (clean_input($course_details["course_description"], array("notags", "nows")) != "") {
						$course_description_section = true;

						echo "	<tr>\n";
						echo "		<td colspan=\"2\">&nbsp;</td>\n";
						echo "	</tr>\n";
						echo "	<tr>\n";
						echo "		<td colspan=\"2\">\n";
						echo "			<h3>Course Description</h3>\n";
						echo 			trim(strip_selected_tags($course_details["course_description"], array("font")));
						echo "		</td>\n";
						echo "	</tr>\n";
					}

					if (clean_input($course_details["course_message"], array("notags", "nows")) != "") {
						$course_message_section = true;
						echo "	<tr>\n";
						echo "		<td colspan=\"2\">&nbsp;</td>\n";
						echo "	</tr>\n";
						echo "	<tr>\n";
						echo "		<td colspan=\"2\">\n";
						echo "			<h3>Director's Message</h3>\n";
						echo			trim(strip_selected_tags($course_details["course_message"], array("font")));
						echo "		</td>\n";
						echo "	</tr>\n";
					}
					echo "	</tbody>\n";
					echo "</table>\n";
					?>
				</div>

				<?php
				$show_objectives = false;
				$objectives = courses_fetch_objectives(array($COURSE_ID), 1, false);
				foreach ($objectives["objectives"] as $objective) {
					if ((isset($objective["primary"]) && $objective["primary"]) || (isset($objective["secondary"]) && $objective["secondary"]) || (isset($objective["tertiary"]) && $objective["tertiary"])) {
						$show_objectives = true;
						break;
					}
				}

				if ($show_objectives || clean_input($course_details["course_objectives"], array("notags", "nows"))) {
					echo "<a name=\"course-objectives-section\"></a>\n";
					echo "<h2 title=\"Course Objectives Section\">Course Objectives</h2>\n";
					echo "<div id=\"course-objectives-section\">\n";
					echo "	<table summary=\"Course Objectives\">\n";
					echo "		<colgroup>\n";
					echo "			<col style=\"width: 22%\" />\n";
					echo "			<col style=\"width: 78%\" />\n";
					echo "		</colgroup>\n";
					echo "		<tbody>\n";
					if (clean_input($course_details["course_objectives"], array("notags", "nows"))) {
						$course_objectives_section = true;
						echo "		<tr>\n";
						echo "			<td colspan=\"2\" style=\"text-align: justify; padding-left: 25px\">\n";
						echo				trim(strip_selected_tags($course_details["course_objectives"], array("font")));
						echo "			</td>\n";
						echo "		</tr>\n";
					}

					if ($show_objectives) {
						echo "		<tr>\n";
						echo "			<td colspan=\"2\" style=\"text-align: justify;\" class=\"objectives\">\n";
											?>
											<script type="text/javascript">
											function renewList (hierarchy) {
												if (hierarchy != null && hierarchy) {
													hierarchy = 1;
												} else {
													hierarchy = 0;
												}
												new Ajax.Updater('objectives_list', '<?php echo ENTRADA_URL; ?>/api/objectives.api.php',
													{
														method:	'post',
														parameters: 'course_ids=<?php echo $COURSE_ID ?>&hierarchy='+hierarchy
													}
												);
											}
											</script>
											<?php
						echo "				<h3>Curriculum Objectives</h3>";
						echo "				<div style=\"float: right;\">\n";
						echo "					<label for=\"show_hierarchy\" class=\"content-small\" />Show objective hierarchy <input type=\"checkbox\" id=\"show_hierarcy\" onclick=\"renewList(this.checked)\" /></label>\n";
						echo "				</div>\n";
						echo "				<strong>The learner will be able to:</strong>";
						echo "				<div id=\"objectives_list\">\n".course_objectives_in_list($objectives["objectives"], 1, false, false, 1, false)."\n</div>\n";
						echo "			</td>\n";
						echo "		</tr>\n";
						echo "		<tr>\n";
						echo "			<td colspan=\"2\">\n";
						echo "				<h3>Clinical Presentations</h3>";
						$query = "	SELECT b.*
									FROM `course_objectives` AS a
									JOIN `global_lu_objectives` AS b
									ON a.`objective_id` = b.`objective_id`
									WHERE a.`objective_type` = 'event'
									AND b.`objective_active` = '1'
									AND a.`course_id` = ".$db->qstr($COURSE_ID)."
									GROUP BY b.`objective_id`
									ORDER BY b.`objective_order`";
						$results = $db->GetAll($query);
						if ($results) {
							echo "				<ul class=\"objectives\">\n";
							foreach ($results as $result) {
								if ($result["objective_name"]) {
									echo "			<li>".$result["objective_name"]."</li>\n";
								}
							}
							echo "				</ul>\n";
						} else {
							echo "<div class=\"display-notice\">While medical presentations may be used to illustrate concepts in this course, there are no specific presentations from the Medical Council of Canada that have been selected.</div>";
						}
						echo "			</td>\n";
						echo "		</tr>\n";
					}
					echo "		</tbody>";
					echo "	</table>";
					echo "</div>";

					$sidebar_html  = "<div style=\"margin: 2px 0px 10px 3px; font-size: 10px\">\n";
					$sidebar_html .= "	<div><img src=\"".ENTRADA_URL."/images/legend-primary-objective.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> Primary Objective</div>\n";
					$sidebar_html .= "	<div><img src=\"".ENTRADA_URL."/images/legend-secondary-objective.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> Secondary Objective</div>\n";
					$sidebar_html .= "	<div><img src=\"".ENTRADA_URL."/images/legend-tertiary-objective.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> Tertiary Objective</div>\n";
					$sidebar_html .= "</div>\n";

					new_sidebar_item("Objective Importance", $sidebar_html, "objective-legend", "open");
				}
				?>

				<a name="course-resources-section"></a>
				<h2 title="Course Resources Section">Course Resources</h2>
				<div id="course-resources-section">
					<?php
					$query = "	SELECT `course_files`.*, MAX(`statistics`.`timestamp`) AS `last_visited`
								FROM `course_files`
								LEFT JOIN `statistics`
								ON `statistics`.`module`=".$db->qstr($MODULE)."
								AND `statistics`.`proxy_id`=".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
								AND `statistics`.`action`='file_download'
								AND `statistics`.`action_field`='file_id'
								AND `statistics`.`action_value`=`course_files`.`id`
								WHERE `course_files`.`course_id`=".$db->qstr($COURSE_ID)."
								GROUP BY `course_files`.`id`
								ORDER BY `file_category` ASC, `file_title` ASC";
					$results = $db->GetAll($query);
					echo "<table class=\"tableList\" cellspacing=\"0\" summary=\"List of File Attachments\">\n";
					echo "<colgroup>\n";
					echo "	<col class=\"modified\" />\n";
					echo "	<col class=\"file-category\" />\n";
					echo "	<col class=\"title\" />\n";
					echo "	<col class=\"date\" />\n";
					echo "</colgroup>\n";
					echo "<thead>\n";
					echo "	<tr>\n";
					echo "		<td class=\"modified\">&nbsp;</td>\n";
					echo "		<td class=\"file-category sortedASC\"><div class=\"noLink\">File Category</div></td>\n";
					echo "		<td class=\"title\"><div class=\"noLink\">File Title</div></td>\n";
					echo "		<td class=\"date\">Last Updated</td>\n";
					echo "	</tr>\n";
					echo "</thead>\n";
					echo "<tbody>\n";
					if ($results) {
						foreach ($results as $result) {
							$filename	= $result["file_name"];
							$parts		= pathinfo($filename);
							$ext		= $parts["extension"];

							echo "<tr id=\"file-".$result["id"]."\">\n";
							echo "	<td class=\"modified\" style=\"vertical-align: top\">".(((int) $result["last_visited"]) ? (((int) $result["last_visited"] >= (int) $result["updated_date"]) ? "<img src=\"".ENTRADA_URL."/images/accept.png\" width=\"16\" height=\"16\" alt=\"You have already downloaded the latest version.\" title=\"You have already downloaded the latest version.\" />" : "<img src=\"".ENTRADA_URL."/images/exclamation.png\" width=\"16\" height=\"16\" alt=\"An updated version of this file is available.\" title=\"An updated version of this file is available.\" />") : "")."</td>\n";
							echo "	<td class=\"file-category\" style=\"vertical-align: top\">".((isset($RESOURCE_CATEGORIES["course"][$result["file_category"]])) ? html_encode($RESOURCE_CATEGORIES["course"][$result["file_category"]]) : "Unknown Category")."</td>\n";
							echo "	<td class=\"title\" style=\"vertical-align: top; white-space: normal; overflow: visible\">\n";
							echo "		<img src=\"".ENTRADA_URL."/serve-icon.php?ext=".$ext."\" width=\"16\" height=\"16\" alt=\"".strtoupper($ext)." Document\" title=\"".strtoupper($ext)." Document\" style=\"vertical-align: middle\" />\n";
							if (((!(int) $result["valid_from"]) || ($result["valid_from"] <= time())) && ((!(int) $result["valid_until"]) || ($result["valid_until"] >= time()))) {
								echo "	<a href=\"".ENTRADA_URL."/file-course.php?id=".$result["id"]."\" title=\"Click to download ".html_encode($result["file_title"])."\" style=\"font-weight: bold\"".(((int) $result["access_method"]) ? " target=\"_blank\"" : "").">".html_encode($result["file_title"])."</a>";
							} else {
								echo "	<span style=\"color: #666666; font-weight: bold\">".html_encode($result["file_title"])."</span>";
							}
							echo "		<span class=\"content-small\">(".readable_size($result["file_size"]).")</span>";
							echo "		<div class=\"content-small\" style=\"margin-top: 3px; margin-bottom: 5px\">\n";
							if (((int) $result["valid_from"]) && ($result["valid_from"] > time())) {
								echo "		This file will be available for downloading <strong>".date(DEFAULT_DATE_FORMAT, $result["valid_from"])."</strong>.<br /><br />";
							} elseif (((int) $result["valid_until"]) && ($result["valid_until"] < time())) {
								echo "		This file was only available for download until <strong>".date(DEFAULT_DATE_FORMAT, $result["valid_until"])."</strong>. Please contact the primary teacher for assistance if required.<br /><br />";
							}

							if (clean_input($result["file_notes"], array("notags", "nows")) != "") {
								echo "		".trim(strip_selected_tags($result["file_notes"], array("font")))."\n";
							}

							echo "		</div>\n";
							echo "	</td>\n";
							echo "	<td class=\"date\" style=\"vertical-align: top\">".(((int) $result["updated_date"]) ? date(DEFAULT_DATE_FORMAT, $result["updated_date"]) : "Unknown")."</td>\n";
							echo "</tr>\n";
						}
					} else {
						echo "<tr>\n";
						echo "	<td colspan=\"4\">\n";
						echo "		<div class=\"content-small\" style=\"margin-top: 3px; margin-bottom: 5px\">There have been no file downloads added to this course.</div>\n";
						echo "	</td>\n";
						echo "</tr>\n";
					}
					echo "</tbody>\n";
					echo "</table>\n";
					echo "<br />\n";

					$query = "SELECT * FROM `course_links` WHERE `course_id`=".$db->qstr($COURSE_ID)." ORDER BY `link_title` ASC";
					$results = $db->GetAll($query);
					echo "<table class=\"tableList\" cellspacing=\"0\" summary=\"List of Linked Resources\">\n";
					echo "<colgroup>\n";
					echo "	<col class=\"modified\" />\n";
					echo "	<col class=\"title\" />\n";
					echo "	<col class=\"date\" />\n";
					echo "</colgroup>\n";
					echo "<thead>\n";
					echo "	<tr>\n";
					echo "		<td class=\"modified\">&nbsp;</td>\n";
					echo "		<td class=\"title sortedASC\"><div class=\"noLink\">Linked Resource</div></td>\n";
					echo "		<td class=\"date\">Last Updated</td>\n";
					echo "	</tr>\n";
					echo "</thead>\n";
					echo "<tbody>\n";
					if ($results) {
						foreach ($results as $result) {
							echo "<tr>\n";
							echo "	<td class=\"modified\" style=\"vertical-align: top\"><img src=\"".ENTRADA_URL."/images/url".(($result["proxify"] == "1") ? "-proxy" : "").".gif\" width=\"16\" height=\"16\" alt=\"\" /></td>\n";
							echo "	<td class=\"title\" style=\"vertical-align: top; white-space: normal; overflow: visible\">\n";

							if (((!(int) $result["valid_from"]) || ($result["valid_from"] <= time())) && ((!(int) $result["valid_until"]) || ($result["valid_until"] >= time()))) {
								echo "	<a href=\"".ENTRADA_URL."/link-course.php?id=".$result["id"]."\" title=\"Click to visit ".$result["link"]."\" style=\"font-weight:  bold\" target=\"_blank\">".(($result["link_title"] != "") ? html_encode($result["link_title"]) : $result["link"])."</a>\n";
							} else {
								echo "	<span style=\"color: #666666; font-weight: bold\">".(($result["link_title"] != "") ? html_encode($result["link_title"]) : "Untitled Link")."</span>";
							}

							echo "		<div class=\"content-small\" style=\"margin-top: 3px; margin-bottom: 5px\">\n";
							if (((int) $result["valid_from"]) && ($result["valid_from"] > time())) {
								echo "		This link will become accessible <strong>".date(DEFAULT_DATE_FORMAT, $result["valid_from"])."</strong>.<br /><br />";
							} elseif (((int) $result["valid_until"]) && ($result["valid_until"] < time())) {
								echo "		This link was only accessible until <strong>".date(DEFAULT_DATE_FORMAT, $result["valid_until"])."</strong>. Please contact the primary teacher for assistance if required.<br /><br />";
							}

							if (clean_input($result["link_notes"], array("notags", "nows")) != "") {
								echo "		".trim(strip_selected_tags($result["link_notes"], array("font")))."\n";
							}
							echo "		</div>\n";
							echo "	</td>\n";
							echo "	<td class=\"date\" style=\"vertical-align: top\">".(((int) $result["updated_date"]) ? date(DEFAULT_DATE_FORMAT, $result["updated_date"]) : "Unknown")."</td>\n";
							echo "</tr>\n";
						}
					} else {
						echo "<tr>\n";
						echo "	<td colspan=\"2\">\n";
						echo "		<div class=\"content-small\" style=\"margin-top: 3px; margin-bottom: 5px\">There have been no linked resources added to this course.</div>\n";
						echo "	</td>\n";
						echo "</tr>\n";
					}
					echo "</tbody>\n";
					echo "</table>\n";
					?>
				</div>

				<?php
				/**
				 * Sidebar item that will provide the links to the different sections within this page.
				 */
				$sidebar_html  = "<ul class=\"menu\">\n";
				if ($course_details_section) {
					$sidebar_html .= "	<li class=\"link\"><a href=\"#course-details-section\" title=\"Course Details\">Course Details</a></li>\n";
				}
				if ($course_objectives_section) {
					$sidebar_html .= "	<li class=\"link\"><a href=\"#course-objectives-section\" title=\"Course Objectives\">Course Objectives</a></li>\n";
				}
				if ($course_resources_section) {
					$sidebar_html .= "	<li class=\"link\"><a href=\"#course-resources-section\" title=\"Course Resources\">Course Resources</a></li>\n";
				}
				$sidebar_html .= "</ul>\n";

				new_sidebar_item("Page Anchors", $sidebar_html, "page-anchors", "open", "1.9");
			} else {
				$ERROR++;
				$ERRORSTR[] = "You do not have the permissions required to view this course. If you believe that you have received this message in error, please contact a system administrator.";

				echo display_error();
			}
		}
	} else {
		$sidebar_html  = "<form action=\"".ENTRADA_URL."/search\" method=\"get\" style=\"display: inline\">\n";
		$sidebar_html .= "<label for=\"q\" class=\"form-nrequired\">Search the curriculum:</label><br />";
		$sidebar_html .= "<input type=\"text\" id=\"q\" name=\"q\" value=\"\" style=\"width: 95%\" /><br />\n";
		$sidebar_html .= "<span style=\"float: left; padding-top: 7px;\"><a href=\"".ENTRADA_URL."/search\" style=\"font-size: 11px\">Advanced Search</a></span>\n";
		$sidebar_html .= "<span style=\"float: right; padding-top: 4px;\"><input type=\"submit\" class=\"button-sm\" value=\"Search\" /></span>\n";
		$sidebar_html .= "</form>\n";

		new_sidebar_item("Curriculum Search", $sidebar_html, "curriculum-search-bar", "open");
		?>
		<div style="text-align: right">
			<form>
				<div>
					<label for="organisation-quick-select" class="content-small">Organisation:</label>
					<select id="organisation-quick-select" name="organisation-quick-select" style="width: 300px" onchange="window.location='<?php echo ENTRADA_URL; ?>/courses?org='+this.options[this.selectedIndex].value">
					<?php
					foreach ($ORGANISATION_LIST as $organisation_id => $organisation_name) {
						echo "<option value=\"".$organisation_id."\" ".($organisation_id == $ORGANISATION_ID ? 'selected': '').">".$organisation_name."</option>\n";
					}
					?>
					</select>
				</div>
				<div>
					<label for="course-quick-select" class="content-small">Course Quick Select:</label>
					<select id="course-quick-select" name="course-quick-select" style="width: 300px" onchange="window.location='<?php echo ENTRADA_URL; ?>/courses?org=<?php echo $ORGANISATION_ID;?>&id='+this.options[this.selectedIndex].value">
					<option value="">-- Select a Course --</option>
					<?php
					foreach ($COURSE_LIST as $course_id => $course_name) {
						echo "<option value=\"".$course_id."\">".$course_name."</option>\n";
					}
					?>
					</select>
				</div>
			</form>
		</div>
		<?php

		$query	= "SELECT * FROM `curriculum_lu_types` WHERE `curriculum_type_active` = '1' ORDER BY `curriculum_type_order` ASC";
		$terms	= $db->GetAll($query);
		if ($terms) {
			echo "<h2>Course Listing</h2>\n";
			echo "<ol class=\"curriculum-layout\">\n";
			foreach ($terms as $term) {
				$query		= "	SELECT * FROM `courses` 
								WHERE `curriculum_type_id` = ".$db->qstr($term["curriculum_type_id"])." 
								AND `course_active` = '1'
								AND `organisation_id` = ".$db->qstr($ORGANISATION_ID)." 
								ORDER BY `course_code` ASC";
				$courses	= $db->GetAll($query);
				if ($courses) {
					echo "<li><h3>".html_encode($term["curriculum_type_name"])."</h3>\n";
					echo "	<ul class=\"course-list\">\n";
					foreach ($courses as $course) {
						echo "<li><a href=\"".ENTRADA_URL."/courses?id=".$course["course_id"]."\">".html_encode($course["course_code"]." - ".$course["course_name"])."</a></li>\n";
					}
					echo "	</ul>\n";
					echo "</li>\n";
				}
			}
			echo "</ol>\n";
		}
	}
}