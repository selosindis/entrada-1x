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
 * @author Developer: Andrew Dos-Santos <andrew.dos-santos@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
if((!defined("PARENT_INCLUDED")) || (!defined("IN_ANNUAL_REPORT"))) {
	exit;
} else if((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL.((isset($_SERVER["REQUEST_URI"])) ? "?url=".rawurlencode(clean_input($_SERVER["REQUEST_URI"], array("nows", "url"))) : ""));
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('annualreport', 'update')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	$EXTERNAL_CONTRIBUTIONS_ID = $_GET["rid"];
	
	// This grid should be expanded upon redirecting back to the academic index.
	$_SESSION["academic_expand_grid"] = "external_grid";
	
	if($EXTERNAL_CONTRIBUTIONS_ID) {
		$query	= "SELECT * FROM `ar_external_contributions` WHERE `external_contributions_id`=".$db->qstr($EXTERNAL_CONTRIBUTIONS_ID);
		$result	= $db->GetRow($query);
		if($result) {
			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/annualreport/academic?section=edit_external", "title" => "Edit Contributions to External Organisations / International Development Projects");
			
			echo "<h1>Edit Contributions to External Organisations / International Development Projects</h1>\n";

			// Error Checking
			switch($STEP) {
				case 2 :					
					/**
					 * Required field "organisation" / Organisation
					 */
					if((isset($_POST["organisation"])) && ($organisation = clean_input($_POST["organisation"], array("notags", "trim")))) {
						$PROCESSED["organisation"] = $organisation;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The <strong>Organisation</strong> field is required.";
					}
					/**
					 * Required field "city_country" / City Country			 
					 */
					if((isset($_POST["city_country"])) && ($city_country = clean_input($_POST["city_country"], array("notags", "trim")))) {
						$PROCESSED["city_country"] = $city_country;
						if(count(explode(",", $city_country)) < 2)
						{
							$ERROR++;
							$ERRORSTR[] = "The <strong>City Country</strong> field must be formatted as follows: City<strong>, </strong>Country.";
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "The <strong>City Country</strong> field is required.";
					}
					/**
					 * Required field "description" / Description			 
					 */
					$PROCESSED["description"] = clean_input($_POST["description"], array("notags", "trim"));
					if((isset($_POST["description"])) && ($description = clean_input($_POST["description"], array("notags", "trim"))) && strlen(trim($_POST["description"])) < 300) {
						$PROCESSED["description"] = $description;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The <strong>Description</strong> field is required.";
					}
					/**
					 * Required field "days_of_year" / Days/Year.
					 */
					if((isset($_POST["days_of_year"])) && ($days_of_year = clean_input($_POST["days_of_year"], array("int")))) {
						$PROCESSED["days_of_year"] = $days_of_year;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The <strong>Days/Year</strong> field is required.";
					}
					/**
					 * Required field "year_reported" / Year Reported.
					 */
					if((isset($_POST["year_reported"])) && ($year_reported = clean_input($_POST["year_reported"], array("int")))) {
						$PROCESSED["year_reported"] = $year_reported;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The <strong>Year Reported</strong> field is required.";
					}
					
					if(isset($_POST["post_action"])) {
						switch($_POST["post_action"]) {							
							case "new" :
								$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "new";
							break;
							case "index" :
							default :
								$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "index";
							break;
						}
					} else {
						$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "index";
					}
					
					if(!$ERROR) {
						$PROCESSED["updated_date"]	= time();
						$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];
						$PROCESSED["proxy_id"]		= $_SESSION[APPLICATION_IDENTIFIER]['tmp']['proxy_id'];
						
						if($db->AutoExecute("ar_external_contributions", $PROCESSED, "UPDATE", "`external_contributions_id`=".$db->qstr($EXTERNAL_CONTRIBUTIONS_ID))) {
								switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
									case "new" :
										$url 	= ENTRADA_URL."/annualreport/academic?section=add_external";
										$msg	= "You will now be redirected to add more Contributions to External Organisations / International Development Projects; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
									case "index" :
									default :
										$url 	= ENTRADA_URL."/annualreport/academic";
										$msg	= "You will now be redirected to the academic page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
								}
		
								$SUCCESS++;
								$SUCCESSSTR[]  = "You have successfully edited <strong>".html_encode($PROCESSED["organisation"])."</strong> in the system.<br /><br />".$msg;
								$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000);";
		
								application_log("success", "Edited Contributions to External Organisations / International Development Projects [".$EXTERNAL_CONTRIBUTIONS_ID."] in the system.");					
		
						} else {
							$ERROR++;
							$ERRORSTR[] = "There was a problem editing this Contributions to External Organisations / International Development Projects record in the system. The MEdIT Unit was informed of this error; please try again later.";
		
							application_log("error", "There was an error editing the Contributions to External Organisations / International Development Projects. Database said: ".$db->ErrorMsg());
						}
					} else {
						$STEP = 1;
					}
				break;
				case 1 :
				default :
					continue;
				break;
			}
			
			// Display Content
			switch($STEP) {
				case 2 :
					if($SUCCESS) {
						echo display_success();
					}
					if($NOTICE) {
						echo display_notice();
					}
					if($ERROR) {
						echo display_error();
					}
				break;
				case 1 :
				default :
					if(!isset($PROCESSED) || count($PROCESSED) <= 0)
					{
						$externalQuery = "SELECT * FROM `ar_external_contributions` WHERE `external_contributions_id` =".$db->qstr($EXTERNAL_CONTRIBUTIONS_ID);						
						$externalResult = $db->GetRow($externalQuery);
					}
					
					if($ERROR) {
						echo display_error();
					}
					$ONLOAD[]					= "setMaxLength();";
					?>
					Describe the nature and number of days for each of the following activities involving the application of professional effort and expertise on behalf of organizations or communities external to the University:<br /><br />
					<?php if($_SESSION["details"]["clinical_member"]) { ?>
					<font size="1">(i)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Activity, either alone or in combination with other activities, exceeds 20% of the time required by the Member's full-time academic duties.<br /> 
					(ii)&nbsp;&nbsp;&nbsp;&nbsp;Any activity, for which prior permission described in the Collective Agreement Section 19.4.2, has been granted.<br />
					(iii)&nbsp;&nbsp;&nbsp;Teaching at another university or institution<br />
					(iv)&nbsp;&nbsp;&nbsp;Consulting and entrepreneurial activities.<br />
					(v)&nbsp;&nbsp;&nbsp;&nbsp;Activities that are service commitments to an outside body, association, or group.<br /></font>
					<?php } else { ?>
					<font size="1">(i)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Activity, either alone or in combination with other activities, exceeds 20% of the time required by the Member's full-time academic duties.<br /> 
					(ii)&nbsp;&nbsp;&nbsp;Teaching at another university or institution<br />
					(iii)&nbsp;&nbsp;&nbsp;Consulting and entrepreneurial activities.<br />
					(iv)&nbsp;&nbsp;&nbsp;&nbsp;Activities that are service commitments to an outside body, association, or group.<br /></font>
					<?php } ?>
					<form action="<?php echo ENTRADA_URL; ?>/annualreport/academic?section=edit_external&amp;step=2&amp;rid=<?php echo $EXTERNAL_CONTRIBUTIONS_ID;?>" method="post">
					<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Contributions to External Organisations / International Development Projects">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<tr>
						<td colspan="3"><h2>Details:</h2></td>
					</tr>
					<tr>
						<td></td>
						<td><label for="organisation" class="form-required">Organisation</label></td>
						<td><input type="text" id="organisation" name="organisation" value="<?php echo ((isset($externalResult["organisation"])) ? html_encode($externalResult["organisation"]) : html_encode($PROCESSED["organisation"])); ?>" maxlength="255" style="width: 95%" /></td>
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align: top"><label for="city_country" class="form-required">City, Country</label></td>
						<td><input type="text" id="city_country" name="city_country" value="<?php echo ((isset($externalResult["city_country"])) ? html_encode($externalResult["city_country"]) : html_encode($PROCESSED["city_country"])); ?>" maxlength="255" style="width: 95%" /></td>
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align: top"><label for="description" class="form-required">Description</label></td>				
						<td><textarea id="description" name="description" style="width: 95%" rows="4" maxlength="300"><?php echo ((isset($externalResult["description"])) ? html_encode($externalResult["description"]) : html_encode($PROCESSED["description"])); ?></textarea></td>
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align: top"><label for="days_of_year" class="form-required">Days/Year</label></td>
						<td><input type="text" id="days_of_year" name="days_of_year" value="<?php echo ((isset($externalResult["days_of_year"])) ? html_encode($externalResult["days_of_year"]) : html_encode($PROCESSED["days_of_year"])); ?>" maxlength="3" style="width: 40px" /></td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td></td>
						<td><label for="year_reported" class="form-required">Report Year</label></td>
						<td><select name="year_reported" id="year_reported" style="vertical-align: middle">
						<?php
							for($i=$AR_PAST_YEARS; $i<=$AR_FUTURE_YEARS; $i++)
							{
								if(isset($PROCESSED["year_reported"]) && $PROCESSED["year_reported"] != '')
								{
									$defaultYear = $PROCESSED["year_reported"];
								}
								else if(isset($externalResult["year_reported"]) && $externalResult["year_reported"] != '')
								{
									$defaultYear = $externalResult["year_reported"];
								}
								else 
								{
									$defaultYear = $AR_CUR_YEAR;
								}
								echo "<option value=\"".$i."\"".(($defaultYear == $i) ? " selected=\"selected\"" : "").">".$i."</option>\n";
							}
							echo "</select>";
						?>
						</td>
					</tr>
					
					<tr>
						<td colspan="3" style="padding-top: 25px">
							<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td style="width: 25%; text-align: left">
									<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/annualreport/academic'" />	
								</td>
								<td style="width: 75%; text-align: right; vertical-align: middle">
									<span class="content-small">After saving:</span>
									<select id="post_action" name="post_action">							
									<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "add") ? " selected=\"selected\"" : ""); ?>>Add More Service</option>
									<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to Service list</option>
									</select>
									<input type="submit" class="button" value="Save" />
								</td>
							</tr>
							</table>
						</td>
					</tr>
					</table>					
					</form>
					<br /><br />
					<?php
				break;
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to edit a Contributions to External Organisations / International Development Projects record you must provide a valid Contributions to External Organisations / International Development Projects identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide a valid Contributions to External Organisations / International Development Projects identifer when attempting to edit a Contributions to External Organisations / International Development Projects record.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit a Contributions to External Organisations / International Development Projects record you must provide the Contributions to External Organisations / International Development Projects identifier.";

		echo display_error();

		application_log("notice", "Failed to provide Contributions to External Organisations / International Development Projects identifer when attempting to edit a Contributions to External Organisations / International Development Projects record.");
	}
}
?>