<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 *
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_USERS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("user", "update", false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {

require_once("Entrada/metadata/functions.inc.php");

//var_dump($PROXY_ID);

$user = User::get($PROXY_ID);
//var_dump($user);
$org_id = $user->getOrganisationID();
$group = $user->getGroup();
$role = $user->getRole();

$types = MetaDataTypes::get($org_id, $group, $role, $PROXY_ID);

$categories = array();
//For each of the applicable types without a parent (top-level types), create a section to help organize    
foreach ($types as $type) {
	$top_p = getTopParentType($type);
	if (!in_array($top_p, $categories, true)) {
		$categories[] = $top_p;
	}
}
//var_dump($categories);
?>
<table class="DataTable" callpadding="0" cellspacing="0">
<colgroup>
<col width="4%" />
<col width="18%" />
<col width="15%" />
<col width="33%" />
<col width="15%" />
<col width="15%" />
</colgroup>
<thead>
	<tr>
		<td></td>
		<th>Sub-type</th>
		<th>Value</th>
		<th>Notes</th>
		<th>Effective Date</th>
		<th>Expiry Date</th>
	</tr>
</thead>
<tfoot>
<tr>
<td></td>
<td colspan="5" class="control">
<a href=""><img src="<?php echo ENTRADA_URL; ?>/images/disk.png" alt="Save Icon" /> Save</a> 
</td>
</tr>
</tfoot>
<?php foreach ($categories as $category) { 
	$values = MetaDataValues::get($org_id, $group, $role,$PROXY_ID, $category);
	//var_dump($values);
	$descendant_type_sets = getDescendentTypesArray($types, $category); 
	$label = html_encode($category->getLabel());
?>
<tbody id="cat_<?php echo $category->getID(); ?>">
	<tr id="cat_head_<?php echo $category->getID(); ?>">
		<td class="control"><a href="#" class="add_btn" id="add_btn_<?php echo $category->getID(); ?>"><img src="<?php echo ENTRADA_URL; ?>/images/add.png" alt="Add <?php echo $label; ?>" title="Add <?php echo $label; ?>" /></a></td>
		<th colspan="5"><?php echo $label; ?></th>
	</tr>
	<?php
		foreach ($values as $value) {
			echo editMetaDataRow_User($value, $category, $descendant_type_sets);
		} ?>
</tbody>
<?php } ?>
<tfoot></tfoot>
<tr>
</table>
<script type="text/javascript">

function addRow(category_id) {
	new Ajax.Request("<?php echo ENTRADA_URL; ?>/admin/users/manage/metadata?section=api-metadata&id=<?php echo $PROXY_ID; ?>",
		{
			method:'post',
			parameters: { type: category_id, request: 'new_value' },
			evalScripts:true,
			onSuccess: function (response) {
				var head = $('cat_head_' + category_id);
				head.insert({after: response.responseText});
				document.fire('MetaData:onAfterUpdate');
			},
			onError: function (response) {
				alert(response.responseText);
			}
		});
	document.fire('MetaData:onBeforeUpdate');
}

function addRowReq(event) {
	Event.stop(event);
	var element = Event.findElement(event);
	var tbody = element.up('tbody');
	var id = tbody.getAttribute('id');
	var regex = /^cat_(\d+)$/;
	var res = regex.exec(id);
	if (res && res[1]) {
		var cat_id = res[1];
		addRow(cat_id);
	}
	return false;
}

function addCategoryListeners() {
	$$('.DataTable .add_btn').each(function (e) {
		clog("adding");
		e.observe("click", addRowReq);
	});
}

function deleteRow(value_id) {
	
}

function undeleteRow(value_id) {
	
}

function addDeleteListener(value_id) {
	
}

function addDeleteListeners() {
	
}

function removeListeners() {
	
}

function meta_user_init() {
	clog("testing");
	addCategoryListeners();
	addDeleteListeners();
}

function meta_user_clean() {
	removeListeners();
}

meta_user_init();
</script>
<?php

 
}