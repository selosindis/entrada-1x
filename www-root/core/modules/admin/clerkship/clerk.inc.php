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
 * @version $Id: $
 */

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_CLERKSHIP"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('electives', 'update')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/clerkship?", "title" => "Clerk Management");
	
	$HEAD[] = "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
	$HEAD[] = "<style type=\"text/css\">.dynamic-tab-pane-control .tab-page { height:auto; }</style>\n";

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	if (isset($_GET["ids"]) && $PROXY_ID = clean_input($_GET["ids"], "int")) {
		$student_name	= get_account_data("firstlast", $PROXY_ID);
		$BREADCRUMB[]	= array("url" => ENTRADA_URL."/clerkship/clerk?ids=".$PROXY_ID, "title" => $student_name);
		
		/**
		 * Process local page actions.
		 */
		$query		= "	SELECT a.*, c.*
						FROM `".CLERKSHIP_DATABASE."`.`events` AS a
						LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
						ON b.`event_id` = a.`event_id`
						LEFT JOIN `".CLERKSHIP_DATABASE."`.`regions` AS c
						ON c.`region_id` = a.`region_id`
						WHERE b.`econtact_type` = 'student'
						AND b.`etype_id` = ".$db->qstr($PROXY_ID)."
						ORDER BY a.`event_start` ASC";
		$results	= $db->GetAll($query);
		if($results) {
			$elective_weeks = clerkship_get_elective_weeks($PROXY_ID);
			$remaining_weeks = (int)$CLERKSHIP_REQUIRED_WEEKS - (int)$elective_weeks["approved"];
			
			$sidebar_html  = "<ul class=\"menu\">\n";
			$sidebar_html .= "	<li><strong>".$elective_weeks["approval"]."</strong> Pending Approval</li>\n";
			$sidebar_html .= "	<li class=\"checkmark\"><strong>".$elective_weeks["approved"]."</strong> Weeks Approved</li>\n";
			$sidebar_html .= "	<li class=\"incorrect\"><strong>".$elective_weeks["trash"]."</strong> Weeks Rejected</li>\n";
			$sidebar_html .= "	<br />";
			if((int)$elective_weeks["approval"] + (int)$elective_weeks["approved"] > 0) {
				$sidebar_html .= "	<li><a target=\"blank\" href=\"".ENTRADA_URL."/admin/clerkship/electives?section=disciplines&id=".$PROXY_ID."\">Discipline Breakdown</a></li>\n";
			}
			$sidebar_html .= "</ul>\n";
		
			$sidebar_html .= "<div style=\"margin-top: 10px\">\n";
			$sidebar_html .= $student_name. " has ".$remaining_weeks." required elective week".(($remaining_weeks != 1) ? "s" : "")." remaining.\n";
			$sidebar_html .= "</div>\n";
		
			new_sidebar_item("Elective Weeks", $sidebar_html, "page-clerkship", "open");
			echo "<h1>".$student_name."</h1>\n";
			?>
		<div class="tab-pane" id="clerk-tabs">
			<div class="tab-page" id="schedule">
				<h2 class="tab">Clerkship Schedule</h2>
				<div style="float: right; padding-top: 8px">
				    <div id="module-content">
				        <ul class="page-action">
				            <li>
				                <a href = "<?php echo ENTRADA_URL."/admin/clerkship/electives?section=add_elective&ids=".$PROXY_ID;?>" class="strong-green">Add Elective</a>
				            </li>
				        </ul>
				    </div>
				</div>
				<div style="float: right; padding-top: 8px">
				    <div id="module-content">
				        <ul class="page-action">
				            <li>
				                <a href = "<?php echo ENTRADA_URL."/admin/clerkship/electives?section=add_core&ids=".$PROXY_ID;?>" class="strong-green">Add Core</a>
				            </li>
				        </ul>
				    </div>
				</div>
				<table class="tableList" cellspacing="0" summary="List of Clerkship Schedule">
				<colgroup>
					<col class="modified" />
					<col class="type" />
					<col class="date" />
					<col class="date" />
					<col class="region" />
					<col class="title" />
				</colgroup>
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="type">Event Type</td>
						<td class="date-smallest">Start Date</td>
						<td class="date-smallest">Finish Date</td>
						<td class="region">Region</td>
						<td class="title">Category Title</td>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ($results as $result) {
					if ((time() >= $result["event_start"]) && (time() <= $result["event_finish"])) {
						$bgcolour	= "#E7ECF4";
						$is_here	= true;
					} else {
						$bgcolour	= "#FFFFFF";
						$is_here	= false;
					}
		
					if ((bool) $result["manage_apartments"]) {
						$aschedule_id = regionaled_apartment_check($result["event_id"], $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]);
						$apartment_available = (($aschedule_id) ? true : false);
					} else {
						$apartment_available = false;
					}
		
					if ($apartment_available) {
						$click_url = ENTRADA_URL."/clerkship?section=details&id=".$result["event_id"];
					} else {
						$click_url = "";
					}
		
					if (!isset($result["region_name"]) || $result["region_name"] == "") {
						$result_region = clerkship_get_elective_location($result["event_id"]);
						$result["region_name"] = $result_region["region_name"];
						$result["city"]		   = $result_region["city"];
					} else {
						$result["city"] = "";
					}
					
					$event_title = clean_input($result["event_title"], array("htmlbrackets", "trim"));
					
					$cssclass 	= "";
					$skip		= false;
		
					if ($result["event_type"] == "elective") {
						switch ($result["event_status"]) {
							case "approval":
								$elective_word = "Pending";
								$cssclass 	= " class=\"in_draft\"";
								$click_url 	= ENTRADA_URL."/admin/clerkship/electives?action=edit&id=".$result["event_id"];
								$skip		= false;
							break;
							case "published":
								$elective_word = "Approved";
								$cssclass 	= " class=\"published\"";
								$click_url 	= ENTRADA_URL."/admin/clerkship/electives?section=edit&id=".$result["event_id"];
								$skip		= false;
							break;
							case "trash":
								$elective_word = "Rejected";
								$cssclass 	= " class=\"rejected\"";
								$click_url 	= ENTRADA_URL."/admin/clerkship/electives?section=edit&id=".$result["event_id"];
								$skip		= true;
							break;
							default:
								$elective_word = "";
								$cssclass = "";
							break;
						}
						
						$elective	= true;					
					} else {
						$elective	= false;
						$skip		= false;
					}
					if (!$click_url) {
						$click_url 	= ENTRADA_URL."/admin/clerkship/electives?section=edit&id=".$result["event_id"];
					}
					if (!$skip) {
						echo "<tr".(($is_here) && $cssclass != " class=\"in_draft\"" ? " class=\"current\"" : $cssclass).">\n";
						echo "	<td class=\"modified\"><a href=\"".$click_url."\" style=\"font-size: 11px\"><img src=\"".ENTRADA_URL."/images/".(($apartment_available) ? "housing-icon-small.gif" : "pixel.gif")."\" width=\"16\" height=\"16\" alt=\"".(($apartment_available) ? "Detailed apartment information available." : "")."\" title=\"".(($apartment_available) ? "Detailed apartment information available." : "")."\" style=\"border: 0px\" /></a></td>\n";
						echo "	<td class=\"type\"><a href=\"".$click_url."\" style=\"font-size: 11px\">".(($elective) ? "Elective".(($elective_word != "") ? " (".$elective_word.")" : "") : "Core Rotation")."</a>"."</td>\n";
						echo "	<td class=\"date-smallest\"><a href=\"".$click_url."\" style=\"font-size: 11px\">".date("D M d/y", $result["event_start"])."</a></td>\n";
						echo "	<td class=\"date-smallest\"><a href=\"".$click_url."\" style=\"font-size: 11px\">".date("D M d/y", $result["event_finish"])."</a></td>\n";
						echo "	<td class=\"region\"><a href=\"".$click_url."\" style=\"font-size: 11px\">".html_encode((($result["city"] == "") ? limit_chars(($result["region_name"]), 30) : $result["city"]))."</a></td>\n";
						echo "	<td class=\"title\">";
						echo "		<a href=\"".$click_url."\" style=\"font-size: 11px\"><span title=\"".$event_title."\">".limit_chars(html_decode($event_title), 55)."</span></a>";
						echo "	</td>\n";
						echo "</tr>\n";
					}
				}
				?>
				</tbody>
				</table>
			</div>
			<div class="tab-page" id="encounters">
				<h2 class="tab">Logged Encounters</h2>
				<?php

				if (isset($_GET["ids"]) && ((int)$_GET["ids"])) {
					$PROXY_ID = $_GET["ids"];
				}
				
				if(isset($_GET["sb"])) {
					if(in_array(trim($_GET["sb"]), array("rotation" , "location", "site", "patient", "date", "age"))) {
						if (trim($_GET["sb"]) == "rotation") {
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"]	= "e.`rotation_title`";
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "e.`rotation_title`";
						} elseif (trim($_GET["sb"]) == "location") {
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"]	= "b.`location`";
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "b.`location`";
						} elseif (trim($_GET["sb"]) == "site") {
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"]	= "c.`site_name`";
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "c.`site_name`";
						} elseif (trim($_GET["sb"]) == "patient") {
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"]	= "a.`patient_info`";
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "a.`patient_info`";
						} elseif (trim($_GET["sb"]) == "date") {
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"]	= "a.`encounter_date`";
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "a.`encounter_date`";
						} elseif (trim($_GET["sb"]) == "age") {
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"]	= "f.`age`";
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "f.`agerange_id`";
						}
					}
				} else {
					if(!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"])) {
						$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"] = "e.`rotation_title`";
						$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "e.`rotation_title`";
					}
					$_GET["sb"] = "rotation";
				}
				
				if(!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) || !in_array(trim($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]), array("e.`rotation_title`" , "b.`location`", "c.`site_name`", "a.`patient_info`", "a.`encounter_date`", "f.`agerange_id`")) || !isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"]) || !in_array(trim($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"]), array("e.`rotation_title`" , "b.`location`", "c.`site_name`", "a.`patient_info`", "a.`encounter_date`", "f.`age`"))) {
					$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "e.`rotation_title`";
					$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"] = "e.`rotation_title`";
					$_GET["sb"] = "rotation";
				}
				$query = "	SELECT `rotation_title`, `rotation_id`
							FROM `".CLERKSHIP_DATABASE."`.`global_lu_rotations`";
				$rotations = $db->GetAll($query);
				$rotation_names = Array();
				if ($rotations) {
					foreach ($rotations as $rotation) {
						$rotation_names[$rotation["rotation_id"]] = $rotation["rotation_title"];
					}
				}
				
				$clerk_name = $db->GetOne("	SELECT CONCAT_WS(' ', `firstname`, `lastname`) as `fullname` 
											FROM `".AUTH_DATABASE."`.`user_data`
											WHERE `id` = ".$db->qstr($PROXY_ID));
				
				if (isset($rotation_name) && $rotation_name) {
					echo "<h2>For ".$rotation_name." Rotation</h2>";
				}
			
				$query = "	SELECT ".$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]." AS `sort_by`, a.`lentry_id`, d.`rotation_id`, a.`entry_active`
							FROM `".CLERKSHIP_DATABASE."`.`logbook_entries` AS a 
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_lu_locations` AS b
							ON a.`llocation_id` = b.`llocation_id`
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_lu_sites` AS c
							ON a.`lsite_id` = c.`lsite_id`
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`events` AS d
							ON a.`rotation_id` = d.`event_id`
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`global_lu_rotations` AS e
							ON d.`rotation_id` = e.`rotation_id`
							WHERE a.`proxy_id` = ".$db->qstr($PROXY_ID)."
							ORDER BY ".$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]." ASC";
				$results = $db->GetAll($query);
				if ($results) {
					$rotation_ids = Array();
					foreach ($results as $result) {
						if (array_search($result["rotation_id"], $rotation_ids) === false) {
							$rotation_ids[] = $result["rotation_id"];
						}
					}
					
					$accessible_rotation_ids = clerkship_rotations_access();
					if (is_array($accessible_rotation_ids) && count($accessible_rotation_ids)) {
						$allow_view = true;
					} else {
						$allow_view = false;
					}
					
					if ($allow_view) {
						?>
						<script type="text/javascript" />
							function loadEntry (entry_id) {
								new Ajax.Updater({ success: 'entry' }, '<?php echo ENTRADA_RELATIVE; ?>/clerkship/logbook?section=entryapi&id='+entry_id, {
									onCreate: function () {
										$('entry').innerHTML = '<br/><br/><span class="content-small" style="align: center;">Loading... <img src="<?php echo ENTRADA_URL; ?>/images/indicator.gif" style="vertical-align: middle;" /></span>';
									}
								});
								if ($('entry-line-'+entry_id).hasClassName('flagged') == false) {
									$("current-entry").value = entry_id;
								} else {
									$("current-entry").value = 0;
								}
								$$('.selected-entry').each(function (e) { e.removeClassName('selected-entry'); });
								$('entry-'+entry_id).addClassName('selected-entry');
							}
							function lastPage() {
								if (Number($('current-page').value) > 1) {
									$('page-' + $('current-page').value).hide();
									$('current-page').value = Number($('current-page').value) - 1;
									$('page-' + $('current-page').value).show();
								}
							}
							function nextPage() {
								if (Number($('current-page').value) < $('max-page').value) {
									$('page-' + $('current-page').value).hide();
									$('current-page').value = Number($('current-page').value) + 1;
									$('page-' + $('current-page').value).show();
								}
							}
						</script>
						<input id="current-entry" type="hidden" value="0" />
						<div style="clear: both"></div>
						<table style="width: 100%;">
							<colgroup>
							    <col style="width: 25%" />
							    <col style="width: 75%" />
							</colgroup>
							<tbody>
								<tr>
									<td>
										<label for="view-type">View Encounters By: </label>
									</td>
									<td>
										<select name="view-type" id="view-type" onchange="window.location = '<?php echo ENTRADA_URL."/admin/clerkship/clerk?".replace_query(array("sb" => false)); ?>&sb='+this.options[this.selectedIndex].value;">
											<option value="rotation"<?php echo (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) && $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "e.`rotation_title`" ? " selected=\"selected\"" : "")?>>Rotation</option>
											<option value="date"<?php echo (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) && $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "a.`encounter_date`" ? " selected=\"selected\"" : "")?>>Encounter Date</option>
											<option value="location"<?php echo (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) && $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "b.`location`" ? " selected=\"selected\"" : "")?>>Setting</option>
											<option value="site"<?php echo (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) && $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "c.`site_name`" ? " selected=\"selected\"" : "")?>>Institution</option>
											<option value="patient"<?php echo (isset($_GET["sb"]) && $_GET["sb"] == "patient" ? " selected=\"selected\"" : "")?>>Patient</option>
										</select>
									</td>
								</tr>
							</tbody>
						</table>
						<br/>
						<div style="width: 100%;">
							<div style="width: 25%; float: left; height: 600px;">
								<h3 style="float: left;">Encounters</h3>
								<?php 
								if (count($results) > 20) {
									echo "<div id=\"pagination-links\" style=\"float: left; margin-left: 30px;\">\n";
									echo "<a href=\"javascript:lastPage()\">&lt;&lt;</a> ";
									echo "<a href=\"javascript:nextPage()\">&gt;&gt;</a>";
									echo "</div>";
								}
								?>
								<div style="clear: both"></div>
								<input type="hidden" value="1" id="current-page" />
								<ul class="encounter-list" id="page-1">
									<?php 
									$count = 0;
									$page_count = 1;
									foreach ($results as $result) {
										$count++;
										if ($count > 20) {
											$count = 1;
											$page_count++;
											echo "</ul>\n";
											echo "<ul class=\"encounter-list\" style=\"display: none;\" id=\"page-".$page_count."\">\n";
										}
										?>
										<li id="entry-line-<?php echo $result["lentry_id"]; ?>" class="logbook-entry<?php echo (!$result["entry_active"] ? " flagged" : ""); ?>">
											<a id="entry-<?php echo $result["lentry_id"]; ?>" onclick="loadEntry(<?php echo $result["lentry_id"]; ?>)" class="logbook-entry">
												<?php
												if (in_array($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"], Array("b.`location`", "c.`site_name`", "a.`patient_info`", "e.`rotation_title`"))) {
													echo ($result["sort_by"] ? $result["sort_by"] : "No ".ucfirst($_GET["sb"])." Set");
												} elseif ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "a.`encounter_date`") {
													echo date(DEFAULT_DATE_FORMAT, $result["sort_by"]);
												}
												?>
											</a>
										</li>
										<?php
									}
									?>
								</ul>
								<input type="hidden" value="<?php echo $page_count; ?>" id="max-page" />
							</div>
							<div style="width: 70%; border: 1px solid #CCCCCC; float: left; height: 600px; overflow: auto; padding: 5px 10px 0px 10px;" id="entry">
								<div class="display-notice">Select an entry on the left to view the encounter details here.</div>
							</div>
						</div>
						<div style="clear: both"></div>
						<div style="width: 98%; text-align: right; margin-top: 10px;">
						<?php
						if ($allow_view) {
							?>
							<input type="button" value="Download All" onclick="window.location = '<?php echo ENTRADA_URL."/clerkship/logbook?section=csv&id=".$PROXY_ID;?>'" />
							<?php 
						}
						if ($allow_view) {
							?>
							<input style="margin-left: 20px;" type="button" value="Deactivate Entry" onclick="if ($('current-entry').value != '0') {window.location = '<?php echo ENTRADA_URL."/clerkship/logbook?section=flag&entry_id="?>'+$('current-entry').value;} else {alert('You must select an active entry before selecting the Deactivate Entry button.');}" />
							<?php 
						}
						?>
						</div>
						<?php
						$sidebar_html  = "<div style=\"margin: 2px 0px 10px 3px; font-size: 10px\">\n";
						$sidebar_html .= "	<div><img src=\"".ENTRADA_URL."/images/legend-active-member.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> Active Entry</div>\n";
						$sidebar_html .= "	<div><img src=\"".ENTRADA_URL."/images/legend-not-accessible.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> Deactivated Entry</div>\n";
						$sidebar_html .= "</div>\n";
						
						new_sidebar_item("Logbook Encounters", $sidebar_html, "objective-legend", "open");
						
					} else {
						$ERROR++;
						$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";
						echo display_error();
					}
				} else {
					$ERROR++;
					if (array_key_exists($rotation_id, $rotation_names) && $rotation_names[$rotation_id]) {
						$ERRORSTR[]	= "No clerkship logbook entries for this rotation [".$rotation_names[$rotation_id]."] have been found for this user in the system. You may view all entries for all rotations by clicking <a href=\"".ENTRADA_URL."/admin/clerkship/clerk?".replace_query(array("rotation" => false))."\" />here</a>.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";
					} else {
						$ERRORSTR[]	= "No clerkship logbook entries have been found for this user in the system.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";
					}
					echo display_error();
				}
				?>
				</div>
				<div class="tab-page" id="progress">
					<h2 class="tab">Progress Report</h2>
					<?php
					$query = "	SELECT DISTINCT(b.`rotation_id`), c.`rotation_title` FROM
								`".CLERKSHIP_DATABASE."`.`event_contacts` AS a
								LEFT JOIN `".CLERKSHIP_DATABASE."`.`events` AS b
								ON a.`event_id` = b.`event_id`
								LEFT JOIN `".CLERKSHIP_DATABASE."`.`global_lu_rotations` AS c
								ON b.`rotation_id` = c.`rotation_id`
								WHERE a.`etype_id` = ".$db->qstr($PROXY_ID)."
								AND a.`econtact_type` = 'student'
								AND b.`event_start` < ".$db->qstr(time())."
								ORDER BY `event_start` ASC";
					$rotations = $db->GetAll($query);
					?>
					<div style="clear: both"></div>
					<?php 
					$summary_shown = false;
					if ($rotations) {
						?>
						<form action="<?php echo ENTRADA_URL ?>/admin/clerkship/flag" method="post">
							<table class="tableList" cellspacing="0" summary="Clerkship Progress Summary">
								<colgroup>
									<col class="modified" />
									<col class="region" />
									<col class="date" />
									<col class="date" />
									<col class="date" />
									<col class="date" />
								</colgroup>
								<thead>
									<tr>
										<td class="modified">&nbsp;</td>
										<td class="region-large">Rotation</td>
										<td class="date-smallest">Objectives Logged</td>
										<td class="date-smallest">Objectives Required</td>
										<td class="date-smallest">Procedures Logged</td>
										<td class="date-smallest">Procedures Required</td>
									</tr>
								</thead>
								<tbody>									
								<?php
								foreach ($rotations as $rotation) {
									if ($rotation["rotation_id"]) {
										$procedures_required = 0;
									    $objectives_required = 0;
									    $objectives_recorded = 0;
									    $procedures_recorded = 0;
									    
										$query = "	SELECT `objective_id`, MAX(`number_required`) AS `required`
													FROM `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives`
													WHERE `rotation_id` = ".$db->qstr($rotation["rotation_id"])."
													GROUP BY `objective_id`";
										$required_objectives = $db->GetAll($query);
										if ($required_objectives) {
											foreach ($required_objectives as $required_objective) {
												$objectives_required += $required_objective["required"];
												$query = "	SELECT COUNT(`objective_id`) AS `recorded`
															FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives`
															WHERE `lentry_id` IN
															(
																SELECT `lentry_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_entries`
																WHERE `entry_active` = '1' 
																AND `proxy_id` = ".$db->qstr($PROXY_ID)."
															)
															AND `objective_id` = ".$db->qstr($required_objective["objective_id"])."
															GROUP BY `objective_id`";
												$recorded = $db->GetOne($query);
												
												if ($recorded) {
													$objectives_recorded += ($recorded <= $required_objective["required"] ? $recorded : $required_objective["required"]);
												}
											}
										}
										$query = "	SELECT `lprocedure_id`, MAX(`number_required`) AS `required`
													FROM `".CLERKSHIP_DATABASE."`.`logbook_preferred_procedures`
													WHERE `rotation_id` = ".$db->qstr($rotation["rotation_id"])."
													GROUP BY `lprocedure_id`";
										$required_procedures = $db->GetAll($query);
										if ($required_procedures) {
											foreach ($required_procedures as $required_procedure) {
												$procedures_required += $required_procedure["required"];
												$query = "SELECT COUNT(`lprocedure_id`) AS `recorded`
														FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_procedures`
														WHERE `lentry_id` IN
														(
															SELECT `lentry_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_entries`
															WHERE `entry_active` = '1' 
															AND `proxy_id` = ".$db->qstr($PROXY_ID)."
														)
														AND `lprocedure_id` = ".$db->qstr($required_procedure["lprocedure_id"])."
														GROUP BY `lprocedure_id`";
												$recorded = $db->GetOne($query);
												
												if ($recorded) {
													$procedures_recorded += ($recorded <= $required_procedure["required"] ? $recorded : $required_procedure["required"]);
												}
											}
										}
										$url = ENTRADA_URL."/clerkship/logbook?section=view&type=missing&core=".$rotation["rotation_id"]."&id=".$PROXY_ID;
										$summary_shown = true;
										?>
										<tr class="entry-log">
											<td class="modified">&nbsp;</td>
											<td class="region-large"><a href="<?php echo $url."\">".$rotation["rotation_title"]; ?></a></td>
											<td class="date-smallest"><a href="<?php echo $url."\">".$objectives_recorded; ?></a></td>
											<td class="date-smallest"><a href="<?php echo $url."\">".$objectives_required; ?></a></td>
											<td class="date-smallest"><a href="<?php echo $url."\">".$procedures_recorded; ?></a></td>
											<td class="date-smallest"><a href="<?php echo $url."\">".$procedures_required; ?></a></td>
										</tr>
										<?php
									}
								}
								?>		
								</tbody>
							</table>
						</form>
						<?php
					}
					if (!$summary_shown) {
						$NOTICE++;
						$NOTICESTR[] = $student_name . " has not begun any core rotations in the system at this time.";
						echo display_notice();
					}
				} else {
					$NOTICE++;
					$NOTICESTR[] = $student_name . " has no scheduled clerkship rotations / electives in the system at this time.  Click <a href = ".ENTRADA_URL."/admin/clerkship/electives?section=add_core&ids=".$PROXY_ID." class=\"strong-green\">here</a> to add a new core rotation.";
		
					echo display_notice();
				}
				?>
			</div>
		</div>
		<script type="text/javascript">
		setupAllTabs(true);
		</script>
		<?php
	} else {
		$ERROR++;
		$ERRORSTR[] = "You must provide a valid <strong>User ID</strong> to view.";
		$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE.DIRECTORY_SEPARATOR.$SECTION."\\'', 15000)";

		echo display_error();
	}
}
?>
