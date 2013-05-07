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
 * This file displays the list of categories pulled
 * from the entrada_clerkship.categories table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer:James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_CATEGORIES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
		header("Location: ".ENTRADA_URL);
		exit;
} elseif (!$ENTRADA_ACL->amIAllowed('categories', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/scriptaculous/sortable_tree.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

	echo "<h1>Manage Clerkship Categories</h1>";
	if($ENTRADA_ACL->amIAllowed('categories', 'create', false)) {
		?>
		<div class="clearfix">
			<div class="pull-right">
				<a href="<?php echo ENTRADA_URL."/admin/settings/manage/categories?section=add&amp;step=1&org=".$ORGANISATION_ID; ?>" class="btn btn-primary">Add New Category</a>
			</div>
		</div>
		<div style="clear: both"></div>
		<?php
	}

	$query = "SELECT * FROM `".CLERKSHIP_DATABASE."`.`categories`
				WHERE `category_parent` = '0'
				AND `category_status` != 'trash'
				AND (`organisation_id` = ".$db->qstr($ORGANISATION_ID)." OR `organisation_id` IS NULL)
				ORDER BY `category_order` ASC";
	$result = $db->GetAll($query);
	if(isset($result) && count($result)>0){
		?>
		<form action="<?php echo ENTRADA_URL."/admin/settings/manage/categories?".replace_query(array("section" => "delete", "step" => 1)); ?>" method="post">
			<table class="tableList" cellspacing="0" summary="List of Categories">
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="title">Clerkship School Categories</td>
					</tr>
				</thead>
			</table>
			<ul class="categories-list">
			<?php
//			echo categories_inlists_conf(0, 0, array('id'=>'pagelists'));
			foreach ($result as $category) {
				echo "<li><div class=\"category-container\"><span class=\"delete\" style=\"width:27px;display:inline-block;\"><input type=\"checkbox\" id=\"delete_".$category["category_id"]."\" name=\"delete[".$category["category_id"]."][category_id]\" value=\"".$category["category_id"]."\" onclick=\"$$('#".$category["category_id"]."-children input[type=checkbox]').each(function(e){e.checked = $('delete_".$category["category_id"]."').checked; if (e.checked) e.disable(); else e.enable();});\"/></span>\n";
				echo "<a href=\"" . ENTRADA_URL . "/admin/settings/manage/categories?" . replace_query(array("section" => "edit", "id" => $category["category_id"])) . "\">".$category["category_name"]."</a></div></li>";
			}
			?>
			</ul>
			<input type="submit" class="btn btn-danger" value="Delete Selected" />
		</form>
		<?php
	} else {
		$NOTICE++;
		$NOTICESTR[] = "There are currently no Categories assigned to this Organisation";
		echo display_notice();
	}
}