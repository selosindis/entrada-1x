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
 * @author Organisation: University of Calgary
 * @author Unit: Faculty of Medicine
 * @author Developer: Doug Hall<hall@ucalgary.ca>
 * @copyright Copyright 2011 University of Calgary. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_GROUPS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('group', 'update')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

    add_error("You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
    $HEAD[] = "<script type=\"text/javascript\" >var ENTRADA_URL = '". ENTRADA_URL ."';</script>";
    $HEAD[] = "<script type=\"text/javascript\" src=\"".  ENTRADA_URL ."/javascript/jquery/jquery.advancedsearch.js\"></script>";
    $HEAD[] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"".  ENTRADA_URL ."/css/jquery/jquery.advancedsearch.css\" />";

    // ERROR CHECKING
	switch ($STEP) {
		case "2" :
			if ((isset($_POST["add_group_id"])) && ((int) trim($_POST["add_group_id"]))) {
				$PROCESSED["group_id"] = (int) trim($_POST["add_group_id"]);
			} else {
				header("Location: ".ENTRADA_URL."/admin/".$MODULE);
			}

            $proxy_ids = array();

            if (isset($_POST["search_target_control_ids"])) {
                $proxy_ids = explode(',', $_POST["search_target_control_ids"]);

                foreach ($proxy_ids as &$proxy_id) {
                    $proxy_id = (int)trim($proxy_id);
                }

                unset($proxy_id);
            }

            if ($proxy_ids) { ?>
                <script type="text/javascript">
                    sessionStorage.removeItem("search_target_control_ids");
                </script>

                <?php
                $PROCESSED["entrada_only"] = 1;
				$PROCESSED["created_date"] = time();
				$PROCESSED["created_by"] = $ENTRADA_USER->getID();
                $PROCESSED["updated_date"] = time();
                $PROCESSED["updated_by"] = $ENTRADA_USER->getID();

                foreach ($proxy_ids as $proxy_id) {
                    if (!$db->GetOne("SELECT `gmember_id` FROM `group_members` WHERE `group_id` = ".$db->qstr($PROCESSED["group_id"])." AND `proxy_id` =".$db->qstr($proxy_id))) {
                        $PROCESSED["proxy_id"]	= $proxy_id;

                        if (!$db->AutoExecute("`group_members`", $PROCESSED, "INSERT")) {
                            add_error("Failed to insert this member into the group. Please contact a system administrator if this problem persists.");
                            application_log("error", "Error while inserting member into database. Database server said: ".$db->ErrorMsg());
                        }
                    }
                }
            } else {
                add_error("You must select a user(s) to add to this group. Please be sure that you select at least one user to add to this group from the interface.");
            }

			$STEP = 1;

		break;
		default :
			// No error checking for step 1.
		break;
	}

	// PAGE DISPLAY
	switch ($STEP) {
		case "2" :			// Step 2
            add_success("You have successfully added this member");
			echo display_success($SUCCESSSTR);
		break;

		default :			// Step 1
			$group_ids = array();


/**
 * @todo What the heck is this? Will who ever did this, please fix this. It's crap.
 *
 */
			if (isset($PROCESSED["group_id"]) && (int)$PROCESSED["group_id"]) {
				$GROUP_ID = $PROCESSED["group_id"];
			} else {
				$GROUP_ID = 0;
			}
			if (isset($_GET["ids"])) {
				$_SESSION["ids"] = array(htmlentities($_GET["ids"]));
			} elseif (isset($_POST["checked"])) {
				$_SESSION["ids"] = $_POST["checked"];
			} elseif ((isset($_POST["group_id"])) && ((int) trim($_POST["group_id"]))) {
				$GROUP_ID = (int) trim($_POST["group_id"]);
			} elseif ((isset($_GET["id"])) && ((int) trim($_GET["id"]))) {
				$GROUP_ID = (int) trim($_GET["id"]);
			}

			if ((!isset($_SESSION["ids"]) || !is_array($_SESSION["ids"])) || (!@count($_SESSION["ids"]))) {
				header("Location: ".ENTRADA_URL."/admin/groups");
				exit;
			}

			$group_ids = $_SESSION["ids"];

			$query = "	SELECT * FROM `groups`
						WHERE `group_id` IN (".implode(", ", $group_ids).")
						ORDER By `group_name`";
			$results	= $db->GetAll($query);

			if (!$results) {
				header("Location: ".ENTRADA_URL."/admin/".$MODULE);
			}
			if (!$GROUP_ID) {
				$GROUP_ID = $results[0]["group_id"]; // $group_ids[0];
			}


			$query = "	SELECT a.*, b.`organisation_id` FROM `groups` AS a
						LEFT JOIN `group_organisations` AS b
						ON a.`group_id` = b.`group_id`
						WHERE a.`group_id` = ".$db->qstr($GROUP_ID);
			if ($group = $db->GetRow($query)) {
				$group_name = $group["group_name"];
				$PROCESSED = $group;
			}
			
			/**
			 * Update requested order to sort by.
			 * Valid: asc, desc
			 */
			if (isset($_GET["so"])) {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = ((strtolower($_GET["so"]) == "desc") ? "DESC" : "ASC");
			} else if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"])) {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = "ASC";
			}
			
			/**
			 * Update requested column to sort by.
			 */
			if (isset($_GET["sb"])) {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = $_GET["sb"];
			} else if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"])) {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "fullname";
			}

			/**
			 * Provide the queries with the columns to order by.
			 */
			switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) {
				case "grouprole" :
					$order_by = "ORDER BY `grouprole` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
				break;
				case "fullname" :
				default :
					$order_by = "ORDER BY `fullname` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
				break;
			}

			$emembers_query	= "	SELECT c.`proxy_id`, c.`gmember_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, c.`member_active`,
								a.`username`, a.`organisation_id`, b.`group`, CONCAT_WS(':', b.`group`, b.`role`) AS `grouprole`
								FROM `".AUTH_DATABASE."`.`user_data` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
								ON a.`id` = b.`user_id`
								INNER JOIN `group_members` c ON a.`id` = c.`proxy_id`
								WHERE b.`account_active` = 'true'
								AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
								AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
								AND c.`group_id` = ".$db->qstr($GROUP_ID)."
								GROUP BY a.`id`
								$order_by";
			$ONLOAD[]	= "showgroup('".$group_name."',".$GROUP_ID.")";

			$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/groups?section=edit", "title" => "Edit");

			?>
			<h1>Editing Groups</h1>

			<h2>Selected Groups</h2>
			<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo "$MODULE"; ?>?section=edit&step=1" method="post" id="select-group-form">
				<input type="hidden" id="step" name="step" value="1" />
				<input type="hidden" id="group_id" name="group_id" value="" />

				<?php echo (($ERROR) ? display_error($ERRORSTR) : ""); ?>
				<table class="tableList" cellspacing="1" cellpadding="1">
					<colgroup>
						<col style="width: 6%" />
						<col style="width: 54%" />
						<col style="width: 30%" />
						<col style="width: 10%" />
					</colgroup>
					<thead>
						<tr>
							<td></td>
							<td>Group Name</td>
							<td>Members</td>
							<td></td>
						</tr>
					</thead>
					<tbody>
					<?php
						foreach($results as $result) {
							$members = $db->GetRow("SELECT COUNT(*) AS members, case when (MIN(`member_active`)=0) then 1 else 0 end as `inactive`
													FROM  `group_members` WHERE `group_id` = ".$db->qstr($result["group_id"]));

								echo "<tr class=\"group".((!$result["group_active"]) ? " na" : (($members["inactive"]) ? " np" : ""))."\">";
								echo "	<td><input type=\"radio\" name=\"groups\" value=\"".$result["group_id"]."\" onclick=\"selectgroup(".$result["group_id"].",'".$result["group_name"]."');\"".(($result["group_id"] == $GROUP_ID) ?" checked=\"checked\"" : "")."/></td>\n";
								echo "	<td><a href=\"".ENTRADA_URL."/admin/groups?section=edit&id=".$result["group_id"]."\" >".html_encode($result["group_name"])."</a></td>";
								echo "	<td><a href=\"".ENTRADA_URL."/admin/groups?section=edit&id=".$result["group_id"]."\" >".$members["members"]."</a></td>";
								echo "	<td>
											<a href=\"".ENTRADA_URL."/admin/groups?section=manage&gids=".$result["group_id"]."\"><img src=\"".ENTRADA_URL."/images/action-edit.gif\" width=\"16\" height=\"16\" alt=\"Rename Group\" title=\"Rename Group\" border=\"0\" /></a>&nbsp;
											<a href=\"".ENTRADA_URL."/admin/groups?section=manage&ids=".$result["group_id"]."\"><img src=\"".ENTRADA_URL."/images/action-delete.gif\" width=\"16\" height=\"16\" alt=\"Delete/Activate Group\" title=\"Delete/Activate Group\" border=\"0\" /></a>
										</td>\n";
								echo "</tr>";
						}
					?>
					</tbody>
				</table>
			</form>
			<?php

//			$orgs = array_keys($ENTRADA_USER->getAllOrganisations());
//			$query = "SELECT * FROM `group_organisations` WHERE `group_id` =".$db->qstr($GROUP_ID)." AND `organisation_id` IN (".implode(",",$orgs).")";
//			print_r($ENTRADA_USER->getAllOrganisations());
//			if ($result = $db->GetAll($query)) {
			?>
			<h2>View Selected Group Members</h2>
			<form action="<?php echo ENTRADA_URL; ?>/admin/groups?section=manage" method="post">
				<table class="tableList" cellspacing="1" cellpadding="1">
					<colgroup>
						<col style="width: 6%" />
						<col style="width: 54%" />
						<col style="width: 30%" />
						<col style="width: 10%" />
					</colgroup>
					<thead>
						<tr>
							<td>&nbsp;</td>
							<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "fullname") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("fullname", "Full Name"); ?></td>
							<td class="grouprole<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "grouprole") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("grouprole", "Group &amp; Role"); ?></td>
							<td>&nbsp;</td>
						</tr>
					</thead>
					<tbody>
					<?php
						$results = $db->GetAll($emembers_query);
						if ($results) {
                            $current_members_ids = array();

							foreach($results as $result) {
								echo "<tr class=\"event".(!$result["member_active"] ? " na" : "")."\">";
								echo "	<td><input type=\"checkbox\" class=\"delchk\" name=\"checked[]\" onclick=\"memberChecks()\" value=\"".$result["gmember_id"]."\" /></td>\n";
								echo "	<td><a href=\"".ENTRADA_URL."/people?profile=".$result["username"]."\" >".html_encode($result["fullname"])."</a></td>";
								echo "	<td><a href=\"".ENTRADA_URL."/people?profile=".$result["username"]."\" >".$result["grouprole"]."</a></td>";
								echo "	<td>
											<a href=\"".ENTRADA_URL."/admin/groups?section=manage&mids=".$result["gmember_id"]."\"><img src=\"".ENTRADA_URL."/images/action-delete.gif\" width=\"16\" height=\"16\" alt=\"Delete/Activate Member\" title=\"Delete/Activate Member\" border=\"0\" /></a>
										</td>\n";
								echo "</tr>";

                                $current_members_ids[] = $result["proxy_id"];
							}
						}
					?>
					</tbody>
				</table>

				<div id="delbutton" style="padding-top: 15px; text-align: right;">
                    <input type="hidden" name="coa" id="coa" value="" /> 
                    <input type="submit" class="btn btn-success" value="Activate" style="vertical-align: middle" onClick="$('coa').value='activate'" />
					<input type="submit" class="btn btn-danger" value="Delete/Deactivate" style="vertical-align: middle" onClick="$('coa').value='delete'" />
				</div>

				<input type="hidden" name="members" value="1" />
			</form>
			<br />

			<h2 style="margin-top: 10px">Add Members</h2>
			<p>If you would like to add users that already exist in the system to this group yourself, you can do so by clicking the checkbox beside their name from the list below. Once you have reviewed the list at the bottom and are ready, click the <strong>Proceed</strong> button at the bottom to complete the process.</p>

            <form action="<?php echo ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "edit", "type" => "add", "step" => 2)); ?>" method="post" id="add-members-form" class="form-horizontal">
                <div class="row-fluid">
                    <div id="group_name_title"></div>
                </div>
                <div class="row-fluid">
                    <div class="member-add-type" id="existing-member-add-type">
                        <label for="choose-members-btn" class="control-label form-required"><?php echo $translate->_("Select Members"); ?></label>
                        <div class="controls">
                            <button id="choose-members-btn" class="btn btn-search-filter" style="min-width: 220px; text-align: left;"><?php echo $translate->_("Browse All Members"); ?> <i class="icon-chevron-down btn-icon pull-right"></i></button>
                        </div>
                    </div>

                    <div id="group_members_list">
                        <h3>Members to be Added on Submission</h3>
                    </div>
                </div>

                <div class="row-fluid">
                    <div class="pull-right">
                        <input type="submit" class="btn btn-primary" value="Proceed" />
                    </div>
                </div>

                <input type="hidden" id="add_group_id" name="add_group_id" value="" />

                <div id="selected_list_container"></div>
            </form>

            <script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery("#choose-members-btn").advancedSearch({
                        api_url: "<?php echo ENTRADA_URL . "/admin/" . $MODULE . "?section=api-members"; ?>",
						build_selected_filters: false,
						resource_url: ENTRADA_URL,
						filter_component_label: "Users",
						filters: {
                            faculty: {
                                api_params: {
                                    group: "faculty",
									excluded_target_ids: <?php echo $current_members_ids ?  json_encode($current_members_ids) : 0; ?>
                                },
                                label: "<?php echo $translate->_("Faculty"); ?>",
                                data_source: "get-users-by-group"
                            },
                            medtech: {
                                api_params: {
                                    group: "medtech",
									excluded_target_ids: <?php echo $current_members_ids ?  json_encode($current_members_ids) : 0; ?>
                                },
                                label: "<?php echo $translate->_("MEdTech"); ?>",
                                data_source: "get-users-by-group"
                            },
                            resident: {
                                api_params: {
									excluded_target_ids: <?php echo $current_members_ids ?  json_encode($current_members_ids) : 0; ?>
                                },
                                label: "<?php echo $translate->_("Residents"); ?>",
                                data_source: "get-resident-users"
                            },
                            staff: {
                                api_params: {
                                    group: "staff",
									excluded_target_ids: <?php echo $current_members_ids ?  json_encode($current_members_ids) : 0; ?>
                                },
                                label: "<?php echo $translate->_("Staff"); ?>",
                                data_source: "get-users-by-group"
                            },
                            student: {
                                select_all_enabled: true,
                                api_params: {
                                    context: "",
                                    previous_context: "organisation_id",
                                    next_context: "organisation_id",
                                    current_context: "organisation_id",
                                    organisation_id: 0,
                                    group_type: 0,
									excluded_target_ids: <?php echo $current_members_ids ?  json_encode($current_members_ids) : 0; ?>
                                },
                                label: "<?php echo $translate->_("Students"); ?>",
                                data_source: "get-organisations",
                                secondary_data_source: "get-students"
                            }
                        },
                        no_results_text: "<?php echo $translate->_("No Users found matching the search criteria"); ?>",
                        selected_list_container: jQuery("#selected_list_container"),
                        parent_form: jQuery("#add-members-form"),
                        list_data: {
                            selector: "#group_members_list",
                            background_value : "url(../images/list-community.gif) no-repeat scroll 0 4px transparent"
                        },
                        width: 300,
                        async: false
                    });
                });

                function selectgroup(group,name) {
                    $('group_id').value = group;
                    $('select-group-form').submit();
                }

                function showgroup(name,group) {
                    $('group_name_title').update(new Element('div',{'style':'font-size:14px; font-weight:600; color:#153E7E'}).update('Group: '+name));
                    $('add_group_id').value = group;
                }

                function toggleDisabled(el) {
                    try {
                        el.disabled = !el.disabled;
                    } catch(E) {
                    }

                    if (el.childNodes && el.childNodes.length > 0) {
                        for (var x = 0; x < el.childNodes.length; x++) {
                            toggleDisabled(el.childNodes[x]);
                        }
                    }
                }

                function memberChecks() {
                    if ($$('.delchk:checked').length&&!disablestatus) {
                        disablestatus = 1;
                        toggleDisabled($('additions'),true);
                        $('delbutton').style.display = 'block';
                        $('additions').fade({ duration: 0.3, to: 0.25 });
                    } else if (!$$('.delchk:checked').length&&disablestatus) {
                        disablestatus = 0;
                        toggleDisabled($('additions'),false);
                        $('delbutton').style.display = 'none';
                        $('additions').fade({ duration: 0.3, to: 1.0 });
                    }
                }
            </script>
            <?php
		break;
	}
}