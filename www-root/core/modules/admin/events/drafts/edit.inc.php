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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVENTS"))) {
	exit;
} else if ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
		header("Location: ".ENTRADA_URL);
		exit;
} else if (!$ENTRADA_ACL->amIAllowed('eventcontent', 'update', false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else { 
	
	if (isset($_GET["mode"]) && $temp = (clean_input($_GET["mode"], array("trim", "striptags")))) {
		ob_clear_open_buffers();
		switch ($temp) {
			case "csv-example" :
				
				$csv_content  = "Event ID,Term,Course Code,Course Name,Date,Start Time,Event Type Durations,Event Types,Event Title,Location,Audience (Cohorts),Audience (Students),Teacher Numbers\n";
				$csv_content .= ",Clerkship,CLERKSHIP,Clerkship,12-06-05,13:30,60,Other,CaRMS Presentation,New Medical Building,Class of 2013,,\n";
				$csv_content .= ",Clerkship,,Clerkship: Family Medicine,12-06-06,0:00,60,Review / Feedback Session,Title,,,6109994; 6110117; 6141374; 6110289; 6141436; 6111491; 5291303; 5277000; 4905480; 6111858,\n";
				$csv_content .= "1113,Clerkship,MEDS446,Clerkship: Family Medicine,12-06-07,10:30,120,Review / Feedback Session,FM Clerkship Interviews Block 3,Teleconference,,6109994; 6110117; 6141374; 6110289; 6141436; 6111491; 5291303; 5277000; 4905480; 6111858,7404733\n";
				
				header('Pragma: public');
				header('Content-type: text/csv');
				header('Content-Disposition: attachment; filename="draft-schedule-example.csv"');
				
				echo $csv_content;				
				
			break;
			default :
			continue;
		}
		exit;
	}
	
	$BREADCRUMB[]	= array("url" => "", "title" => "Edit Draft Schedule");
	$draft_id = (int) $_GET["draft_id"];
	
	/**
	* Load the rich text editor.
	*/
	load_rte();
	// Error Checking
	switch ($STEP) {
		case 2 :
			/**
			* Required field "draft_name" / Draft Title.
			*/
			if ((isset($_POST["draft_name"])) && ($tmp_input = clean_input($_POST["draft_name"], array("notags", "trim")))) {
				$PROCESSED["name"] = $tmp_input;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Draft Title</strong> field is required.";
			}

			/**
			* Non-Required field "draft_description" / Draft Description.
			*/
			if ((isset($_POST["draft_description"])) && ($tmp_input = clean_input($_POST["draft_description"], array("trim", "allowedtags")))) {
				$PROCESSED["description"] = $tmp_input;
			} else {
				$PROCESSED["description"] = "";
			}

			/**
			* Required field "associated_proxy_ids" / Draft Authors (array of proxy ids).
			* This is actually accomplished after the draft is inserted below.
			*/
			if((isset($_POST["associated_proxy_ids"]))) {
				$associated_proxy_ids = explode(",", $_POST["associated_proxy_ids"]);
				foreach($associated_proxy_ids as $contact_order => $proxy_id) {
					if($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
						$PROCESSED["associated_proxy_ids"][(int) $contact_order] = $proxy_id;
					}
				}
			}

			/**
			* The current draft author must be in the draft author list.
			*/
			if (!in_array($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"], $PROCESSED["associated_proxy_ids"])) {
				array_unshift($PROCESSED["associated_proxy_ids"], $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]);

				$NOTICE++;
				$NOTICESTR[] = "You cannot remove yourself as a <strong>Draft Author</strong>.";
			}

			if (!$ERROR) {
				if ($db->AutoExecute("drafts", $PROCESSED, "UPDATE", "`draft_id` = ".$db->qstr($draft_id))) {
					/**
					* Delete existing draft contacts, so we can re-add them.
					*/
					$query = "DELETE FROM `draft_creators` WHERE `draft_id` = ".$db->qstr($draft_id);
					$db->Execute($query);

					/**
					* Add the updated draft authors to the draft_contacts table.
					*/
					
					if ((is_array($PROCESSED["associated_proxy_ids"])) && !empty($PROCESSED["associated_proxy_ids"])) {
						foreach ($PROCESSED["associated_proxy_ids"] as $proxy_id) {
							if (!$db->AutoExecute("draft_creators", array("draft_id" => $draft_id, "proxy_id" => $proxy_id), "INSERT")) {
								$ERROR++;
								$ERRORSTR[] = "There was an error while trying to attach a <strong>Draft Author</strong> to this draft.<br /><br />The system administrator was informed of this error; please try again later.";

								application_log("error", "Unable to insert a new draft_contact record while adding a new draft. Database said: ".$db->ErrorMsg());
							}
						}
					}

					$SUCCESS++;
					$SUCCESSSTR[] = "The <strong>Draft Information</strong> section has been successfully updated.";

					application_log("success", "Draft information for draft_id [".$draft_id."] was updated.");
				} else {
					$ERROR++;
					$ERRORSTR[] = "There was a problem updating this draft. The system administrator was informed of this error; please try again later.";

					application_log("error", "There was an error updating draft information for draft_id [".$draft_id."]. Database said: ".$db->ErrorMsg());
				}
			}
		break;
		case 1 :
		default :
			$PROCESSED = $draft_record;

			$query = "SELECT `proxy_id` FROM `draft_contacts` WHERE `draft_id` = ".$db->qstr($RECORD_ID);
			$results = $db->GetAll($query);
			if ($results) {
				foreach ($results as $result) {
					$PROCESSED["associated_proxy_ids"][] = $result["proxy_id"];
				}
			}
		break;
	}

	// Display Content
	switch ($STEP) {
		case 2 :
		case 1 :
		default :
			
		break;
	}
	
	$query = "	SELECT * 
				FROM `drafts`
				WHERE `draft_id` = ".$db->qstr($draft_id);
	$draft_information = $db->GetRow($query);
	
	if ($draft_information && $draft_information["status"] == "open") {
	
	$query = "	SELECT a.`proxy_id`, CONCAT(b.`lastname`, ', ', b.`firstname`) AS `fullname`
				FROM `draft_creators` AS a
				JOIN `".AUTH_DATABASE."`.`user_data` AS b
				ON a.`proxy_id` = b.`id`
				WHERE a.`draft_id` = ".$db->qstr($draft_id);
	$creators = $db->GetAssoc($query);

	if (!array_key_exists($ENTRADA_USER->getID(), $creators)) {
		add_notice("Your account is not approved to work on this draft schedule.<br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");
		echo display_notice();
	} else {
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
		?>
	
		<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>/drafts/?section=edit&draft_id=<?php echo $draft_id; ?>" method="post" id="editDraftForm" onsubmit="picklist_select('proxy_id')">
			<input type="hidden" name="step" value="2" />
			<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Editing Draft">
				<colgroup>
					<col style="width: 3%" />
					<col style="width: 20%" />
					<col style="width: 77%" />
				</colgroup>
				<thead>
					<tr>
						<td colspan="3">
							<a name="draft_information_section"></a><h2 id="draft_information_section" title="Draft Information">Draft Information</h2>
							<?php
							if ($SUCCESS) {
								fade_element("out", "display-success-box");
								echo display_success();
							}

							if ($NOTICE) {
								fade_element("out", "display-notice-box", 100, 15000);
								echo display_notice();
							}

							if ($ERROR) {
								echo display_error();
							}
							?>
						</td>
					</tr>
				</thead>
				<tbody id="draft-information">
					<tr>
						<td></td>
						<td><label for="draft_title" class="form-required">Draft Title</label></td>
						<td><input type="text" id="draft_name" name="draft_name" value="<?php echo html_encode($draft_information["name"]); ?>" maxlength="64" style="width: 96%" /></td>
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align: top">
							<label for="draft_description" class="form-nrequired">Draft Description</label>
						</td>
						<td>
							<textarea id="draft_description" name="draft_description" style="width: 550px; height: 125px" cols="70" rows="10"><?php echo clean_input($draft_information["description"], array("trim", "encode")); ?></textarea>
						</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td style="vertical-align: top">
							<label for="associated_proxy_ids" class="form-required">Draft Authors</label>
							<div class="content-small" style="margin-top: 15px">
								<strong>Tip:</strong> Select any other individuals you would like to give access to assigning or modifying this draft.
							</div>
						</td>
						<td style="vertical-align: top">
							<input type="text" id="author_name" name="fullname" size="30" autocomplete="off" style="width: 203px" />
							<?php
							$ONLOAD[] = "author_list = new AutoCompleteList({ type: 'author', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=coordinator', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
							?>
							<div class="autocomplete" id="author_name_auto_complete"></div>
							<input type="hidden" id="associated_author" name="associated_proxy_ids" value="" />
							<input type="button" class="button-sm" id="add_associated_author" value="Add" style="vertical-align: middle" />
							<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
							<ul id="author_list" class="menu" style="margin-top: 15px">
								<?php
								if (is_array($creators) && !empty($creators)) {
									$selected_authors = array();
									$query = "	SELECT `id` AS `proxy_id`, CONCAT_WS(', ', `lastname`, `firstname`) AS `fullname`, `organisation_id`
												FROM `".AUTH_DATABASE."`.`user_data`
												WHERE `id` IN (".implode(", ", array_keys($creators)).")
												ORDER BY `lastname` ASC, `firstname` ASC";
									$results = $db->GetAll($query);
									if ($results) {
										foreach ($results as $result) {
											$selected_authors[$result["proxy_id"]] = $result;
										}

										unset($results);
									}

									foreach ($creators as $proxy_id => $creator) {
										if ($proxy_id = (int) $proxy_id) {
											if (array_key_exists($proxy_id, $selected_authors)) {
												?>
												<li class="community" id="author_<?php echo $proxy_id; ?>" style="cursor: move;"><?php echo $selected_authors[$proxy_id]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="author_list.removeItem('<?php echo $proxy_id; ?>');" class="list-cancel-image" /></li>
												<?php
											}
										}
									}
								}
								?>
							</ul>
							<input type="hidden" id="author_ref" name="author_ref" value="" />
							<input type="hidden" id="author_id" name="author_id" value="" />
						</td>
					</tr>
					<tr>
						<td colspan="3" style="padding: 25px 0px 25px 0px">
							<div style="float: right; text-align: right">
								<input type="submit" class="button" value="Save Changes" />
							</div>
							<div class="clear"></div>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
		</div>
		<?php 
		$query = "	SELECT a.*, CONCAT(c.`prefix`, ' ', c.`lastname`, ', ', c.`firstname` ) AS `fullname`, f.`curriculum_type_name` AS `event_term`
					FROM `draft_events` AS a
					LEFT JOIN `draft_contacts` AS b
					ON b.`devent_id` = a.`devent_id`
					LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS c
					ON b.`proxy_id` = c.`id`
					LEFT JOIN `draft_audience` AS d
					ON a.`event_id` = d.`event_id`
					LEFT JOIN `courses` AS e
					ON a.`course_id` = e.`course_id`
					LEFT JOIN `curriculum_lu_types` AS f
					ON e.`curriculum_type_id` = f.`curriculum_type_id`
					WHERE a.`draft_id` = ".$db->qstr($draft_id)."
					GROUP BY a.`devent_id`
					ORDER BY a.`event_start`";
		$learning_events = $db->GetAll($query);
		?>
		<script type="text/javascript">
			jQuery(function(){
				jQuery(".noLink").live("click", function(){
					return false;
				});

				jQuery('#draftEvents').dataTable({
					"aaSorting": [[ 1, "asc" ]]
				});

				jQuery("tbody a.date, tbody a.title, tbody a.time").live("click", function(){
					var element = jQuery(this);
					var temp_id = element.parent().parent().attr('rel');
					var temp_input = jQuery('<input/>', {
						id: element.parent().parent().attr('id')+'-input',
						type: 'text'
					});

					temp_input.addClass(element.attr("class"));

					var temp_data = element.html();

					switch (element.attr('class').trim()) {
						case "date" :
							temp_input.datepicker({
								dateFormat: "yy-mm-dd",
								defaultDate: temp_data,
								onClose: function(dateText, inst) {
									temp_input.attr("value", dateText);
									if (dateText != temp_data && dateText != "") { 
										temp_input.parent().append(update_draft_event("date", temp_id, dateText)); 
										temp_input.remove();
									} else {
										temp_input.parent().append(element);
										temp_input.remove();
									}
								}
							});
						break;
					}

					element.parent().append(temp_input);
					element.siblings("input").focus();
					element.remove();

					return false;
				});

				jQuery("tbody input[type=text].time, tbody input[type=text].title").live("blur", function(){
					var element = jQuery(this);
					var temp_id = element.parent().parent().attr('rel');
					element.parent().append(update_draft_event(element.attr("class").trim(), temp_id, element.val()));
					element.remove();
				});

				function update_draft_event(action, event_id, new_data) {
					var ajax_data = "";
					jQuery.ajax({
						type: 'POST',
						url: '<?php echo ENTRADA_URL ;?>/api/learning-events-schedule.api.php',
						data: 'action='+action+'&id='+event_id+'&data='+new_data,
						async: false,
						success: function(data) {
							ajax_data = data;
						}
					});
					return ajax_data;
				}

				jQuery("form").keypress(function(e) {
					if (e.keyCode == 13) {
						jQuery("input").blur();
						return false;
					}
				});
				
				jQuery(".import-csv").live("click", function(){
					jQuery("#import-csv").dialog({
						title: "Import CSV",
						resizable: false,
						draggable: false,
						modal: true,
						width: 300,
						buttons: [
							{
								text: "Import",
								click: function() { jQuery("#csv-form").trigger("submit"); }
							},
							{
								text: "Cancel",
								click: function() { jQuery(this).dialog("close"); }
							}
						]
					});
					return false;
				});
			});
		</script>
		<style type="text/css">
			#draftEvents_length {padding:5px 4px 0 0;}
			#draftEvents_filter {-moz-border-radius:10px 10px 0px 0px;-webkit-border-top-left-radius: 10px;-webkit-border-top-right-radius: 10px;border-radius: 10px 10px 0px 0px; border: 1px solid #9D9D9D;border-bottom:none;background-color:#FAFAFA;font-size: 0.9em;padding:3px;}
			#draftEvents_paginate a {margin:2px 5px;}
			#import-csv {display:none;}
		</style>
		<div style="clear:both;"></div>
		<h2>Learning Events in Draft</h2>
		<?php 
		$JQUERY[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/jquery/jquery.dataTables.min.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
		if ($ENTRADA_ACL->amIAllowed("event", "create", false)) {
			?>
			<div style="float: right">
				<ul class="page-action">
					<li><a href="#" class="import-csv">Import CSV</a></li>
					<li><a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=add&mode=draft&draft_id=<?php echo $draft_id; ?>" class="strong-green">Add New Event</a></li>
				</ul>
			</div>
			<div style="clear: both"></div>
			<?php
		}
		?>
		<div id="import-csv">
			<form id="csv-form" action="<?php echo ENTRADA_URL; ?>/admin/events/drafts?section=csv-import&draft_id=<?php echo $draft_id; ?>" enctype="multipart/form-data" method="POST">
				<p><a href="<?php echo ENTRADA_URL; ?>/admin/events/drafts?section=edit&mode=csv-example">Click here to download example CSV</a>.</p>
				<input type="hidden" name="draft_id" value="<?php echo $draft_id; ?>" />
				<input type="file" name="csv_file" /> 
			</form>
		</div>
		<form name="frmSelect" action="<?php echo ENTRADA_URL; ?>/admin/events?section=delete&mode=draft&draft_id=<?php echo $draft_id; ?>" method="post">
			<table class="tableList" id="draftEvents" cellspacing="0" cellpadding="1" summary="List of Events" style="margin-bottom:5px;">
				<colgroup>
					<col class="modified" />
					<col class="date" width="12%" />
					<col class="time" width="10%" />
					<col class="duration" width="14%" />
					<col class="term" />
					<col class="teacher" />
					<col class="title" />
				</colgroup>
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="date"><a href="#" class="noLink">Date</a></td>
						<td class="time"><a href="#" class="noLink">Time</a></td>
						<td class="duration"><a href="#" class="noLink">Duration</a></td>
						<td class="term"><a href="#" class="noLink">Term</a></td>
						<td class="teacher"><a href="#" class="noLink">Teacher</a></td>
						<td class="title"><a href="#" class="noLink">Event Title</a></td>
					</tr>
				</thead>
				<?php if ($ENTRADA_ACL->amIAllowed("event", "delete", false) or $ENTRADA_ACL->amIAllowed("event", "create", false)) : ?>
				<?php endif; ?>
				<tbody>
				<?php

				$count_modified = 0;

				foreach ($learning_events as $result) {
					$url = "";
					$accessible = true;
					$administrator = false;
					if ($ENTRADA_ACL->amIAllowed(new EventResource($result["event_id"], $result["course_id"], $result["organisation_id"]), "update")) {
						$administrator = true;
						$url = ENTRADA_URL."/admin/events?section=edit&mode=draft&id=".$result["devent_id"];
					} else if ($ENTRADA_ACL->amIAllowed(new EventContentResource($result["devent_id"], $result["course_id"], $result["organisation_id"]), "update")) {
						$url = ENTRADA_URL."/admin/events?section=content&id=".$result["devent_id"];
					}

					if ((($result["release_date"]) && ($result["release_date"] > time())) || (($result["release_until"]) && ($result["release_until"] < time()))) {
						$accessible = false;
					}

					echo "<tr id=\"event-".$result["event_id"]."\" rel=\"".$result["devent_id"]."\" class=\"event".((!$url) ? " np" : ((!$accessible) ? " na" : ""))."\">\n";
					echo "	<td class=\"modified\">".(($administrator) ? "<input type=\"checkbox\" name=\"checked[]\" value=\"".$result["devent_id"]."\" />" : "<img src=\"".ENTRADA_URL."/images/pixel.gif\" width=\"19\" height=\"19\" alt=\"\" title=\"\" />")."</td>\n";
					echo "	<td class=\"date\">".(($url) ? "<a href=\"".$url."\" title=\"Event Date\" class=\"date\">" : "").date("Y-m-d", $result["event_start"]).(($url) ? "</a>" : "")."</td>\n";
					echo "	<td class=\"time\">".(($url) ? "<a href=\"".$url."\" title=\"Event Time\" class=\"time\">" : "").date("H:i", $result["event_start"]).(($url) ? "</a>" : "")."</td>\n";
					echo "	<td class=\"duration\">".(($url) ? "<a href=\"".$url."\" title=\"Duration\">" : "").$result["event_duration"].(($url) ? " minutes</a>" : "")."</td>\n";
					echo "	<td class=\"term\">".(($url) ? "<a href=\"".$url."\" title=\"Intended For ".html_encode($result["event_term"])."\">" : "").html_encode($result["event_term"]).(($url) ? "</a>" : "")."</td>\n";
					echo "	<td class=\"teacher\">".(($url) ? "<a href=\"".$url."\" title=\"Primary Teacher: ".html_encode($result["fullname"])."\">" : "").html_encode($result["fullname"]).(($url) ? "</a>" : "")."</td>\n";
					echo "	<td class=\"title\">".(($url) ? "<a href=\"".$url."\" title=\"Event Title: ".html_encode($result["event_title"])."\" class=\"title\">" : "").html_encode($result["event_title"]).(($url) ? "</a>" : "")."</td>\n";
					echo "</tr>\n";
				}
				?>
				</tbody>
			</table>
			<table width="100%">
				<tr>
					<td></td>
					<td style="padding-top: 10px">
						<?php
						if ($ENTRADA_ACL->amIAllowed("event", "delete", false)) {
							?>
							<input type="button" class="button" onclick="document.frmSelect.submit()" value="Delete Selected" />
							<?php
						}
						?>
					</td>
					<td style="padding-top: 10px; text-align: right">
						<input type="button" value="Calendar Preview" id="calendar_preview" onclick="window.location='<?php echo ENTRADA_URL . "/admin/events/drafts?section=preview&draft_id=".$draft_id; ?>';" />
						<?php
						if ($ENTRADA_ACL->amIAllowed("event", "delete", false)) {
							?>
							<input type="button" value="Approve Draft" onclick="window.location='<?php echo ENTRADA_URL . "/admin/events/drafts?section=status&action=approve&draft_id=".$draft_id; ?>';" />
							<?php
						}
						?>
					</td>
				</tr>
			</table>
		</form>
	<?php }
	} else {
		add_error("This draft has been approved and can no longer be edited.");
		echo display_error();
	}
} ?>