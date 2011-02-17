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
		<th></th>
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
	<tr class="cat_head" id="cat_head_<?php echo $category->getID(); ?>">
		<td></td>
		<th colspan="2"><?php echo $label; ?></th>
		<td class="control" colspan="3"><ul class="page-action"><li><a href="#" class="add_btn" id="add_btn_<?php echo $category->getID(); ?>">Add <?php echo $label; ?></a></li></ul></td>
	</tr>
	<?php
		foreach ($values as $value) {
			echo editMetaDataRow_User($value, $category, $descendant_type_sets);
		} ?>
</tbody>
<?php } ?>
</table>
<script type="text/javascript">

function addRow(category_id, event) {
	Event.stop(event);
	new Ajax.Request("<?php echo ENTRADA_URL; ?>/admin/users/manage/metadata?section=api-metadata&id=<?php echo $PROXY_ID; ?>",
		{
			method:'post',
			parameters: { type: category_id, request: 'new_value' },
			evalScripts:true,
			onSuccess: function (response) {
				var head = $('cat_head_' + category_id);
				var xml = response.responseXML;
				var value_id = xml.firstChild.getAttribute("id");
				if (value_id) {
					var value_parts = /value_edit_(\d+)/.exec(value_id);
					if (value_parts && value_parts[1]) {
						head.insert({after: response.responseText});
						document.fire('MetaData:onAfterRowInsert', value_parts[1]);
					}
				}
			},
			onError: function (response) {
				alert(response.responseText);
			}
		});
	document.fire('MetaData:onBeforeRowInsert', category_id);
}

function deleteRow(value_id) {
	var tr = $('value_edit_'+value_id);
	tr.setAttribute("class", "value_delete");
	var checkbox = $('delete_'+value_id);
	var opts = [ "enable", "disable" ];
	tr.select('input:not([type=checkbox]), select').invoke(opts[Number(checkbox.checked)]);
}

function mkEvtReq(regex, func) {
	return function(event) {
		var element = Event.findElement(event);
		var tr = element.up('tr');
		var id = tr.getAttribute('id');
		var res = regex.exec(id);
		if (res && res[1]) {
			var target_id = res[1];
			func(target_id, event);
		}
		return false;
	}
}

var addRowReq = mkEvtReq(/^cat_head_(\d+)$/,addRow);
var deleteRowReq = mkEvtReq(/^value_edit_(\d+)$/, deleteRow);

function addDeleteListener(value_id) {
	var btn = $('delete_btn_'+value_id);
	btn.observe('click', deleteRowReq);
}


function addCategoryListeners() {
	$$('.DataTable .add_btn').invoke("observe", "click", addRowReq);
}

function addDeleteListeners() {
	$$('.DataTable .delete_btn').invoke("observe", "click", deleteRowReq);
}

function removeListeners() {
	$$('.DataTable .add_btn, .DataTable .delete_btn').invoke("stopObserving");
}

function meta_user_init() {
	addCategoryListeners();
	addDeleteListeners();
	document.observe('MetaData:onAfterRowInsert', function(event) {
		addDeleteListener(event.memo);
	});
}

function meta_user_clean() {
	removeListeners();
}

meta_user_init();
</script>
<?php

 
}