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
 * @author Developer: Andrew Dos-Santos <ad29@queensu.ca>
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

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	/**
	 * Add PlotKit to the beginning of the $HEAD array.
	 */
	array_unshift($HEAD,
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/MochiKit/MochiKit.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/excanvas.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/Base.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/Layout.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/Canvas.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/SweetCanvas.js\"></script>"
		);
			
	$BREADCRUMB[]	= array("url" => "", "title" => "Grant Eligible - Total Hours" );
	$province 	= clean_input($_POST["prov_state"], array("notags", "specialchars"));
	$grad_year	= clean_input($_POST["grad_year"], array("notags", "specialchars"));
	?>
	<style type="text/css">
	h1 {
		page-break-before:	always;
		border-bottom:		2px #CCCCCC solid;
		font-size:			24px;
	}
	
	h2 {
		font-weight:		normal;
		border:				0px;
		font-size:			18px;
	}
	
	div.top-link {
		float: right;
	}
	</style>
	<a name="top"></a>
	<div class="no-printing">
		<form action="<?php echo ENTRADA_URL; ?>/admin/reports?section=<?php echo $SECTION; ?>&step=2" method="post">
		<input type="hidden" name="update" value="1" />
		<table style="width: 100%" cellspacing="0" cellpadding="2" border="0">
		<colgroup>
			<col style="width: 3%" />
			<col style="width: 20%" />
			<col style="width: 77%" />
		</colgroup>
		<tbody>
			<tr>
				<td colspan="3"><h2>Report Options</h2></td>
			</tr>
			<?php echo generate_calendars("reporting", "Reporting Date", true, true, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"], true, true, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]); ?>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" style="vertical-align: middle"><label for="prov_state" class="form-required">Electives Outside of:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				
				<?php
					$query		= "SELECT `province` FROM `global_lu_provinces` WHERE `country_id` = ".$db->qstr(DEFAULT_COUNTRY_ID);
					$results	= $db->GetAll($query);
					if ($results) {
						echo "<select id=\"prov_state\" name=\"prov_state\" style=\"width: 177px\">\n";
						foreach($results as $result) {
							echo "<option value=\"".$result["province"]."\"".(($province == $result["province"]) ? " selected=\"selected\"" : ((!isset($province) || $province == "") && $result["province"] == "Ontario") ? " selected=\"selected\"" : "").">".clean_input($result["province"], array("notags", "specialchars"))."</option>\n";
						}
						echo "</select>\n";
					}
					?>
				</td>
			</tr>
			<tr>
				<td colspan="3" style="vertical-align: middle"><label for="grad_year" class="form-required">Grad Year:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				
				<?php
					$query		= "SELECT DISTINCT `audience_value` FROM `event_audience` WHERE `audience_type` = \"grad_year\" ORDER BY `audience_value` DESC";
					$results	= $db->GetAll($query);
					if ($results) {
						echo "<select id=\"grad_year\" name=\"grad_year\" style=\"width: 177px\">\n";
						foreach($results as $result) {
							echo "<option value=\"".$result["audience_value"]."\"".(($grad_year == $result["audience_value"]) ? " selected=\"selected\"" : "").">".clean_input($result["audience_value"], array("notags", "specialchars"))."</option>\n";
						}
						echo "</select>\n";
					}
					?>
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
		
		echo "<h1>Grant Eligible (Outside of ".$province.") for the class of: ".$grad_year."</h1>";
		echo "<div class=\"content-small\" style=\"margin-bottom: 10px\">\n";
		echo "	<strong>Date Range:</strong> ".date(DEFAULT_DATE_FORMAT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." <strong>to</strong> ".date(DEFAULT_DATE_FORMAT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]).".";
		echo "</div>\n";
		
		$query		= "	SELECT c.`firstname`, c.`lastname`, c.`email`, a.`event_start`, a.`event_finish`, b.`etype_id`
						FROM `".CLERKSHIP_DATABASE."`.`events` AS a
						LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
						ON a.`event_id` = b.`event_id`
						LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS c
						ON c.`id` = b.`etype_id`
						LEFT JOIN `".CLERKSHIP_DATABASE."`.`electives` AS d
						ON a.`event_id` = d.`event_id`
						LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS e 
						ON c.`id` = e.`user_id`
						WHERE ((a.`event_start` BETWEEN ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." AND ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]).")
						OR (a.`event_finish` BETWEEN ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." AND ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"])."))
						AND a.`event_status` = \"published\"
						AND a.`event_type` = \"elective\"
						AND c.`id` IS NOT NULL
						AND d.`prov_state` != ".$db->qstr($province)."
						AND e.`role` = ".$db->qstr($grad_year)."
						AND e.`app_id` = ".$db->qstr(AUTH_APP_ID)."
						ORDER BY c.`lastname` ASC, c.`firstname` ASC";
		
		$results	= $db->GetAll($query);
		
		if ($results) {
			?>
			<table class="tableList" cellspacing="0" summary="Grant Eligible - Total Hours Details">
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
					<td class="general">Weeks</td>
				</tr>
			</thead>
			<tbody>
			<?php
			
			$previousClerk 	= "";
			$difference		= 0;
			$weeks			= 0;
			$totalWeeks		= 0;
			$clerksArray	= array();
			
			foreach ($results as $result) {
				if(isset($clerksArray[$result["etype_id"]]["weeks"])) {
					$difference		= ($result["event_finish"] - $result["event_start"]) / 604800;
					$weeks			= ceil($difference);
					
					$clerksArray[$result["etype_id"]]["weeks"] += $weeks;
					$totalWeeks += $weeks;					
				} else {
					$difference		= ($result["event_finish"] - $result["event_start"]) / 604800;
					$weeks			= ceil($difference);
					
					$clerksArray[$result["etype_id"]]["firstname"] 	= $result["firstname"];
					$clerksArray[$result["etype_id"]]["lastname"] 	= $result["lastname"];
					$clerksArray[$result["etype_id"]]["email"] 		= $result["email"];
					$clerksArray[$result["etype_id"]]["weeks"] 		= $weeks;
					
					$totalWeeks += $weeks;
				}
			}
			
			foreach($clerksArray as $result) {
				echo "<tr>\n";
				echo "	<td class=\"general\">".$result["firstname"]."</td>\n";
				echo "	<td class=\"general\">".$result["lastname"]."</td>\n";
				echo "	<td class=\"title\">".$result["email"]."</td>\n";
				echo "	<td class=\"title\">".$result["weeks"]."</td>\n";
				echo "</tr>\n";
			}
			
			$query		= "	SELECT DISTINCT(etype_id)
						FROM `".CLERKSHIP_DATABASE."`.`events` AS a
						LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
						ON a.`event_id` = b.`event_id`
						LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS c
						ON c.`id` = b.`etype_id`
						LEFT JOIN `".CLERKSHIP_DATABASE."`.`electives` AS d
						ON a.`event_id` = d.`event_id`
						LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS e 
						ON c.`id` = e.`user_id`
						WHERE ((a.`event_start` BETWEEN ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." AND ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]).")
						OR (a.`event_finish` BETWEEN ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." AND ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"])."))
						AND a.`event_status` = \"published\"
						AND c.`id` IS NOT NULL
						AND a.`event_type` = \"elective\"
						AND d.`prov_state` != ".$db->qstr($province)." 
						AND e.`role` = ".$db->qstr($grad_year)."
						AND e.`app_id` = ".$db->qstr(AUTH_APP_ID);
			
			$results	= $db->GetAll($query);
			
			$query		= "	SELECT COUNT(a.event_id) AS total
						FROM `".CLERKSHIP_DATABASE."`.`events` AS a
						LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
						ON a.`event_id` = b.`event_id`
						LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS c
						ON c.`id` = b.`etype_id`
						LEFT JOIN `".CLERKSHIP_DATABASE."`.`electives` AS d
						ON a.`event_id` = d.`event_id`
						LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS e 
						ON c.`id` = e.`user_id`
						WHERE ((a.`event_start` BETWEEN ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." AND ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]).")
						OR (a.`event_finish` BETWEEN ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." AND ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"])."))
						AND a.`event_status` = \"published\"
						AND c.`id` IS NOT NULL
						AND a.`event_type` = \"elective\"
						AND d.`prov_state` != ".$db->qstr($province)." 
						AND e.`role` = ".$db->qstr($grad_year)."
						AND e.`app_id` = ".$db->qstr(AUTH_APP_ID);
			
			$result	= $db->GetRow($query);
			
			$totalHours = count($results) * $totalWeeks * 40;
			
			?>
			</tbody>
			</table>
			<?php	
			echo "<h2>Electives: ".$result["total"]."</h1>";			
			echo "<h2>Weeks: ".$totalWeeks."</h1>";			
			echo "<h2>Hours: ".$totalHours."</h1>";			
		} else {
			echo display_notice(array("There are no electives in the system during the timeframe you have selected."));	
		}
	}
}
?>