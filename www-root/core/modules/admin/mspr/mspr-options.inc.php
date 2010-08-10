<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * This file gives Entrada users the ability to update their user profile.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
if ((!defined("PARENT_INCLUDED")) || (!defined("IN_MSPR_ADMIN"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("mspr", "update", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";
	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
}  else {
	
	require_once("Models/MSPRs.class.php");
	
	$PAGE_META["title"]			= "MSPR Class Options";
	$PAGE_META["description"]	= "";
	$PAGE_META["keywords"]		= "";

	if (isset($_GET['year'])) {
		$year = $_GET['year'];
		if (!is_numeric($year)) {
			unset($year);
		}
	}
	
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/mspr?year=".$year, "title" => "Class of ".$year );
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/mspr?section=mspr-options?year=".$year, "title" => "MSPR Class Options");

	$PROCESSED		= array();
	
	if ((is_array($_SESSION["permissions"])) && ($total_permissions = count($_SESSION["permissions"]) > 1)) {
		$sidebar_html  = "The following individual".((($total_permissions - 1) != 1) ? "s have" : " has")." given you access to their ".APPLICATION_NAME." permission levels:";
		$sidebar_html .= "<ul class=\"menu\">\n";
		foreach ($_SESSION["permissions"] as $proxy_id => $result) {
			if ($proxy_id != $_SESSION["details"]["id"]) {
				$sidebar_html .= "<li class=\"checkmark\"><strong>".html_encode($result["fullname"])."</strong><br /><span class=\"content-small\">Exp: ".(($result["expires"]) ? date("D M d/y", $result["expires"]) : "Unknown")."</span></li>\n";
			}
		}
		$sidebar_html .= "</ul>\n";

		new_sidebar_item("Delegated Permissions", $sidebar_html, "delegated-permissions", "open");
	}

	add_mspr_admin_sidebar($year);
	
	$class_data = MSPRClassData::get($year);
	
	$class_close = $class_data->getClosedTimestamp();
	
	if ($_POST["action"]=="Update Options") {
		$SUCCESS++;
		$SUCCESSSTR[]="MSPR options for the clas of ". $year ." successfully updated.<br /><br />You will be redirected to the MSPR Class page in 5 seconds.";

		display_status_messages();
		if (!$ERROR){
			header( "refresh:5;url=".ENTRADA_URL."/admin/mspr?year=".$year );
			exit; 
		}
	}


	?>
	<h1>MSPR Options for Class of <?php echo $year; ?></h1>
	
	<div class="instructions">
	</div>
	<br />

	<form action="<?php echo ENTRADA_URL; ?>/admin/mspr?section=mspr-options&year=<?php echo $year; ?>" method="post">
		<table class="mspr_form">
			<colgroup>
				<col width="3%"></col>
				<col width="25%"></col>
				<col width="72%"></col>
			</colgroup>
			<tfoot>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
						<input type="submit" class="button" name="action" value="Update Options" />
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
					$current_time = ($class_close) ? $class_close : 0;
					echo generate_calendar("close_datetime","Submission Deadline:",true,$current_time,true,false,false,false,false);
				?>
			</tbody>
		</table>	
	</form>
	<?php 
}
