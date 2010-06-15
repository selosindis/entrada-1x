<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Gives community administrators the ability to delete an existing page from
 * their community.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_PAGES")) || !COMMUNITY_INCLUDED || !IN_PAGES) {
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
}

if (($LOGGED_IN) && (!$COMMUNITY_MEMBER)) {
	$NOTICE++;
	$NOTICESTR[] = "You are not currently a member of this community, <a href=\"".ENTRADA_URL."/communities?section=join&community=".$COMMUNITY_ID."&step=2\" style=\"font-weight: bold\">want to join?</a>";

	echo display_notice();
} else {
	$CPAGE_ID		= 0;
	
	$BREADCRUMB[]	= array("url" => "", "title" => "Delete Pages");

	echo "<h1>Delete Pages</h1>\n";

	// Error Checking
	switch($STEP) {
		case 2 :
		case 1 :
		default :
			if ((isset($_POST["delete"])) && ($tmp_input = clean_input($_POST["delete"], array("nows", "int")))) {
				$query	= "SELECT * FROM `community_pages` WHERE `cpage_id` = ".$db->qstr($tmp_input)." AND `community_id` = ".$db->qstr($COMMUNITY_ID);
				$result	= $db->GetRow($query);
				if ($result) {
					$CPAGE_ID	= $result["cpage_id"];
					$parent_id	= $result["parent_id"];
					$page_order	= $result["page_order"];
					$page_url 	= $result["page_url"];
					if ($page_url == "" || !$page_url) {
						$ERROR++;
						$ERRORSTR[] = "The home page of the community cannot be deleted.";
					} elseif (((int)$result["page_active"]) == 0) {
						$ERROR++;
						$ERRORSTR[] = "The page you have tried to delete does not exist within this community.";
					}
				}
			}
			
			if (!$CPAGE_ID) {
				header("Location: ".COMMUNITY_URL.$community_details["community_url"].":pages");
				exit;
			}
		break;
	}
	
	// Display Page
	switch($STEP) {
		case 2 :
			communities_pages_delete($CPAGE_ID);
			$query			= "SELECT `cpage_id`, `page_order` FROM `community_pages` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `parent_id` = ".$db->qstr($parent_id)." AND `page_order` > ".$db->qstr($page_order);
			$moving_pages	= $db->GetAll($query);
			if ($moving_pages) {
				foreach($moving_pages as $moving_page) {
					$query = "UPDATE `community_pages` SET `page_order` = ".$db->qstr($moving_page["page_order"] - 1)." WHERE `cpage_id` = ".$db->qstr($moving_page["cpage_id"]);
					$db->Execute($query);
				}
			}
			header("Location: ".COMMUNITY_URL.$community_details["community_url"].":pages");
			exit;
		break;
		case 1 :
		default :
			if ($ERROR) {
				echo display_error();
			} else {
				echo display_notice(array("Please review the following page or pages to ensure that you wish to permanently delete them. This action cannot be undone, and once removed the content is not recoverable."));
				?>
				<form action="<?php echo COMMUNITY_URL.$community_details["community_url"].":pages?".replace_query(array("action" => "delete", "step" => 2)); ?>" method="post">
				<table class="tableList" cellspacing="0" summary="List of pages to be removed">
				<colgroup>
					<col class="modified" />
					<col class="title" />
				</colgroup>
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="title">Community Pages</td>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td>&nbsp;</td>
						<td style="padding-top: 10px">
							<input type="submit" class="button" value="Delete Selected" />
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php
				echo communities_pages_intable($CPAGE_ID, 0, array("selected" => $CPAGE_ID, "selectable_children" => false));
				?>
				</tbody>
				</table>
				</form>
				<?php
			}
		break;
	}
}
?>