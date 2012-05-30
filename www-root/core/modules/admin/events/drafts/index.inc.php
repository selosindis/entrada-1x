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
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
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
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/events/drafts", "title" => "My Draft Learning Events");
	?>
	<h1>My Draft Event Schedules</h1>
	<div style="float: right">
		<ul class="page-action">
			<li class="last"><a class="strong-green" href="<?php echo ENTRADA_URL; ?>/admin/events/drafts?section=create-draft">Create New Draft</a></li>
		</ul>
	</div>
	<div style="clear: both"></div>
	<?php
	// fetch the list of draft ids for the user
	$query = "	SELECT `draft_id`
				FROM `draft_creators`
				WHERE `proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]);
	$my_drafts = $db->GetArray($query);
	if (!empty($my_drafts)) {
		foreach ($my_drafts as $draft) {
			$drafts[] = $draft["draft_id"];
		}
		$query = "	SELECT *
					FROM `drafts`
					WHERE `draft_id` IN ('".implode("', '", $drafts)."')
					ORDER BY `created` ASC";
		$drafts = $db->GetAll($query); 
		?>
	
			<form name="frmSelect" action="<?php echo ENTRADA_URL; ?>/admin/events/drafts?section=delete" method="post">

			<div class="tableListTop">
				<img src="<?php echo ENTRADA_URL; ?>/images/lecture-info.gif" width="15" height="15" alt="" title="" style="vertical-align: middle" />
				You are currently working on <strong><?php echo (count($drafts) <= 1) ? count($drafts) : "no" ; ?></strong> draft<?php echo (count($drafts) < 1) ? "s" : "" ; ?>.
			</div>
			<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Events">
				<colgroup>
					<col class="modified" />
					<col class="name" />
					<col class="date" />
					<col class="status" />
				</colgroup>
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="name<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "name") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("name", "Draft Name"); ?></td>
						<td class="date<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "date") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("duration", "Created"); ?></td>
						<td class="status<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "status") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("term", "Status"); ?></td>
					</tr>
				</thead>
				<?php if ($ENTRADA_ACL->amIAllowed("event", "delete", false) or $ENTRADA_ACL->amIAllowed("event", "create", false)) : ?>
				<tfoot>
					<tr>
						<td></td>
						<td style="padding-top: 10px" colspan="2">
							<?php
							if ($ENTRADA_ACL->amIAllowed("event", "delete", false)) {
								?>
								<input type="submit" class="button" value="Delete Selected" />
								<?php
							} ?>
						</td>
						<td style="padding-top: 10px; text-align: right" colspan="1">
							<?php
							if ($ENTRADA_ACL->amIAllowed("event", "delete", false)) {
								?>
								<input type="submit" class="button" value="Re-open Drafts"  onClick="document.frmSelect.action ='<?php echo ENTRADA_URL; ?>/admin/events/drafts?section=status&action=reopen'" />
								<input type="submit" class="button" value="Approve Drafts"  onClick="document.frmSelect.action ='<?php echo ENTRADA_URL; ?>/admin/events/drafts?section=status&action=approve'" />
								<?php
							}
							?>
						</td>
					</tr>
				</tfoot>
				<?php endif; ?>
				<tbody>
				<?php

				$count_modified = 0;

				foreach ($drafts as $draft) {
					echo "<tr id=\"draft-".$draft["draft_id"]."\" rel=\"".$draft["draft_id"]."\" class=\"draft".((!$url) ? " np" : ((!$accessible) ? " na" : ""))."\">\n";
					echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"checked[]\" value=\"".$draft["draft_id"]."\" /></td>\n";
					echo "	<td class=\"name\">".(($draft["status"] == "open") ? "<a href=\"".$url."?section=edit&draft_id=".$draft["draft_id"]."\" title=\"Draft Name\">" : "") .$draft["name"].(($draft["status"] == "open") ? "</a>" : "" )."</td>\n";
					echo "	<td class=\"date\">".(($draft["status"] == "open") ? "<a href=\"".$url."?section=edit&draft_id=".$draft["draft_id"]."\" title=\"Duration\">" : "").date("Y-m-d", $draft["created"]).(($draft["status"] == "open") ? "</a>" : "")."</td>\n";
					echo "	<td class=\"status\">".(($draft["status"] == "open") ? "<a href=\"".$url."?section=edit&draft_id=".$draft["draft_id"]."\" title=\"Draft Status\">" : "").$draft["status"].(($draft["status"] == "open") ? "</a>" : "")."</td>\n";
					echo "</tr>\n";
				}
				?>
				</tbody>
			</table>

			</form>
	
		<?php
	} else {
		add_notice("<h3>No draft schedules</h3>You are not currently working on any draft schedules. You can create a new draft schedule or have a user add you to an existing one.");
		echo display_notice();
	}
}