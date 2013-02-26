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
 * Module:	Curriculum Explorer
 * Area:		Public
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2013 Queen's University, MEdTech Unit
 *
 */

if (!defined("PARENT_INCLUDED")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("search", "read")) {
	add_error("Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {

	function fetch_objective_parents($objective_id, $level = 0) {
		global $db, $ENTRADA_USER;
		if ($level >= 99) {
			exit;
		}
		$query = "	SELECT a.`objective_parent`, a.`objective_id`, a.`objective_name`
					FROM `global_lu_objectives` AS a
					JOIN `objective_organisation` AS b
					ON a.`objective_id` = b.`objective_id`
					WHERE a.`objective_id` = ".$db->qstr($objective_id)."
					AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation());
		$objective = $db->GetAssoc($query);
		if ($objective) {
			foreach ($objective as $parent_id => $objective_data)
			if ($parent_id != 0) {
				$objective_data["parent"] = fetch_objective_parents($parent_id, $level++);
			}
			return $objective_data;
		}
	}
	
	$PAGE_META["title"]			= "Curriculum Explorer";
	$PAGE_META["description"]	= "Allowing you to browse the curriculum by objective set, course, and date.";
	$PAGE_META["keywords"]		= "";

	$BREADCRUMB[] = array("url" => ENTRADA_URL."/curriculumexplorer", "title" => "Curriculum Explorer");

	if (isset($_GET["mode"]) && $_GET["mode"] == "ajax") {
		$MODE = "ajax";
	}
	
	if (isset($_GET["id"]) && ($tmp_input = clean_input($_GET["id"], array("int")))) {
		$PROCESSED["id"] = $tmp_input;
	}
	
	if (isset($MODE) && $MODE == "ajax") {
		
		if (!$PROCESSED["id"] && $_GET["objective_parent"] && ($tmp_input = clean_input($_GET["objective_parent"], array("int")))) {
			$PROCESSED["objective_parent"] = $tmp_input;
		} else {
			$PROCESSED["objective_parent"] = $PROCESSED["id"];
		}
		if ($_GET["year"] && ($tmp_input = clean_input($_GET["year"], array("int")))) {
			$PROCESSED["year"] = $tmp_input;
			$SEARCH_DURATION["start"]	= mktime(0, 0, 0, 9, 1, $PROCESSED["year"]);
			$SEARCH_DURATION["end"]		= strtotime("+1 year", $SEARCH_DURATION["start"]);
		}
		if ($_GET["course_id"] && ($tmp_input = clean_input($_GET["course_id"], array("int")))) {
			$PROCESSED["course_id"] = $tmp_input;
		}
		
		ob_clear_open_buffers();
		
		$query = "	SELECT a.`objective_id`, a.`objective_name`, a.`objective_parent`, 
						COUNT(DISTINCT " . ($PROCESSED["course_id"] ? "IF(c.`course_id` = " . $db->qstr($PROCESSED["course_id"]) . ", c.`course_id`, NULL)" : "c.`course_id`" ) . ") AS `course_count`, 
						COUNT(DISTINCT IF(f.`event_id` IS NOT NULL " . ($PROCESSED["course_id"] ? " && f.`course_id` = ".$db->qstr($PROCESSED["course_id"])." " : "") . ", f.`event_id`, NULL)) AS `event_count`
					FROM `global_lu_objectives` AS a 
					JOIN `objective_organisation` AS b
					ON a.`objective_id` = b.`objective_id`

					LEFT JOIN `course_objectives` AS c
					ON a.`objective_id` = c.`objective_id`
					LEFT JOIN `courses` AS d
					ON c.`course_id` = d.`course_id`

					LEFT JOIN `event_objectives` AS e
					ON a.`objective_id` = e.`objective_id`
					LEFT JOIN `events` AS f
					ON e.`event_id` = f.`event_id`

					WHERE a.`objective_parent` = " . $db->qstr($PROCESSED["objective_parent"]) . " 
					AND a.`objective_active` = '1'
					AND b.`organisation_id` = " . $db->qstr($ENTRADA_USER->getActiveOrganisation()).
					($PROCESSED["year"] ? " AND (IF (f.`event_id` IS NOT NULL, f.`event_start` BETWEEN ".$db->qstr($SEARCH_DURATION["start"])." AND ".$db->qstr($SEARCH_DURATION["end"]).", '1' = '1'))" : "")."
					AND (d.`course_active` = '1' OR d.`course_active` IS NULL)
					GROUP BY a.`objective_id`
					ORDER BY a.`objective_id` ASC";
		$child_objectives = $db->GetAll($query);
		
		$query = "	SELECT b.`course_id`, b.`course_name`, `course_code`
					FROM `course_objectives` AS a
					JOIN `courses` As b
					ON a.`course_id` = b.`course_id`
					JOIN `objective_organisation` AS c
					ON a.`objective_id` = c.`objective_id`
					WHERE a.`objective_id` = ".$db->qstr($PROCESSED["objective_parent"])."
					AND b.`course_active` = 1
					AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation()).
					($PROCESSED["course_id"] ? " AND (a.`course_id` = " . $db->qstr($PROCESSED["course_id"]).", '1' = '1'))" : "");
		$mapped_courses = $db->GetAll($query);
		
		$query = "	SELECT c.*, d.`objective_name`, e.`course_code`, e.`course_name`
					FROM `event_objectives` AS a
					JOIN `objective_organisation` AS b
					ON a.`objective_id` = b.`objective_id`
					JOIN `events` AS c
					ON a.`event_id` = c.`event_id`
					JOIN `global_lu_objectives` AS d
					ON a.`objective_id` = d.`objective_id`
					JOIN `courses` AS e
					ON c.`course_id` = e.`course_id`
					WHERE a.`objective_id` = ".$db->qstr($PROCESSED["objective_parent"])." 
					AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation()).
					($PROCESSED["year"] ? " AND (IF (c.`event_id` IS NOT NULL, c.`event_start` BETWEEN ".$db->qstr($SEARCH_DURATION["start"])." AND ".$db->qstr($SEARCH_DURATION["end"]).", '1' = '1'))" : "").
					($PROCESSED["course_id"] ? " AND (IF (c.`course_id` IS NOT NULL, c.`course_id` = " . $db->qstr($PROCESSED["course_id"]).", '1' = '1'))" : "")."
					ORDER BY c.`course_id`, c.`event_start` DESC";
		$event_objectives = $db->GetAll($query);
		
		$objective_parents = fetch_objective_parents($PROCESSED["objective_parent"]);

		
		if ($event_objectives) {
			$objective_name = $event_objectives[0]["objective_name"];
			foreach ($event_objectives as $objective) {
				$events[$objective["course_code"] . ": " . $objective["course_name"]][] = $objective;
			}
		} else {
			echo $db->ErrorMsg();
		}
		
		echo json_encode(array("status" => "success", "objective_parent" => $PROCESSED["objective_parent"], "events" => $events, "courses" => $mapped_courses, "child_objectives" => $child_objectives, "objective_name" => $objective_name, "objective_parents" => $objective_parents));

		exit;
		
	}
	
	switch ($STEP) {
		case 2 :

			/*
			 * Objective Set ID
			 */
			if (isset($_GET["objective_parent"]) && ($tmp_input = clean_input($_GET["objective_parent"], array("int")))) {
				$PROCESSED["objective_parent"] = $tmp_input;
			}
			/*
			 * Course ID
			 */
			if (isset($_GET["course_id"]) && ($tmp_input = clean_input($_GET["course_id"], array("int")))) {
				$PROCESSED["course_id"] = $tmp_input;
			}
			/*
			 * Academic Year
			 */
			if (isset($_GET["year"]) && ($tmp_input = clean_input($_GET["year"], array("int")))) {
				$PROCESSED["year"] = $tmp_input;
				$SEARCH_DURATION["start"]	= mktime(0, 0, 0, 9, 1, $PROCESSED["year"]);
				$SEARCH_DURATION["end"]		= strtotime("+1 year", $SEARCH_DURATION["start"]);
			}
			
			
		break;
		case 1 :
		default :
		break;
	}
	
	?>
	<h1>Curriculum Explorer</h1>
	<form action="<?php echo ENTRADA_URL; ?>/curriculumexplorer" method="GET">
		<input type="hidden" name="step" value="2" />
		<?php
		if ($SEARCH_MODE == "timeline") {
			echo "<input type=\"hidden\" name=\"m\" value=\"timeline\" />\n";
		}
		?>
		<table style="width: 100%" cellspacing="1" cellpadding="1" border="0">
			<colgroup>
				<col style=" width: 20%" />
				<col style=" width: 22%" />
				<col style=" width: 5%" />
				<col style=" width: 20%" />
				<col style=" width: 23%" />
			</colgroup>
			<tbody>
				<tr>
					<td><label for="objective-set" style="font-weight: bold; margin-right: 5px; white-space: nowrap">Objective Set:</label></td>
					<td colspan="4">
						<select id="objective-set" name="objective_parent" style="width: 250px" <?php echo (($SEARCH_MODE == "timeline") ? " disabled=\"disabled\"" : ""); ?>>
							<?php
								$query = "	SELECT a.* FROM `global_lu_objectives` AS a
											LEFT JOIN `objective_organisation` AS b
											ON a.`objective_id` = b.`objective_id`
											JOIN `objective_audience` AS c
											ON a.`objective_id` = c.`objective_id`
											WHERE a.`objective_parent` = '0' 
											AND a.`objective_active` = '1' 
											AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
											AND c.`audience_value` = 'all'
											ORDER BY a.`objective_order` ASC";
								$objective_sets = $db->GetAssoc($query);
								if ($objective_sets) {
									foreach ($objective_sets as $objective_id => $objective_set) {
										?>
							<option value="<?php echo $objective_id; ?>" <?php echo ($objective_id == $PROCESSED["objective_parent"]) ? " selected=\"selected\"" : "" ; ?>><?php echo (!empty($objective_set["objective_code"]) ? $objective_set["objective_code"] . " - " : "") . $objective_set["objective_name"]; ?></option>
										<?php
									}
								}
							?>
						</select>
					</td>
				<tr>
					<td><label for="course" style="font-weight: bold; margin-right: 5px; white-space: nowrap">Course:</label></td>
					<td>
						<select id="course" name="course_id" style="width: 250px" <?php echo (($SEARCH_MODE == "timeline") ? " disabled=\"disabled\"" : ""); ?>>
							<option value="0">-- All Courses --</option>
							<?php
							$query = "	SELECT * FROM `courses`
										WHERE `organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
										AND `course_active` = '1'
										ORDER BY `course_code` ASC";
							$courses = $db->GetAll($query);
							if ($courses) {
								foreach ($courses as $course) {
									?>
							<option value="<?php echo $course["course_id"]; ?>" <?php echo ($course["course_id"] == $PROCESSED["course_id"]) ? " selected=\"selected\"" : "" ; ?>><?php echo (!empty($course["course_code"]) ? $course["course_code"] . " - " : "") . $course["course_name"]; ?></option>
									<?php
								}
							}
							?>
						</select>
					</td>
					<td>&nbsp;</td>
					<td><label for="year" style="font-weight: bold; margin-right: 5px; white-space: nowrap">Academic Year:</label></td>
					<td>
						<select id="year" name="year" style="width: 250px" <?php echo (($SEARCH_MODE == "timeline") ? " disabled=\"disabled\"" : ""); ?>>
							<option value="0"<?php echo ((!$SEARCH_YEAR)? " selected=\"selected\"" : ""); ?>>-- All Years --</option>
							<?php
							$start_year = (fetch_first_year() - 3);
							for ($year = $start_year; $year >= ($start_year - 3); $year--) { ?>
								<option value="<?php echo $year; ?>"  <?php echo ($year == $PROCESSED["year"]) ? " selected=\"selected\"" : "" ; ?>><?php echo $year ."/" . ($year + 1); ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="5" align="right"><input type="submit" value="Browse" /></td>
				</tr>
			</tbody>
		</table>
	</form>
	<br />
	<script type="text/javascript">
	var SITE_URL = "<?php echo ENTRADA_URL; ?>";
	var YEAR = "<?php echo $PROCESSED["year"]; ?>";
	var COURSE = "<?php echo $PROCESSED["course_id"]; ?>";
	var OBJECTIVE_PARENT = "<?php echo $PROCESSED["objective_parent"]; ?>";
	</script>
	<script type="text/javascript" src="<?php echo ENTRADA_URL; ?>/javascript/curriculumexplorer.js" /></script>
	<?php if ($PROCESSED["id"]) { ?>
	<script type="text/javascript">
	jQuery(function(){
		var id = <?php echo $PROCESSED["id"]; ?>;
		jQuery.getJSON("<?php echo ENTRADA_URL; ?>/curriculumexplorer?mode=ajax&id="+id, function(data) {
			var link = jQuery(document.createElement("a")).addClass(".objective-link").attr("data-id", "<?php echo $PROCESSED["id"]; ?>").html(data.objective_name);
			renderDOM(data, link);
			if (jQuery(".objective-link[data-id="+id+"]").length > 0) {
				jQuery(".objective-link[data-id="+id+"]").addClass("active");
			}
		});
	});
	</script>
	<?php } ?>
	<?php 
		switch ($STEP) {
			case 2 :
				/*
				 * Fetch the child objectives of the selected objective set.
				 */
				$query = "	SELECT a.`objective_id`, a.`objective_name`, a.`objective_parent`, 
								COUNT(DISTINCT " . ($PROCESSED["course_id"] ? "IF(c.`course_id` = " . $db->qstr($PROCESSED["course_id"]) . ", c.`course_id`, NULL)" : "c.`course_id`" ) . ") AS `course_count`, 
								COUNT(DISTINCT IF(f.`event_id` IS NOT NULL " . ($PROCESSED["course_id"] ? " && f.`course_id` = ".$db->qstr($PROCESSED["course_id"])." " : "") . ", f.`event_id`, NULL)) AS `event_count`
							FROM `global_lu_objectives` AS a 
							JOIN `objective_organisation` AS b
							ON a.`objective_id` = b.`objective_id`

							LEFT JOIN `course_objectives` AS c
							ON a.`objective_id` = c.`objective_id`
							LEFT JOIN `courses` AS d
							ON c.`course_id` = d.`course_id`

							LEFT JOIN `event_objectives` AS e
							ON a.`objective_id` = e.`objective_id`
							LEFT JOIN `events` AS f
							ON e.`event_id` = f.`event_id`

							WHERE a.`objective_parent` = " . $db->qstr($PROCESSED["objective_parent"]) . " 
							AND a.`objective_active` = '1'
							AND b.`organisation_id` = " . $db->qstr($ENTRADA_USER->getActiveOrganisation()).
							($PROCESSED["year"] ? " AND (IF (f.`event_id` IS NOT NULL, f.`event_start` BETWEEN ".$db->qstr($SEARCH_DURATION["start"])." AND ".$db->qstr($SEARCH_DURATION["end"]).", '1' = '1'))" : "")."
							AND (d.`course_active` = '1' OR d.`course_active` IS NULL)
							GROUP BY a.`objective_id`
							ORDER BY a.`objective_id` ASC";
				$objectives = $db->GetAssoc($query);
				if ($objectives) {
			?>
			<div id="objective-browser">
				<div id="objective-breadcrumb">
					<a class="objective-link" href="#" data-id="<?php echo $PROCESSED["objective_parent"]; ?>"><?php echo $objective_sets[$PROCESSED["objective_parent"]]["objective_name"]; ?></a>
				</div>
				<div id="objective-list">
					<ul>
						<?php
						foreach ($objectives as $objective_id => $objective) {
							$count = "";
							if (!$PROCESSED["course_id"]) {
								$count = $objective["course_count"] + $objective["event_count"];
							} else {
								$count = $objective["event_count"];
							}
							
							$class= "";
							if ($count < 5) {
								$class = "red";
							} else if ($count < 10) {
								$class = "yellow";
							} else {
								$class = "green";
							}
							echo "<li><span class=\"". $class ."\">".(($count < 10 ? "0" . $count : $count))."</span><a class=\"objective-link\" href=\"#\" data-id=\"" . $objective_id . "\">" . $objective["objective_name"] . "</a></li>\n";
						}
						?>
					</ul>
				</div>
				<div id="objective-container">
					
					<div id="objective-details">
						<h1><?php echo $objective_sets[$PROCESSED["objective_parent"]]["objective_name"]; ?></h1>
						<div class="display-generic">Please select a objective from the list on the left.</div>
					</div>
				</div>
			</div>
			<?php
				} else {
					echo "<pre>".$query."</pre>";
				}
			break;
		}
}