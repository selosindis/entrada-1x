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
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_NOTICES"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->amIAllowed('notice', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if(isset($_GET["notice_id"]) && $NOTICE_ID = (int)$_GET["notice_id"]){
		$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/notices?".replace_query(array("section" => "edit","id"=>$NOTICE_ID)), "title" => "Editing Notice");
		$BREADCRUMB[]	= array("url" => "", "title" => "Notice Statistics");	
		$ORG_ID = $db->GetOne("SELECT `organisation_id` FROM `notices` WHERE `notice_id` = ".$db->qstr($NOTICE_ID));
		
		$query = "SELECT * FROM `statistics` WHERE `module` = 'notices' AND `action` = 'read' AND `action_field` = 'notice_id' AND `action_value` = ".$db->qstr($NOTICE_ID);
		$reads = $db->GetAll($query);
		$read_users = array();
		if($reads){
			foreach($reads as $read){
				$read_users["proxy_id"][] = $read["proxy_id"];
				$read_users["timestamp"][] = $read["timestamp"];
			}
		}
		
		$query = "SELECT * FROM `notice_audience` WHERE `notice_id` = ".$db->qstr($NOTICE_ID);
		$audience_members = $db->GetAll($query);
		$audience = array();
		if($audience_members){
			foreach($audience_members as $member){
				switch($member["audience_type"]){
					case 'cohorts':
					case 'course_list':
						$query = "SELECT a.*, CONCAT_WS(', ',b.`lastname`,b.`firstname`) as `fullname` FROM `group_members` AS a JOIN `".AUTH_DATABASE."`.`user_data` AS b ON a.`proxy_id` = b.`id` WHERE `group_id` = ".$db->qstr($member["audience_value"]);
						$group_mmbrs = $db->GetAll($query);
						if($group_mmbrs){
							foreach($group_mmbrs as $gmember){
								$audience[$gmember["proxy_id"]] = $gmember["fullname"];
							}
						}
						break;
					case 'staff':
					case 'faculty':
					case 'students':
						$query = "SELECT CONCAT_WS(', ',`lastname`,`firstname`) as `fullname` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($member["audience_value"]);
						$fullname = $db->GetOne($query);
						if($fullname){
							$audience[$member["audience_value"]] = $fullname;
						}
						break;
					case 'all:faculty':
						$query = "SELECT CONCAT_WS(', ',a.`lastname`,a.`firstname`) AS `fullname`,a.`id` AS `proxy_id` FROM `".AUTH_DATABASE."`.`user_data` AS a JOIN `".AUTH_DATABASE."`.`user_access` AS b ON a.`id` = b.`user_id` WHERE b.`group` = 'faculty' AND (b.`access_expires` = 0 OR b.`access_expires` > ".time().") AND b.`app_id` = ".$db->qstr($ORG_ID);
						$users = $db->GetAll($query);
						if($users){
							foreach($users as $user){
								$audience[$user["proxy_id"]] = $user["fullname"];
							}
						}
						break;
					case 'all:staff':
						$query = "SELECT CONCAT_WS(', ',a.`lastname`,a.`firstname`) AS `fullname`,a.`id` AS `proxy_id` FROM `".AUTH_DATABASE."`.`user_data` AS a JOIN `".AUTH_DATABASE."`.`user_access` AS b ON a.`id` = b.`user_id` WHERE b.`group` IN ('medtech','staff') AND (b.`access_expires` = 0 OR b.`access_expires` > ".time().") AND b.`app_id` = ".$db->qstr($ORG_ID);
						$users = $db->GetAll($query);
						if($users){
							foreach($users as $user){
								$audience[$user["proxy_id"]] = $user["fullname"];
							}
						}						
						break;
					case 'all:students':
						$query = "SELECT CONCAT_WS(', ',a.`lastname`,a.`firstname`) AS `fullname`,a.`id` AS `proxy_id` FROM `".AUTH_DATABASE."`.`user_data` AS a JOIN `".AUTH_DATABASE."`.`user_access` AS b ON a.`id` = b.`user_id` WHERE b.`group` = 'student' AND (b.`access_expires` = 0 OR b.`access_expires` > ".time().") AND b.`app_id` = ".$db->qstr($ORG_ID);
						$users = $db->GetAll($query);
						if($users){
							foreach($users as $user){
								$audience[$user["proxy_id"]] = $user["fullname"];
							}
						}						
						break;
					case 'all:users':
						$query = "SELECT CONCAT_WS(', ',a.`lastname`,a.`firstname`) AS `fullname`,a.`id` AS `proxy_id` FROM `".AUTH_DATABASE."`.`user_data` AS a JOIN `".AUTH_DATABASE."`.`user_access` AS b ON a.`id` = b.`user_id` WHERE (b.`access_expires` = 0 OR b.`access_expires` > ".time().") AND b.`app_id` = ".$db->qstr($ORG_ID);
						$users = $db->GetAll($query);
						if($users){
							foreach($users as $user){
								$audience[$user["proxy_id"]] = $user["fullname"];
							}
						}						
						break;
						
				}
			}
		}
