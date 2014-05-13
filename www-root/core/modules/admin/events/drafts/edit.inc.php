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

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."].");
} else {

	if (isset($_GET["mode"]) && $temp = (clean_input($_GET["mode"], array("trim", "striptags")))) {
		ob_clear_open_buffers();
		switch ($temp) {
			case "csv-example" :
				$csv_content  = "Original Event,Parent Event,Term,Course Code,Course Name,Date,Start Time,Total Duration,Event Type Durations,Event Types,Event Title,Event Description,Location,Audience (Cohorts),Audience (Groups),Audience (Students),Teacher Numbers,Teacher Names, Objectives Release Date"."\n";
				$csv_content .= "0,1,Term 1,EXAMPLE101,Introduction to Example,".date("Y-m-d").",9:00,120,60;30;30,Lecture;Lab;Review / Feedback Session,\"Demo Event #1: Learning Demos\",\"This session will focus on learning demos and their purpose.\",Room 102,Class of ".fetch_first_year().",,,8217321,Dr. Jenn Warden,".date("Y-m-d", strtotime("+1 day"))."\n";
				$csv_content .= "0,1,Term 1,EXAMPLE101,Introduction to Example,".date("Y-m-d", strtotime("+1 week")).",9:00,60,60,Lecture,\"Demo Event #2: More About Demos\",\"This session will expand on learning demos, and give an idea of how to give one.\",Room 102,Class of ".fetch_first_year().",,,7291430,Ted Simon,".date("Y-m-d", strtotime("+10 days"))."\n";
				$csv_content .= "0,0,Term 1,EXAMPLE101,Introduction to Example,".date("Y-m-d", strtotime("+1 week")).",10:00,60,60,Small Group,\"Demo Small Group Session (Group 1)\",,Room 201,,".fetch_first_year()." Group 1,,7291430,Ted Simon,".date("Y-m-d", strtotime("+12 days"))."\n";
				$csv_content .= "0,0,Term 1,EXAMPLE101,Introduction to Example,".date("Y-m-d", strtotime("+1 week")).",10:00,60,60,Small Group,\"Demo Small Group Session (Group 2)\",,Room 202,,".fetch_first_year()." Group 2,,8392943,Sam Edwards,".date("Y-m-d", strtotime("+2 weeks"))."\n";
				$csv_content .= "0,0,Term 1,EXAMPLE101,Introduction to Example,".date("Y-m-d", strtotime("+1 week")).",10:00,60,60,Small Group,\"Demo Small Group Session (Group 3)\",,Room 203,,".fetch_first_year()." Group 3,,2536437,Jim Sampson,".date("Y-m-d", strtotime("+1 week"))."\n";
				$csv_content .= "0,1,Term 1,EXAMPLE101,Introduction to Example,".date("Y-m-d", strtotime("+8 days")).",8:00,60,60,Examination,\"Mid-Term Make Up Session\",\"An mid term examination on Learning Demos will be given during this session.\",Room 203a,,,8290103;2823945,7291430,Ted Simon,".date("Y-m-d", strtotime("+2 weeks"))."\n";
				$csv_content .= "0,1,Term 4,EXAMPLE403,Applying Examples,".date("Y-m-d", strtotime("+9 days")).",14:30,60,60,Directed Independent Learning,Neurology Theme of the Week: Multiple Sclerosis,,,,,,,,".date("Y-m-d", strtotime("+3 weeks"))."\n";

				header('Pragma: public');
				header('Content-type: text/csv');
				header('Content-Disposition: attachment; filename="draft-schedule-example.csv"');

				echo $csv_content;

			break;
			default :
                continue;
            break;
		}
		exit;
	}

	$draft_id = (int) $_GET["draft_id"];
    $draft = Models_Event_Draft::fetchRowByID($draft_id);
    
	/**
	* Load the rich text editor.
	*/
	load_rte();
	// Error Checking
	switch ($STEP) {
		case 2 :
			
			$i = 0;
            if (isset($_POST["options"]) && !empty($_POST["options"])) {
                foreach ($_POST["options"] as $option => $value) {
                    $PROCESSED["options"][$i]["option"] = clean_input($option, "alpha");
                    $PROCESSED["options"][$i]["value"] = 1;
                    $i++;
                }
            }
			
			/**
			* Required field "draft_name" / Draft Title.
			*/
			if ((isset($_POST["draft_name"])) && ($tmp_input = clean_input($_POST["draft_name"], array("notags", "trim")))) {
				$PROCESSED["name"] = $tmp_input;
			} else {
				add_error("The <strong>Draft Title</strong> field is required.");
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
			if (!in_array($ENTRADA_USER->getActiveId(), $PROCESSED["associated_proxy_ids"])) {
				array_unshift($PROCESSED["associated_proxy_ids"], $ENTRADA_USER->getActiveId());
				add_notice("You cannot remove yourself as a <strong>Draft Author</strong>.");
			}

			if (!$ERROR) {
				if ($draft->fromArray(array("name" => $PROCESSED["name"], "description" => $PROCESSED["description"]))->update()) {
					/**
					* Delete existing draft contacts, so we can re-add them.
					*/
					if ($draft->deleteCreators()) {
                        /**
                        * Add the updated draft authors to the draft_contacts table.
                        */

                        if ((is_array($PROCESSED["associated_proxy_ids"])) && !empty($PROCESSED["associated_proxy_ids"])) {
                            foreach ($PROCESSED["associated_proxy_ids"] as $proxy_id) {
                                $creator = new Models_Event_Draft_Creator(array("draft_id" => $draft_id, "proxy_id" => $proxy_id));
                                if (!$creator->insert()) {
                                    add_error("There was an error while trying to attach a <strong>Draft Author</strong> to this draft.<br /><br />The system administrator was informed of this error; please try again later.");
                                    application_log("error", "Unable to insert a new draft_contact record while adding a new draft. Database said: ".$db->ErrorMsg());
                                }
                            }
                        }
                    }
					
					if ($PROCESSED["options"]) {
						$draft->deleteOptions();
						foreach ($PROCESSED["options"] as $option) {
                            $option["draft_id"] = $draft_id;
                            $new_draft_option = new Models_Event_Draft_Option($option);
							if (!$new_draft_option->insert()) {
								application_log("error", "Error when saving draft [".$draft_id."] options, DB said: ".$db->ErrorMsg());
							}
						}
					}
					add_success("The <strong>Draft Information</strong> section has been successfully updated.");
					application_log("success", "Draft information for draft_id [".$draft_id."] was updated.");
				} else {
					add_error("There was a problem updating this draft. The system administrator was informed of this error; please try again later.");
					application_log("error", "There was an error updating draft information for draft_id [".$draft_id."]. Database said: ".$db->ErrorMsg());
				}
			}
		break;
	}

	if ($draft && $draft->getStatus() == "open") {

	$BREADCRUMB[]	= array("url" => "", "title" => "Edit ".$draft->getName());

    $options = Models_Event_Draft_Option::fetchAllByDraftID($draft_id);
    if ($options) {
        foreach ($options as $option) {
            $draft_options[$option->getOption()] = true; 
        }
    }
    
    $current_creators = array();
    $creators = Models_Event_Draft_Creator::fetchAllByDraftID($draft_id);
    if (!empty($creators)) {
        foreach ($creators as $creator) {
            $PROCESSED["associated_proxy_ids"][] = $creator->getProxyID();
        }
    }

	if (!in_array($ENTRADA_USER->getID(), $PROCESSED["associated_proxy_ids"])) {
		add_notice("Your account is not approved to work on this draft schedule.<br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");
		echo display_notice();
	} else {
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
		?>

		<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>/drafts/?section=edit&draft_id=<?php echo $draft_id; ?>" method="post" id="editDraftForm" onsubmit="picklist_select('proxy_id')">
			<input type="hidden" name="step" value="2" />
			<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Editing Draft">
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
						<td><input type="text" id="draft_name" name="draft_name" value="<?php echo html_encode($draft->getName()); ?>" maxlength="64" style="width: 96%" /></td>
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align: top">
							<label for="draft_description" class="form-nrequired">Draft Description</label>
						</td>
						<td>
							<textarea id="draft_description" name="draft_description" class="expandable" style="width: 96%;"><?php echo clean_input($draft->getDescription(), array("trim", "encode")); ?></textarea>
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
							<input type="button" class="btn" id="add_associated_author" value="Add" style="vertical-align: middle" />
							<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
							<ul id="author_list" class="menu" style="margin-top: 15px">
								<?php
								if (is_array($creators) && !empty($creators)) {
									
									foreach ($creators as $creator) {
										?>
                                        <li class="community" id="author_<?php echo $creator->getProxyID(); ?>" style="cursor: move;"><?php echo $creator->getCreator()->getFullName(); ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="author_list.removeItem('<?php echo $creator->getProxyID(); ?>');" class="list-cancel-image" /></li>
										<?php
									}
								}
								?>
							</ul>
							<input type="hidden" id="author_ref" name="author_ref" value="" />
							<input type="hidden" id="author_id" name="author_id" value="" />
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td colspan="2">
							<div class="content-small" style="margin-top: 15px">
								<strong>Copy Resources:</strong><br />
								<span class="content-small">Selecting the following options will copy the content from the previous instance of the event.</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td style="vertical-align: top" colspan="2">
							<table width="100%" cellpadding="0" cellspacing="0">
								<colgroup>
									<col style="width: 3%" />
									<col style="width: 3%" />
									<col style="width: 94%" />
								</colgroup>
								<tr>
									<td>&nbsp;</td>
									<td><input type="checkbox" <?php echo ($draft_options["files"] ? "checked=\"checked\"" : ""); ?> name="options[files]" /></td>
									<td>Copy files <span class="content-small">- Excluding podcasts</span></td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><input type="checkbox" <?php echo ($draft_options["links"] ? "checked=\"checked\"" : ""); ?> name="options[links]" /></td>
									<td>Copy links</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><input type="checkbox" <?php echo ($draft_options["objectives"] ? "checked=\"checked\"" : ""); ?> name="options[objectives]" /></td>
									<td>Copy objectives</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><input type="checkbox" <?php echo ($draft_options["topics"] ? "checked=\"checked\"" : ""); ?> name="options[topics]" /></td>
									<td>Copy hot topics</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><input type="checkbox" <?php echo ($draft_options["quizzes"] ? "checked=\"checked\"" : ""); ?> name="options[quizzes]" /></td>
									<td>Copy quizzes</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<div style="float: right; text-align: right">
								<input type="submit" class="btn btn-primary" value="Save Changes" />
							</div>
							<div class="clear"></div>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
		<?php
		$draft_events = Models_Event_Draft_Event::fetchAllByDraftID($draft_id);
		?>
		<script type="text/javascript">
			jQuery(function(){
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
			<div class="row-fluid space-below">
				<div class="pull-right">
					<a href="#" class="btn btn-small btn-success import-csv"><i class="icon-plus-sign icon-white"></i> Import CSV</a>
					<a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=add&mode=draft&draft_id=<?php echo $draft_id; ?>" class="btn btn-small btn-success"><i class="icon-plus-sign icon-white"></i> Add New Event</a>
				</div>
			</div>
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
			<table class="table table-striped table-bordered" id="draftEvents" cellspacing="0" cellpadding="1" summary="List of Events" style="margin-bottom:5px;">
				<colgroup>
					<col class="modified" />
					<col class="date-smallest" />
					<col class="accesses" />
					<col class="general" />
					<col class="title" />
				</colgroup>
				<thead>
					<tr>
						<th class="modified" width="5%">&nbsp;</th>
						<th class="date-smallest" width="12%">Date</th>
						<th class="accesses" width="7%">Time</th>
						<th class="general">Duration</th>
						<th class="title">Event Title</th>
					</tr>
				</thead>
				<?php if ($ENTRADA_ACL->amIAllowed("event", "delete", false) or $ENTRADA_ACL->amIAllowed("event", "create", false)) : ?>
				<?php endif; ?>
				<tbody>
				<?php

				$count_modified = 0;
                if (!empty($draft_events)) {
                    foreach ($draft_events as $draft_event) {
                        $url = "";
                        $accessible = true;

                        $url = ENTRADA_URL."/admin/events?section=edit&mode=draft&id=".$draft_event->getDeventID();

                        if ((($draft_event->getReleaseDate()) && ($draft_event->getReleaseDate() > time())) || (($draft_event->getReleaseUntil()) && ($draft_event->getReleaseUntil() < time()))) {
                            $accessible = false;
                        }

                        echo "<tr id=\"event-".$draft_event->getEventID()."\" rel=\"".$draft_event->getDeventID()."\" class=\"event".((!$url) ? " np" : ((!$accessible) ? " na" : ""))."\">\n";
                        echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"checked[]\" value=\"".$draft_event->getDeventID()."\" /></td>\n";
                        echo "	<td class=\"date-smallest\">".(($url) ? "<a href=\"".$url."\" title=\"Event Date\" class=\"date\">" : "").date("Y-m-d", $draft_event->getEventStart()).(($url) ? "</a>" : "")."</td>\n";
                        echo "	<td class=\"accesses\">".(($url) ? "<a href=\"".$url."\" title=\"Event Time\" class=\"time\">" : "").date("H:i", $draft_event->getEventStart()).(($url) ? "</a>" : "")."</td>\n";
                        echo "	<td class=\"general\">".(($url) ? "<a href=\"".$url."\" title=\"Duration\">" : "").$draft_event->getEventDuration().(($url) ? " minutes</a>" : "")."</td>\n";
                        echo "	<td class=\"title\">".(($url) ? "<a href=\"".$url."\" title=\"Event Title: ".html_encode($draft_event->getEventTitle())."\" class=\"title\">" : "").html_encode($draft_event->getEventTitle()).(($url) ? "</a>" : "")."</td>\n";
                        echo "</tr>\n";
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="5">There are currently no events in this draft. Please use the Import CSV button or the Add New Event button above to create some.</td>
                    </tr>
                    <?php
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
							<input type="button" class="btn btn-danger" onclick="document.frmSelect.submit()" value="Delete Selected" />
							<?php
						}
						?>
					</td>
					<td style="padding-top: 10px; text-align: right">
						<input class="btn" type="button" value="Calendar Preview" id="calendar_preview" onclick="window.location='<?php echo ENTRADA_URL . "/admin/events/drafts?section=preview&draft_id=".$draft_id; ?>';" />
						<?php
						if ($ENTRADA_ACL->amIAllowed("event", "delete", false)) {
							?>
							<input class="btn btn-primary" type="button" value="Publish Draft" onclick="window.location='<?php echo ENTRADA_URL . "/admin/events/drafts?section=status&action=approve&draft_id=".$draft_id; ?>';" />
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