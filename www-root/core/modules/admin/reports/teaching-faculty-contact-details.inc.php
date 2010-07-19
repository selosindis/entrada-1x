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
 * This file is used to add events to the entrada.events table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_REPORTS"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->amIAllowed('report', 'read', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]." and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[]	= array("url" => "", "title" => "Teaching Faculty Contact Details");
	?>
	<div class="no-printing">
		<form action="<?php echo ENTRADA_URL; ?>/admin/reports?section=<?php echo $SECTION; ?>&step=2" method="post">
			<table style="width: 100%" cellspacing="0" cellpadding="2" border="0">
				<colgroup>
					<col style="width: 3%" />
					<col style="width: 20%" />
					<col style="width: 77%" />
				</colgroup>
				<tbody>
					<tr>
						<td colspan="3"><h2>Reporting Dates</h2></td>
					</tr>
					<?php echo generate_calendars("reporting", "Reporting Date", true, true, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"], true, true, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]); ?>
					<tr>
						<td style="vertical-align: top;"><input id="organisation_checkbox" type="checkbox" disabled="disabled" checked="checked"></td>
						<td style="vertical-align: top; padding-top: 4px;"><label for="organisation_id" class="form-required">Organisation</label></td>
						<td style="vertical-align: top;">
							<select id="organisation_id" name="organisation_id" style="width: 177px">
								<?php
								$query		= "SELECT `organisation_id`, `organisation_title` FROM `".AUTH_DATABASE."`.`organisations`";
								$results	= $db->GetAll($query);
								$all_organisations = false;
								if($results) {
									$all_organisations = true;
									foreach($results as $result) {
										if($ENTRADA_ACL->amIAllowed('resourceorganisation'.$result["organisation_id"], 'read')) {
											echo "<option value=\"".(int) $result["organisation_id"]."\"".(((isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"])) && ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] == $result["organisation_id"])) ? " selected=\"selected\"" : "").">".html_encode($result["organisation_title"])."</option>\n";
										} else {
											$all_organisations = false;
										}
									}
								}
								if($all_organisations) {
									?>
									<option value="-1" <?php echo (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"]) && ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] == -1) ? " selected=\"selected\"" : ""); ?>>All organisations</option>
									<?php
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="3" style="text-align: right; padding-top: 10px"><input type="submit" class="button" value="Create Report" /></td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	<?php
	if ($STEP == 2) {
		$output		= array();
		$appendix	= array();
		
		$terms_included		= array("1" => "Term 1", "2" => "Term 2", "2A" => "Phase 2A", "2B" => "Phase 2B", "2C" => "Phase 2C", "2E" => "Phase 2E", "3" => "Phase 3");
		
		$eventtype_legend	= array();
		
		echo "<h1>Teaching Faculty Contact Details</h1>";
		echo "<div class=\"content-small\" style=\"margin-bottom: 10px\">\n";
		echo "	<strong>Date Range:</strong> ".date(DEFAULT_DATE_FORMAT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." <strong>to</strong> ".date(DEFAULT_DATE_FORMAT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]).".";
		echo "</div>\n";

		if(isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"]) && $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] != -1) {
			$organisation_where = " AND (b.`organisation_id` = ".$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"].") ";
		} else {
			$organisation_where = "";
		}
		
		foreach ($terms_included as $term => $term_title) {
			echo "<h2>".$term_title."</h2>";
			
			$query		= "	SELECT d.`firstname`, d.`lastname`, d.`email`, COUNT(*) AS `total`
							FROM `events` AS a
							LEFT JOIN `courses` AS b
							ON b.`course_id` = a.`course_id`
							LEFT JOIN `event_contacts` AS c
							ON a.`event_id` = c.`event_id`
							LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS d
							ON d.`id` = c.`proxy_id`
							WHERE (a.`event_start` BETWEEN ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." AND ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]).")
							AND b.`course_active` = '1'
							AND a.`event_phase` = ".$db->qstr($term).
							$organisation_where."
							GROUP BY c.`proxy_id`
							ORDER BY d.`lastname` ASC, d.`firstname` ASC";
			$results	= $db->GetAll($query);
			if ($results) {
				?>
				<table class="tableList" cellspacing="0" summary="Teaching Faculty Contact Details">
				<colgroup>
					<col class="general" />
					<col class="general" />
					<col class="title" />
					<col class="general" />
				</colgroup>
				<thead>
					<tr>
						<td class="general" style="border-left: 1px #666 solid">Firstname</td>
						<td class="general">Lastname</td>
						<td class="title">E-Mail Address</td>
						<td class="general">Events Taught</td>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ($results as $result) {
					if ((bool) $result["email"]) {
						echo "<tr>\n";
						echo "	<td class=\"general\">".$result["firstname"]."</td>\n";
						echo "	<td class=\"general\">".$result["lastname"]."</td>\n";
						echo "	<td class=\"title\">".$result["email"]."</td>\n";
						echo "	<td class=\"general\">".$result["total"]."</td>\n";
						echo "</tr>\n";
					}
				}
				?>
				</tbody>
				</table>
				<?php				
			} else {
				echo display_notice(array("There are no learning events in the system during the timeframe you have selected."));	
			}
		}
	}
}
?>