//		print_r($audience);
//		pring_r($read_users["proxy_id"]);
		
		?>
		<h1>Notice Statistics</h1>
		<h2>Users Who Have Seen This Notice</h2>
		<?php
		if($read_users) {
			?>
			
			<table class="tableList" cellspacing="0" summary="List of Notices">
			<colgroup>
				<col class="modified" />
				<col class="title" />
				<col class="date" />
			</colgroup>
			<thead>
				<tr>
					<td class="modified">&nbsp;</td>
					<td class="title">User Fullname</td>
					<td class="date">Timestamp</td>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td></td>
					<td colspan="2" style="padding-top: 10px">
						
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
				foreach($read_users["proxy_id"] as $key=>$proxy_id) {
					if(array_key_exists($proxy_id, $audience)){
						echo "<tr id=\"notice-".$result["notice_id"]."\" class=\"notice".(($expired) ? " na" : "")."\">\n";
						echo "	<td class=\"modified\">&nbsp;</td>\n";
						echo "	<td class=\"title\">".$audience[$proxy_id]."</td>\n";
						echo "	<td class=\"date\">".date("F jS, Y",$read_users["timestamp"][$key])."</td>\n";
						echo "</tr>\n";
					}
				}
				?>
			</tbody>
			</table>
			<?php
		} else {
			?>
			<div class="display-notice">
				<h3>No Users Have Read Notice</h3>
				There are currently no records of users having read the notice.<br/>
				<strong>Note:</strong> It's possible that users have read the notice and not checked off 'Mark Read'.
			</div>
			<?php
		}
		?>
		<h2>Users Who Haven't Seen This Notice</h2>
		<?php
		if ($audience) {
			$proxy_ids = array_keys($audience);
			$missing = false;
			foreach($proxy_ids as $id){
				if(!in_array($id,$read_users["proxy_id"])){
					$missing = true;
					break;
				}
			}
			if ($missing) {
				?>

				<table class="tableList" cellspacing="0" summary="List of Notices">
				<colgroup>
					<col class="modified" />
					<col class="title" />
				</colgroup>
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="title">User Fullname</td>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td></td>
						<td style="padding-top: 10px">

						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php
					foreach($audience as $proxy_id=>$user) {
						if(!in_array($proxy_id,$read_users["proxy_id"])){
							echo "<tr id=\"notice-".$result["notice_id"]."\" class=\"notice".(($expired) ? " na" : "")."\">\n";
							echo "	<td class=\"modified\">&nbsp;</td>\n";
							echo "	<td class=\"title\">".$user."</td>\n";
							echo "</tr>\n";
						}
					}
					?>
				</tbody>
				</table>
				<?php
			} else {
				?>
			<div class="display-notice">
				<h3>No Users Missing Notice</h3>
				All audience members have read the notice.
			</div>
			<?php
			
			}
		} else {
			?>
			<div class="display-notice">
				<h3>No Audience for Notice</h3>
				There is no audience assigned to this notice.
			</div>
			<?php
		}
	}
}
?>