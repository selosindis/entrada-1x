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
 * The default file that is loaded when /admin/users is accessed.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
if ((!defined("PARENT_INCLUDED")) || (!defined("IN_AWARDS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("awards", "update", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	require_once("Models/awards/InternalAwards.class.php");
	require_once("Models/awards/InternalAwardReceipts.class.php");
	
	process_manage_award_details();
	$awards = InternalAwards::get(true);

	
					
	$PAGE_META["title"]			= "Awards Listing";
	$PAGE_META["description"]	= "";
	$PAGE_META["keywords"]		= "";

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/ActiveDataEntryProcessor.js\"></script>";
	?>
	<div id="award_messages">
	<?php 
	display_status_messages();
	?>
	</div>
	<h1>Awards Listing</h1>
	
		<?php
			$show_new_award_form = (isset($_GET["show"]) && ($_GET["show"] == "add_new_award") ? true : false);
		?>	

	<div id="add_new_award_link" style="margin-bottom:18px; float: right;<?php if ($show_new_award_form) { echo " display: none;"; } ?>">
			<a id="add_new_award" href="<?php echo ENTRADA_URL; ?>/admin/awards?show=add_new_award" class="btn btn-primary">Add Award</a>
	</div>
	<form class="form-horizontal" id="new_award_form" action="<?php echo ENTRADA_URL; ?>/admin/awards" method="post"<?php echo ((!$show_new_award_form) ? " style=\"display: none;\"" : ""); ?>>
		
		
		<input type="hidden" name="action" value="new_award" />
		<div class="control-group">
			<label for="award_title" class="control-label form-required">Title:</label>
			<div class="controls">
				<input id="award_title" name="award_title" type="text" maxlength="4096"></input>	
			</div>
		</div>
		<div class="control-group">
			<label for="award_title" class="control-label form-required">Terms of Award:</label>
			<div class="controls">
				<textarea id="award_terms" name="award_terms" cols="65" rows="5"></textarea>	
			</div>
		</div>
		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="Add Award" />	
		</div>
		
	</form>
	<div class="clear"></div>
	

	<div class="clear"></div>
	
	<div id="awards_listing">
	<?php echo awards_list($awards); ?>
	</div>
		<script language="javascript">
		var new_award = new ActiveDataEntryProcessor({
			url : '<?php echo webservice_url("awards"); ?>',
			data_destination: $('#awards_listing'),
			new_form: $('#new_award_form'),
			remove_forms_selector: '.remove_award_form',
			new_button: $('#add_new_award_link'),
			hide_button: $('#hide_new_award'),
			messages: $('#award_messages')
		});
	</script>
	<?php
}
?>