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
} elseif (!$ENTRADA_ACL->amIAllowed('annualreport', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[]	= array("url" => "", "title" => "Copy Forward" );
        
    echo "<div id=\"display-error-box\" class=\"display-generic\">\n";
    echo "<strong>Note:</strong> Copying forward should only be used once a year to copy records from the previous year. If records are copied by accident you can simply go back and delete them.";
    echo "</div>";
    if($STEP == 2) {
    	if(isset($_POST["copy_from"]) && $copy_from = clean_input($_POST["copy_from"], array("int"))) {
    		$PROCESSED["copy_from"] = $copy_from;
    	} else {
    		$ERROR++;
    		$ERRORSTR[] = "You must select a year to <strong>Copy From</strong>.";
    	}
    	
    	if(isset($_POST["copy_to"]) && ($copy_to = clean_input($_POST["copy_to"], array("int")))) {
    		$PROCESSED["copy_to"] = $copy_to;
    	} else {
    		$ERROR++;
    		$ERRORSTR[] = "You must select a year to <strong>Copy To</strong>.";
    	}
    	
    	if($_POST["copy_from"] >= $_POST["copy_to"]) {
    		$ERROR++;
    		$ERRORSTR[] = "<strong>Copy To</strong> must be greater than <strong>Copy From</strong>.";
    	}
    	
    	if(!isset($_POST["copy"]) || !is_array($_POST["copy"])) {
			$ERROR++;
			$ERRORSTR[] = "You must select at least one subsection to <strong>Copy</strong>.";
		} else {
			$PROCESSED["copy"] = $_POST["copy"];
		}
		
		if(!$ERROR) {
			foreach($PROCESSED["copy"] as $copy) {
				$getRecordsToCopy = "	SELECT * 
										FROM `".$copy."`
										WHERE `proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
										AND `year_reported` = ".$db->qstr($PROCESSED["copy_from"]);
				
				if($results = $db->GetAll($getRecordsToCopy)) {
					foreach($results as $result) {
						$result["year_reported"] = $PROCESSED["copy_to"];
						$result["updated_date"]	= time();
						$result["updated_by"] = $_SESSION["details"]["id"];
						$result["proxy_id"]	= $_SESSION[APPLICATION_IDENTIFIER]['tmp']['proxy_id'];
						
						// Remove the ID from the array so that the insert can happen as if it were a new record.
						array_shift($result);
						
						if($db->AutoExecute($copy, $result, "INSERT")) {
								$url 	= ENTRADA_URL."/annualreport/tools";
								$msg	= "You will now be redirected to the Tools page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
			
								$SUCCESS++;
								$SUCCESSSTR[]  = "You have successfully copied forward from <strong>". $PROCESSED["copy_from"] ."</strong> to <strong>". $PROCESSED["copy_to"] ."</strong>. <br /><br />".$msg;
								$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000);";
			
								application_log("success", "User ID: ".$_SESSION["details"]["id"]." - Copied forward from ". $PROCESSED["copy_from"] ." to ". $PROCESSED["copy_to"] .".");					
			
						} else {
							$ERROR++;
							$ERRORSTR[] = "There was a problem Copying Forward. The MEdTech Unit was informed of this error; please try again later.";
			
							application_log("error", "There was an error Copying Forward. Database said: ".$db->ErrorMsg());
						}
					}
				}
			}
		} else {
			$STEP = 1;
		}
    }
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
	<?php
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
			?>
		<div class="no-printing">
			<form action="<?php echo ENTRADA_URL; ?>/annualreport/tools?section=<?php echo $SECTION; ?>&step=2" method="post">
			<input type="hidden" name="update" value="1" />
			<table style="width: 100%" cellspacing="0" cellpadding="2" border="0">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 20%" />
				<col style="width: 77%" />
			</colgroup>
			<tbody>
				<tr>
					<td colspan="3"><h2>Copy Forward Options</h2></td>
				</tr>
				<tr>
					<td></td>
					<td><label for="copy_from" class="form-required">Copy From</label></td>
					<td><select name="copy_from" id="copy_from" style="vertical-align: middle">
					<?php
						$getProfileDatesQuery = "	SELECT DISTINCT `year_reported` 
													FROM `ar_profile` 
													WHERE `proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]['tmp']['proxy_id'])."
													ORDER BY `year_reported` DESC";
						if($resutls = $db->GetAll($getProfileDatesQuery)) {
							foreach($resutls as $result) {
								if(isset($PROCESSED["copy_from"]) && $PROCESSED["copy_from"] != '') {
									$defaultFromYear = $PROCESSED["copy_from"];
								}
								echo "<option value=\"".$result["year_reported"]."\"".(($defaultFromYear == $result["year_reported"]) ? " selected=\"selected\"" : "").">".$result["year_reported"]."</option>\n";
							}
						}
						echo "</select>";
					?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td><label for="copy_to" class="form-required">Copy To</label></td>
					<td><select name="copy_to" id="copy_to" style="vertical-align: middle">
					<?php
						// If it is between July and December then allow them to copy forward to THIS year
						// otherwise they need to copy forward to NEXT Year
						if((int)date("m") > 7 && (int)date("m") <= 12) {	
							$copy_to[] = date("Y");
							$copy_to[] = date("Y") + 1;
						} else { 
							$copy_to[] = date("Y", strtotime("-1 year"));
							$copy_to[] = date("Y");
						}
						
						foreach($copy_to as $i) {
							if(isset($PROCESSED["copy_to"]) && $PROCESSED["copy_to"] != '')
							{
								$defaultToYear = $PROCESSED["copy_to"];
							}
							else 
							{
								$defaultToYear = $AR_FUTURE_YEARS;
							}
							echo "<option value=\"".$i."\"".(($defaultToYear == $i) ? " selected=\"selected\"" : "").">".$i."</option>\n";
						}
						echo "</select>";
					?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td><label for="copy" class="form-required">Copy</label></td>
					<td><select name="copy[]" id="copy" multiple style="vertical-align: middle">
					<?php
						echo "<option value=\"ar_undergraduate_nonmedical_teaching\" selected=\"selected\">Undergraduate (Other) Teaching</option>\n";
						echo "<option value=\"ar_graduate_teaching\" selected=\"selected\">Graduate Teaching</option>\n";
						echo "<option value=\"ar_undergraduate_supervision\" selected=\"selected\">Undergraduate Supervision</option>\n";
						echo "<option value=\"ar_graduate_supervision\" selected=\"selected\">Graduate Supervision</option>\n";
						echo "<option value=\"ar_memberships\" selected=\"selected\">Membership on Graduate Examining and Supervisory Committees (Excluding Supervision)</option>\n";
						if($_SESSION["details"]["clinical_member"]) {
							echo "<option value=\"ar_clinical_education\" selected=\"selected\">Education of Clinical Trainees Including Clinical Clerks</option>\n";
						}
						echo "<option value=\"ar_continuing_education\" selected=\"selected\">Continuing Education</option>\n";
						echo "<option value=\"ar_innovation\" selected=\"selected\">Innovation in Education</option>\n";
						echo "<option value=\"ar_other\" selected=\"selected\">Other Education</option>\n";
						if($_SESSION["details"]["clinical_member"]) {
							echo "<option value=\"ar_clinical_activity\" selected=\"selected\">Clinical Activity</option>\n";
							echo "<option value=\"ar_ward_supervision\" selected=\"selected\">Ward Supervision</option>\n";
							echo "<option value=\"ar_clinics\" selected=\"selected\">Clinics</option>\n";
							echo "<option value=\"ar_consults\" selected=\"selected\">In-Hospital Consultations</option>\n";
							echo "<option value=\"ar_on_call\" selected=\"selected\">On-Call Responsibility</option>\n";
							echo "<option value=\"ar_procedures\" selected=\"selected\">Procedures</option>\n";
							echo "<option value=\"ar_other_activity\" selected=\"selected\">Other Professional Activity</option>\n";
							echo "<option value=\"ar_clinical_innovation\" selected=\"selected\">Innovation in Clinical Activity</option>\n";
						}
					    echo "<option value=\"ar_research\" selected=\"selected\">Projects / Grants / Contracts</option>\n";
					    echo "<option value=\"ar_conference_papers\" selected=\"selected\">Invited Lectures / Conference Papers</option>\n";
					    echo "<option value=\"ar_scholarly_activity\" selected=\"selected\">Other Scholarly Activity</option>\n";
					    echo "<option value=\"ar_patent_activity\" selected=\"selected\">Patents</option>\n";
					    echo "<option value=\"ar_internal_contributions\" selected=\"selected\">Service Contributions on Behalf of Queen's University</option>\n";
					    echo "<option value=\"ar_external_contributions\" selected=\"selected\">External Contributions</option>\n";
					    echo "<option value=\"ar_self_education\" selected=\"selected\">Self Education</option>\n";
					    echo "<option value=\"ar_prizes\" selected=\"selected\">Prizes, Honours and Awards</option>\n";
					    echo "<option value=\"ar_profile\" selected=\"selected\">Activity Profile</option>\n";
					    echo "</select>";
					?>
					</td>
				</tr>
				<tr>
					<td colspan="3" style="text-align: right; padding-top: 10px"><input type="submit" class="button" value="Copy Forward" /></td>
				</tr>
			</tbody>
			</table>
			</form>
		</div>
		<?php
		break;
	}
}
?>