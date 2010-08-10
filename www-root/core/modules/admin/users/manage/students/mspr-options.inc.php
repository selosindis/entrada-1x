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
if (!defined("IN_MANAGE_USER_STUDENTS")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->isLoggedInAllowed('mspr', 'update',true) || $user_record["group"] != "student") {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";
	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
}  else {
	
	require_once("Models/MSPRs.class.php");
	$PROXY_ID					= $user_record["id"];
	$user = User::get($user_record["id"]);
	
	$PAGE_META["title"]			= "MSPR Options";
	$PAGE_META["description"]	= "";
	$PAGE_META["keywords"]		= "";

	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/users/manage/students?section=mspr&id=".$PROXY_ID, "title" => "MSPR");
	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/users/manage/students?section=mspr-options&id=".$PROXY_ID, "title" => "MSPR Options");

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

	
	$mspr = MSPR::get($user);
	$year = $user->getGradYear();
	$class_data = MSPRClassData::get($year);
	
	$class_close = $class_data->getClosedTimestamp();
	$mspr_close = $mspr->getClosedTimestamp();
	
	add_mspr_management_sidebar();
	switch($step) {
		case "processing":
			$SUCCESS++;
			$SUCCESSSTR[]="MSPR options for ". $user->getFullname() ." successfully updated.<br /><br />You will be redirected to the MSPR page in 5 seconds.";
	
			display_status_messages();
			if (!$ERROR){
				header( "refresh:5;url=".ENTRADA_URL."/admin/users/manage/students?section=mspr&id=".$PROXY_ID );
				break; // only break if there are no errors
			}
		default:	
		?>
		<h1>MSPR Options for <?php echo $user->getFullname(); ?></h1>
		
		<div class="instructions">
			<strong>Instructions:</strong><p>To set a custom deadline, check the box on the left, and specify the date and time in the fields on the right. To restore the default, uncheck the box on the left.</p>
			<p><strong>Note: </strong>Although students may have custom deadlines for MSPR submissions, this is only intended to be used in extraordinary circumstances. </p>
		</div>
		<br />
		<p>Class of <?php echo $year; ?> default submission deadline: <?php echo ($class_close ? date("F j, Y \a\\t g:i a",$class_close) : "Unset"); ?></p>

		<form action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr-options&id=<?php echo $PROXY_ID; ?>" method="post">
			<input type="hidden" name="user_id" value="<?php echo $PROXY_ID; ?>"></input>
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
						$current_time = ($class_close != $mspr_close) ? $mspr_close : 0;
						echo generate_calendar("close_datetime","Custom Submission Deadline:",false,$current_time,true,false,false);
					?>
				</tbody>
			</table>	
		</form>
		<?php 
	}
}
