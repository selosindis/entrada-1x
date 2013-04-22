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
 * This file edits a department in the `departments` table.
 *
 * @author Organisation: Queen's University
 * @author Unit: MEdTech Unit
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
 */
if ((!defined("PARENT_INCLUDED")) || (!defined("IN_CONFIGURATION"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: " . ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("configuration", "create", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:" . html_encode($AGENT_CONTACTS["administrator"]["email"]) . "\">" . html_encode($AGENT_CONTACTS["administrator"]["name"]) . "</a> for assistance.");

	echo display_error();

	application_log("error", "Group [" . $_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"] . "] and role [" . $_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"] . "] do not have access to this module [" . $MODULE . "]");
} else {

	$BREADCRUMB[] = array("url" => ENTRADA_URL . "/admin/settings/manage/departments?" . replace_query(array("section" => "profile-fields")) . "&amp;org=" . $ORGANISATION_ID, "title" => "Custom Profile Fields");

	if ((isset($_GET["department_id"])) && ($department_id = clean_input($_GET["department_id"], array("notags", "trim")))) {
		$PROCESSED["department_id"] = $department_id;
	}
	
	if ((isset($_POST["ajax_action"])) && ($tmp_input = clean_input($_POST["ajax_action"], array("notags", "trim")))) {
		$ajax_action = $tmp_input;
	}
	
	if ($ajax_action) {
		ob_clear_open_buffers();
		
		switch ($ajax_action) {
			case "create_field" :
				$add_mode = "INSERT";
				if ($_POST["add_mode"] == "update") {
					if (!empty($_POST["id"]) && ($tmp_input = clean_input($_POST["id"], "numeric"))) {
						$PROCESSED["id"] = $tmp_input;
						$add_mode = "UPDATE";
					}
				}
				if (!empty($_POST["title"]) && ($tmp_input = clean_input($_POST["title"], array("notags", "trim")))) {
					$PROCESSED["title"] = $tmp_input;
					$PROCESSED["name"] = strtolower(str_replace(" ", "-", $tmp_input));
				} else {
					$errors["title"] = "A title is required.";
				}
				if (!empty($_POST["type"]) && ($_POST["type"] == "richtext" || $_POST["type"] == "textarea" || $_POST["type"] == "textinput" || $_POST["type"] == "checkbox")) {
					$PROCESSED["type"] = strtoupper(clean_input($_POST["type"], array("notags", "trim")));
				} else {
					$errors["type"] = "Invalid type selected.";
				}
				if (isset($_POST["length"]) && ($tmp_input = clean_input($_POST["length"], array("numeric")))) {
					$PROCESSED["length"] = $tmp_input;
				} else {
					$PROCESSED["length"] = NULL; // NULL = unlimited length.
				}
				if (isset($_POST["required"])) {
					$PROCESSED["required"] = 1;
				} else {
					$PROCESSED["required"] = 0;
				}
				
				if (!$errors) {
					if ($add_mode != "UPDATE") {
						$query = "SELECT COUNT(*) FROM `profile_custom_fields` WHERE `department_id` = ".$db->qstr($PROCESSED["department_id"])." and `organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation());
						$order = $db->GetOne($query);
						$PROCESSED["order"] = $order;
					}

					$PROCESSED["organisation_id"] = $ENTRADA_USER->getActiveOrganisation();
					
					if ($db->AutoExecute("profile_custom_fields", $PROCESSED, $add_mode, $add_mode == "UPDATE" ? "`id` = ".$PROCESSED["id"] : "'1' = '1'")) {
						$PROCESSED["id"] = ($add_mode == "UPDATE" ? $PROCESSED["id"] : $db->Insert_ID());
						echo json_encode(array("status" => "success", "data" => $PROCESSED));
					} else {
						echo json_encode(array("status" => "error", "data" => array("system" => "A system error ocurred, an Administrator has been informed, please try again later.".$db->ErrorMsg())));	
					}
				} else {
					echo json_encode(array("status" => "error", "data" => $errors));
				}
				
			break;
			case "delete_field" :

				if (!empty($_POST["id"]) && ($tmp_input = clean_input($_POST["id"], "numeric"))) {
					$PROCESSED["id"] = $tmp_input;
				}
				
				$PROCESSED["active"] = 0;
				
				$db->AutoExecute("profile_custom_fields", $PROCESSED, "UPDATE", "`id` = ".$db->qstr($PROCESSED["id"]));
				
			break;
			case "fetch_field" :
				
				if ($_POST["id"] && ($tmp_input = clean_input($_POST["id"], array("numeric")))) {
					$PROCESSED["id"] = $tmp_input;
				}
				
				if (!empty($PROCESSED["id"])) {
					$query = "SELECT * FROM `profile_custom_fields` WHERE `id` = ".$db->qstr($PROCESSED["id"]);
					$result = $db->GetRow($query);
					if ($result) {
						echo json_encode(array("status" => "success", "data" => $result));
					}
				} else {
					$errors["id"] = "Invalid ID, unable to pull field record from database.";
					echo json_encode(array("status" => "error", "data" => $errors));
				}
				
			break;
			default:
			break;
		}
		
		exit;
	}
	
	$query = "SELECT * FROM `profile_custom_fields` WHERE `department_id` = ".$db->qstr($department_id)." AND `organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())." AND `active` = '1' ORDER BY `order`";
	$custom_fields = $db->GetAll($query);

	?>
	<script type="text/javascript">
	jQuery(function(){
		jQuery(".field-action").tooltip();
		jQuery(".field-action").on("click", function() {
			var id = jQuery(this).parent().parent().attr("data-id");
			if (jQuery(this).hasClass("edit-field")) {
				jQuery.ajax({
					url : "<?php echo ENTRADA_URL; ?>/admin/settings/manage/departments?section=profile-fields&org=<?php echo $ENTRADA_USER->getActiveOrganisation(); ?>&department_id=<?php echo $department_id; ?>",
					data : "ajax_action=fetch_field&id=" + id,
					type : "post",
					success : function(data) {
						var json_data = JSON.parse(data);
						jQuery("#add_field").modal('show');
						jQuery("#add_field #field_title").attr("value", json_data.data.title);
						jQuery("#add_field #field_length").attr("value", json_data.data.length);
						jQuery("#add_field #field_type option[value="+json_data.data.type.toLowerCase()+"]").attr("selected", "selected");
						if (json_data.data.required == "1") {
							jQuery("#add_field #field_required").attr("checked", "checked");
						}
						jQuery("#add_mode").attr("value", "update");
						jQuery("#add_field #id").attr("value", id);
					}
				})
			} else if (jQuery(this).hasClass("delete-field")) {
				jQuery("#delete_field").attr("data-id", jQuery(this).parent().parent().attr("data-id")).modal("show");
			}
			return false;
		});
		jQuery("#delete_btn").on("click", function() {
			var data_id = jQuery("#delete_field").attr("data-id");
			jQuery.ajax({
				url : "<?php echo ENTRADA_URL; ?>/admin/settings/manage/departments?section=profile-fields&org=<?php echo $ENTRADA_USER->getActiveOrganisation(); ?>&department_id=<?php echo $department_id; ?>",
				data : "ajax_action=delete_field&id=" + data_id,
				type : "post",
				success : function(data) {
					jQuery("tr[data-id="+data_id+"]").remove();
					jQuery("#delete_field").attr("data-id", "").modal("hide");
				}
			})
		});
		jQuery("#delete_field").on("hide", function() {
			jQuery(this).attr("data-id", "");
		});
		jQuery("#reorder").on("click", function() {
			alert("reorder clicked");
			return false;
		});
		jQuery("#add_field_btn").on("click", function() {
			jQuery("#add_field #field_title").attr("value", "");
			jQuery("#add_field #field_type").attr("value", "");
			jQuery("#add_field #field_type").children().removeProp("selected");
			jQuery("#add_field #field_length").attr("value", "");
			jQuery("#add_field #field_required").removeProp("checked");
			jQuery("#add_mode").attr("value", "insert");
		});
		jQuery("#add_btn").on("click", function() {
			var form_data = jQuery("#create_field").serialize();
			jQuery.ajax({
				url : "<?php echo ENTRADA_URL; ?>/admin/settings/manage/departments?section=profile-fields&org=<?php echo $ENTRADA_USER->getActiveOrganisation(); ?>&department_id=<?php echo $department_id; ?>",
				data : "ajax_action=create_field&" + form_data,
				type : "post",
				success: function(data) {
					var json_data = JSON.parse(data);
					if (json_data.status == "success") {
						if (jQuery("#add_mode").attr("value") == "insert") {
							var new_row = document.createElement("tr");
							jQuery(new_row).attr("data-id", json_data.data.id)

							var title = document.createElement("td");
							jQuery(title).html(json_data.data.title);

							var type = document.createElement("td");
							jQuery(type).html(json_data.data.type.toLowerCase());

							var length = document.createElement("td");
							jQuery(length).html(json_data.data.length);

							var required = document.createElement("td");
							jQuery(required).attr("style", "text-align:center!important;")
							jQuery(required).html("<i class=\"" + (json_data.data.required == "1" ? "icon-ok-sign" : "icon-remove-sign") + "\"></i>");

							var edit = document.createElement("td");
							jQuery(edit).html('<a href="#" class="field-action edit-field" data-toggle="tooltip" title="Edit Field"><i class="icon-edit"></i></a>');

							jQuery(new_row).append(title).append(type).append(length).append(required).append(edit);

							jQuery("#field-list tbody").append(new_row);
						} else {
							jQuery("tr[data-id="+json_data.data.id+"] .title").html(json_data.data.title);
						}
						jQuery("#add_field").modal("hide");
					} else {
						for (error in json_data.data) {
							jQuery("#field_" + error).parent().parent().addClass("error");
							jQuery("#field_" + error).after("<span class=\"help-inline\">"+json_data.data[error]+"</span>");
						}
					}
				}
			});
			return false;
		});
	});
	</script>
	<h1>Custom Profile Fields</h1>
	<?php echo departments_nav($department_id, "profile-fields"); ?>
	<div class="row-fluid">
		<a href="#" class="btn btn-navbar pull-left" id="reorder">Reorder</a>
		<a href="#add_field" class="btn btn-primary pull-right" data-toggle="modal" id="add_field_btn">Add Field</a>
	</div>
	<br />
	<?php
	if ($custom_fields) {
		?>
		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="table table-striped table-hover table-bordered" id="field-list">
			<thead>
				<tr>
					<th>Title</th>
					<th width="10%">Type</th>
					<th width="10%">Length</th>
					<th width="5%">Req.</th>
					<th width="8%"></th>
				</tr>
			</thead>
			<tbody>
		<?php foreach ($custom_fields as $field) { ?>
				<tr data-id="<?php echo html_encode($field["id"]); ?>">
					<td class="title"><?php echo html_encode($field["title"]); ?></td>
					<td class="type"><?php echo html_encode(strtolower($field["type"])); ?></td>
					<td class="length"><?php echo html_encode($field["length"]); ?></td>
					<td class="required" style="text-align:center!important;"><i class="<?php echo $field["required"] == 1 ? "icon-ok-sign" : "icon-remove-sign" ; ?>"></i></td>
					<td class="action">
						<a href="#" class="field-action edit-field" data-toggle="tooltip" title="Edit Field"><i class="icon-edit"></i></a>
						<a href="#" class="field-action delete-field" data-toggle="tooltip" title="Delete Field"><i class="icon-trash"></i></a>
					</td>
				</tr>
		<?php } ?>
			</tbody>
		</table>
		<?php
	} else {
		add_notice("There are currently no customized fields for this department. Please use the Add Field button above to create a new custom field for this department.");
		echo display_notice();	
	}
	?>
	<div id="delete_field" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Delete Custom Field</h3>
		</div>
		<div class="modal-body">
			<p class="info">Are you sure you wish to delete this field?</p>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn" data-dismiss="modal">Cancel</a>
			<a href="#" class="btn btn-primary" id="delete_btn">Delete</a>
		</div>
	</div>
	<div id="add_field" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Add Custom Field</h3>
		</div>
		<div class="modal-body">
			<form action="" method="POST" class="form-horizontal" id="create_field">
				<input id="add_mode" type="hidden" name="add_mode" value="insert" />
				<input id="id" type="hidden" name="id" value="" />
				<div class="control-group">
					<label class="control-label" for="field_title">Field Title:</label>
					<div class="controls">
						<input class="input-large" name="title" id="field_title" type="text" placeholder="Favourite color" value="" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="field_type">Field Type:</label>
					<div class="controls">
						<select class="inline" id="field_type" name="type">
							<option value="richtext">Rich Text</option>
							<option value="textarea">Plain Text</option>
							<option value="textinput">One Line Text</option>
							<option value="checkbox">Checkbox</option>
						</select>
						<span class="help-inline"><?php echo html_encode($result["firstname"]." ".$result["lastname"]); ?></span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="field_length">Field Length:</label>
					<div class="controls">
						<input class="input-small" name="length" id="field_length" type="text" placeholder="255" value="" />
						<span class="help-inline">Leave blank for unlimited.</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="field_required">Field Required:</label>
					<div class="controls">
						<label class="checkbox"><input type="checkbox" id="field_required" name="required" value="" /> This option will require the field to be answered.</label>
					</div>
				</div>
			</form>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn" data-dismiss="modal">Cancel</a>
			<a href="#" class="btn btn-primary" id="add_btn">Add</a>
		</div>
	</div>
	<?php
}


