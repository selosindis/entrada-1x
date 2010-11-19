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
 * This file contains all of the functions used within Entrada.
 *
 * @author Organisation: Univeristy of Calgary
 * @author Unit: Faculty of Medicine
 * @author Developer: Howard Lu <yhlu@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
*/

function show_log() {
}
/**
 * Function used by public evaluations and admin evaluations index to output the HTML for both the filter
 * controls and current filter status (Showing Events That Include:) box.
 */
function eval_sche_evaluators_filter_controls($module_type = "") {
	global $db, $ENTRADA_ACL, $ORGANISATION_ID;
	if (!isset($ORGANISATION_ID) || !$ORGANISATION_ID) {
		if (isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["evaluations"]["organisation_id"]) && $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["evaluations"]["organisation_id"]) {
			$ORGANISATION_ID = $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["evaluations"]["organisation_id"];
		} else {
			$ORGANISATION_ID = $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"];
			$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["evaluations"]["organisation_id"] = $ORGANISATION_ID;
		}
	}

	/**
	 * Determine whether or not this is being called from the admin section.
	 */
	if ($module_type == "admin") {
		$module_type = "/admin";
	} else {
		$module_type = "";
	}
	?>
	<table id="filterList" style="clear: both; width: 100%" cellspacing="0" cellpadding="0" border="0" summary="Event Filters">
		<tr>
			<td style="width: 53%; vertical-align: top">
				<form action="<?php echo ENTRADA_URL.$module_type; ?>/evaluations/scheduler" method="get" id="filter_edit" name="filter_edit" style="position: relative;">
				<input type="hidden" name="section" value="add" />
				<input type="hidden" name="action" value="filter_edit" />
				<input type="hidden" id="filter_edit_type" name="filter_type" value="" />
				<input type="hidden" id="multifilter" name="filter" value="" />
				<label for="filter_select" class="content-subheading" style="vertical-align: middle">Apply Filter:</label>
				<select id="filter_select" onchange="showMultiSelect();" style="width: 184px; vertical-align: middle">
					<option>Select Filter</option>
					<option value="teacher">Teacher Filters</option>
					<option value="student">Student Filters</option>
					<option value="grad">Graduating Year Filters</option>
					<option value="course">Course Filters</option>
					<option value="phase">Phase / Term Filters</option>
					<option value="eventtype">Event Type Filters</option>
					<option value="clinical_presentation">Clinical Presentation Filters</option>
				</select>
				<?php

				$query = "SELECT `organisation_id`,`organisation_title` FROM `".AUTH_DATABASE."`.`organisations` ORDER BY `organisation_title` ASC";
				$organisation_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
				$organisation_ids_string = "";
				if ($organisation_results) {
					$organisations = array();
					foreach ($organisation_results as $result) {
						if($ENTRADA_ACL->amIAllowed("resourceorganisation".$result["organisation_id"], "read")) {
							if (!$organisation_ids_string) {
								$organisation_ids_string = $db->qstr($result["organisation_id"]);
							} else {
								$organisation_ids_string .= ", ".$db->qstr($result["organisation_id"]);
							}
							if (isset($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["organisation"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["organisation"]) && (in_array($result["organisation_id"], $_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["organisation"]))) {
								$checked = 'checked="checked"';
							} else {
								$checked = '';
							}
							$organisations[$result["organisation_id"]] = array('text' => $result["organisation_title"], 'value' => 'organisation_'.$result["organisation_id"], 'checked' => $checked);
							$organisation_categories[$result["organisation_id"]] = array('text' => $result["organisation_title"], 'value' => 'organisation_'.$result["organisation_id"], 'category'=>true);
						}
					}
				}
				if (!$organisation_ids_string) {
					$organisation_ids_string = $db->qstr($ORGANISATION_ID);
				}

				// Get the possible teacher filters
				$query = "	SELECT a.`id` AS `proxy_id`, a.`organisation_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`
							FROM `".AUTH_DATABASE."`.`user_data` AS a
							LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
							ON b.`user_id` = a.`id`
							LEFT JOIN `event_contacts` AS c
							ON c.`proxy_id` = a.`id`
							WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
							AND a.`organisation_id` IN (".$organisation_ids_string.")
							AND (b.`group` = 'faculty' OR (b.`group` = 'resident' AND b.`role` = 'lecturer'))
							AND c.`econtact_id` IS NOT NULL
							GROUP BY a.`id`
							ORDER BY `fullname` ASC";
				$teacher_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
				if ($teacher_results) {
					$teachers = $organisation_categories;
					foreach ($teacher_results as $r) {
						if (isset($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["teacher"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["teacher"]) && (in_array($r['proxy_id'], $_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]['teacher']))) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}

						$teachers[$r["organisation_id"]]['options'][] = array('text' => $r['fullname'], 'value' => 'teacher_'.$r['proxy_id'], 'checked' => $checked);
					}
					echo lp_multiple_select_popup('teacher', $teachers, array('title'=>'Select Teachers:', 'submit_text'=>'Apply', 'cancel'=>true, 'submit'=>true));
				}

				// Get the possible Student filters
				$query = "	SELECT a.`id` AS `proxy_id`, a.`organisation_id`, b.`role`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`
							FROM `".AUTH_DATABASE."`.`user_data` AS a
							LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
							ON a.`id` = b.`user_id`
							WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
							AND a.`organisation_id` = ".$db->qstr($ORGANISATION_ID)."
							AND b.`account_active` = 'true'
							AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
							AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
							AND b.`group` = 'student'
							AND b.`role` >= ".$db->qstr(((date("Y", time()) + ((date("m", time()) < 7) ?  3 : 4)) - 4)).
							(($_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"] == "student") ? " AND a.`id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]) : "")."
							GROUP BY a.`id`
							ORDER BY b.`role` DESC, a.`lastname` ASC, a.`firstname` ASC";
				$student_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
				if ($student_results) {
					$students = $organisation_categories;
					foreach ($student_results as $r) {
						if (isset($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["student"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["student"]) && (in_array($r['proxy_id'], $_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["student"]))) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						$students[$r["organisation_id"]]['options'][] = array('text' => $r['fullname'], 'value' => 'student_'.$r['proxy_id'], 'checked' => $checked);
					}

					echo lp_multiple_select_popup('student', $students, array('title'=>'Select Students:', 'submit_text'=>'Apply', 'cancel'=>true, 'submit'=>true));
				}

				// Get the possible courses filters
				$query = "	SELECT `course_id`, `course_name`
							FROM `courses`
							WHERE `organisation_id` = ".$db->qstr($ORGANISATION_ID)."
							ORDER BY `course_name` ASC";
				$courses_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
				if ($courses_results) {
					$courses = array();
					foreach ($courses_results as $c) {
						if (isset($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["course"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["course"]) && (in_array($c['course_id'], $_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["course"]))) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}

						$courses[] = array('text' => $c['course_name'], 'value' => 'course_'.$c['course_id'], 'checked' => $checked);
					}

					echo lp_multiple_select_popup('course', $courses, array('title'=>'Select Courses:', 'submit_text'=>'Apply', 'cancel'=>true, 'submit'=>true));
				}

				// Get the possible event type filters
				$query = "SELECT `eventtype_id`, `eventtype_title` FROM `evaluations_lu_eventtypes` WHERE `eventtype_active` = '1' ORDER BY `eventtype_order` ASC";
				$eventtype_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
				if ($eventtype_results) {
					$eventtypes = array();
					foreach ($eventtype_results as $result) {
						if (isset($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["eventtype"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["eventtype"]) && (in_array($result["eventtype_id"], $_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["eventtype"]))) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						$eventtypes[] = array('text' => $result["eventtype_title"], 'value' => 'eventtype_'.$result["eventtype_id"], 'checked' => $checked);
					}

					echo lp_multiple_select_popup('eventtype', $eventtypes, array('title'=>'Select Event Types:', 'submit_text'=>'Apply', 'cancel'=>true, 'submit'=>true));
				}

				$syear		= (date("Y", time()) - 1);
				$eyear		= (date("Y", time()) + 4);
				$gradyears = array();
				for ($year = $syear; $year <= $eyear; $year++) {
					if (isset($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["grad"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["grad"]) && (in_array($year, $_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["grad"]))) {
						$checked = 'checked="checked"';
					} else {
						$checked = '';
					}
					$gradyears[] = array('text' => "Graduating in $year", 'value' => "grad_".$year, 'checked' => $checked);
				}

				echo lp_multiple_select_popup('grad', $gradyears, array('title'=>'Select Gradutating Years:', 'submit_text'=>'Apply', 'cancel'=>true, 'submit'=>true));

				$phases = array(
					array('text'=>'Term 1', 'value'=>'phase_1', 'checked'=>''),
					array('text'=>'Term 2', 'value'=>'phase_2', 'checked'=>''),
					array('text'=>'Term 3', 'value'=>'phase_t3', 'checked'=>''),
					array('text'=>'Phase 2A', 'value'=>'phase_2a', 'checked'=>''),
					array('text'=>'Phase 2B', 'value'=>'phase_2b', 'checked'=>''),
					array('text'=>'Phase 2C', 'value'=>'phase_2c', 'checked'=>''),
					array('text'=>'Phase 2E', 'value'=>'phase_2e', 'checked'=>''),
					array('text'=>'Phase 3', 'value'=>'phase_3', 'checked'=>'')
				);

				for ($i = 0; $i < 6; $i++) {
					if (isset($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["phase"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["phase"])) {
						$pieces = explode('_', $phases[$i]['value']);
						if (in_array($pieces[1], $_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]['phase'])) {
							$phases[$i]['checked'] = 'checked="checked"';
						}
					}
				}

				echo lp_multiple_select_popup('phase', $phases, array('title'=>'Select Phases / Terms:', 'submit_text'=>'Apply', 'cancel'=>true, 'submit'=>true));

				$clinical_presentations = fetch_mcc_objectives();
				foreach ($clinical_presentations as &$clinical_presentation) {
					$clinical_presentation["value"] = "objective_".$clinical_presentation["objective_id"];
					$clinical_presentation["text"] = $clinical_presentation["objective_name"];
					$clinical_presentation["checked"] = "";
					if (isset($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["clinical_presentations"]) && is_array($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["clinical_presentations"])) {
						if (in_array($clinical_presentation["value"], $_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]["clinical_presentations"])) {
							$clinical_presentation["checked"] = "checked=\"checked\"";
						}
					}
				}

				echo lp_multiple_select_popup('clinical_presentation', $clinical_presentations, array('title'=>'Select Clinical Presentations:', 'submit_text'=>'Apply', 'cancel'=>true, 'submit'=>true));
				?>
				</form>
				<script type="text/javascript">
				var multiselect = [];
				var id;
				function showMultiSelect() {
					$$('select_multiple_container').invoke('hide');
					id = $F('filter_select');
					if (multiselect[id]) {
						multiselect[id].container.show();
					} else {
						if ($(id+'_options')) {
							$('filter_edit_type').value = id;
							$(id+'_options').addClassName('multiselect-processed');
							multiselect[id] = new Control.SelectMultiple('multifilter',id+'_options',{
								checkboxSelector: 'table.select_multiple_table tr td input[type=checkbox]',
								nameSelector: 'table.select_multiple_table tr td.select_multiple_name label',
								filter: id+'_select_filter',
								resize: id+'_scroll',
								afterCheck: function(element) {
									var tr = $(element.parentNode.parentNode);
									tr.removeClassName('selected');
									if (element.checked) {
										tr.addClassName('selected');
									}
								}
							});

							$(id+'_cancel').observe('click',function(event){
								this.container.hide();
								$('filter_select').options.selectedIndex = 0;
									$('filter_select').show();
								return false;
							}.bindAsEventListener(multiselect[id]));

							$(id+'_close').observe('click',function(event){
								this.container.hide();
								$('filter_edit').submit();
								return false;
							}.bindAsEventListener(multiselect[id]));
							multiselect[id].container.show();
						}
					}
					return false;
				}
				function setDateValue(field, date) {
					timestamp = getMSFromDate(date);
					if (field.value != timestamp) {
						window.location = '<?php echo ENTRADA_URL.$module_type."/evaluations/scheduler?".(($_SERVER["QUERY_STRING"] != "") ? replace_query(array("dstamp" => false))."&" : ""); ?>dstamp='+timestamp;
					}
					return;
				}
				</script>
			</td>
			<td style="width: 47%; vertical-align: top">
				<?php
				if ((is_array($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"])) && (count($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"]))) {
					echo "<table class=\"inner-content-box\" id=\"filter-list\" cellspacing=\"0\" summary=\"Selected Filter List\">\n";
					echo "<thead>\n";
					echo "	<tr>\n";
					echo "		<td class=\"inner-content-box-head\">Showing Evaluations That Include:</td>\n";
					echo "	</tr>\n";
					echo "</thead>\n";
					echo "<tbody>\n";
					echo "	<tr>\n";
					echo "		<td class=\"inner-content-box-body\">";
					echo "		<div id=\"filter-list-resize-handle\" style=\"margin:0px -6px -6px -7px;\">";
					echo "		<div id=\"filter-list-resize\" style=\"height: 60px; overflow: auto;  padding: 0px 6px 6px 6px;\">\n";
					foreach ($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["filters"] as $filter_type => $filter_contents) {
						if (is_array($filter_contents)) {
							echo 	$filter_name = filter_name($filter_type);
							echo "	<div style=\"margin: 2px 0px 10px 3px\">\n";
							foreach ($filter_contents as $filter_key => $filter_value) {
								echo "	<div id=\"".$filter_type."_".$filter_key."\">";
								echo "		<a href=\"".ENTRADA_URL.$module_type."/evaluations/scheduler?section=add&action=filter_remove&amp;filter=".$filter_type."_".$filter_key."\" title=\"Remove this filter\">";
								echo "		<img src=\"".ENTRADA_URL."/images/checkbox-on.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" />";
								switch ($filter_type) {
									case "teacher" :
									case "student" :
										echo get_account_data("fullname", $filter_value);
									break;
									case "grad" :
										echo "Class of ".$filter_value;
									break;
									case "course" :
										echo course_name($filter_value);
									break;
									case "phase" :
										echo "Phase / Term ".strtoupper($filter_value);
									break;
									case "eventtype" :
										echo fetch_eventtype_title($filter_value);
									break;
									case "organisation":
										echo fetch_organisation_title($filter_value);
									break;
									case "objective":
										echo fetch_objective_title($filter_value);
									break;
									default :
										echo strtoupper($filter_value);
									break;
								}
								echo "		</a>";
								echo "	</div>\n";
							}
							echo "	</div>\n";
						}
					}
					echo "		</div>\n";
					echo "		</div>\n";
					echo "		</td>\n";
					echo "	</tr>\n";
					echo "</tbody>\n";
					echo "</table>\n";
					echo "<br />\n";
					echo "<script type=\"text/javascript\">";
					echo "	new ElementResizer($('filter-list-resize'), {handleElement: $('filter-list-resize-handle'), min: 40});";
					echo "</script>";
				}
				?>
			</td>
		</tr>
	</table>
	<?php
}

?>
