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
 * This file is used to edit categories in the entrada_clerkship.categories table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer:James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_CATEGORIES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('categories', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/settings/manage/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	
	if (isset($_GET["id"]) && ($id = clean_input($_GET["id"], array("notags", "trim")))) {
				$CATEGORY_ID = $id;
	}
	
	if (isset($_GET["mode"]) && $_GET["mode"] == "ajax") {
		$MODE = "ajax";
	}
	
	if ($CATEGORY_ID) {
		/**
		 * Fetch a list of available evaluation targets that can be used as Form Types.
		 */
		$query = "SELECT * FROM `".CLERKSHIP_DATABASE."`.`category_type` ORDER BY `ctype_parent`, `ctype_id`";
		$results = $db->GetAll($query);
		if ($results) {
			foreach ($results as $result) {
				$CATEGORY_TYPES[$result["ctype_id"]] = $result;
			}
		}
		
		$query = "	SELECT * FROM `".CLERKSHIP_DATABASE."`.`categories` AS a
                    JOIN `".CLERKSHIP_DATABASE."`.`category_type` AS b
                    ON a.`category_type` =  b.`ctype_id`
					WHERE a.`category_id` = ".$db->qstr($CATEGORY_ID)."
					AND (a.`organisation_id` = ".$db->qstr($ENTRADA_USER->GetActiveOrganisation())." OR a.`organisation_id` IS NULL)
					AND a.`category_status` != 'trash'";
		$category_details	= $db->GetRow($query);
		
		if (isset($MODE) && $MODE == "ajax") {
			ob_clear_open_buffers();
			$time = time();

			if ($category_details["category_parent"] != 0) {
				
				switch ($STEP) {
					case "2" :
						/**
						* Required field "category_name" / Category Name
						*/
						if (isset($_POST["category_name"]) && ($category_name = clean_input($_POST["category_name"], array("notags", "trim")))) {
							$PROCESSED["category_name"] = $category_name;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The <strong>Category Name</strong> is a required field.";
						}

						/**
						* Non-required field "category_code" / Category Code
						*/
						if (isset($_POST["category_code"]) && ($category_code = clean_input($_POST["category_code"], array("notags", "trim")))) {
							$PROCESSED["category_code"] = $category_code;
						} else {
							$PROCESSED["category_code"] = "";
						}
                        
                        $category_dates = validate_calendars("category", true, false, false);
                        if ((isset($category_dates["start"])) && ((int) $category_dates["start"])) {
                            $PROCESSED["category_start"]	= (int) $category_dates["start"];
                        } else {
                            $ERROR++;
                            $ERRORSTR[] = "The <strong>Category Start</strong> field is required.";
                        }
                        
                        if ((isset($category_dates["finish"])) && ((int) $category_dates["finish"])) {
                            $PROCESSED["category_finish"]	= (int) $category_dates["finish"];
                        } else {
                            $ERROR++;
                            $ERRORSTR[] = "The <strong>Category Finish</strong> field is required.";
                        }
                        
                        /**
                         * Required field "category_type" / Category Type.
                         */
                        if (isset($_POST["category_type"]) && ($tmp_input = clean_input($_POST["category_type"], "int")) && array_key_exists($tmp_input, $CATEGORY_TYPES)) {
                            $PROCESSED["category_type"] = $tmp_input;
                        } else {
                            $ERROR++;
                            $ERRORSTR[] = "The <strong>Category Type</strong> field is a required field.";
                        }

						/**
						* Non-required field "category_parent" / Category Parent
						*/
						if (isset($_POST["category_id"]) && ($category_parent = clean_input($_POST["category_id"], array("int")))) {
							$PROCESSED["category_parent"] = $category_parent;
						} else {
							$PROCESSED["category_parent"] = 0;
						}

						/**
						* Non-required field "category_desc" / Category Description
						*/
						if (isset($_POST["category_desc"]) && ($category_desc = clean_input($_POST["category_desc"], array("notags", "trim")))) {
							$PROCESSED["category_desc"] = $category_desc;
						} else {
							$PROCESSED["category_desc"] = "";
						}
						
						/**
						* Required field "category_order" / Category Order
						*/
						if (isset($_POST["category_order"]) && ($category_order = clean_input($_POST["category_order"], array("int"))) && $category_order != "-1") {
							$PROCESSED["category_order"] = clean_input($_POST["category_order"], array("int")) - 1;
						} else if($category_order == "-1") {
							$PROCESSED["category_order"] = $category_details["category_order"];
						} else {
							$PROCESSED["category_order"] = 0;
						}

						if (!$ERROR) {
							if ($category_details["category_order"] != $PROCESSED["category_order"]) {
								$query = "SELECT `category_id` FROM `".CLERKSHIP_DATABASE."`.`categories`
											WHERE `category_parent` = ".$db->qstr($PROCESSED["category_parent"])."
											AND (`organisation_id` = ".$db->qstr($ENTRADA_USER->GetActiveOrganisation())." OR `organisation_id` IS NULL)
											AND `category_id` != ".$db->qstr($CATEGORY_ID)."
											AND `category_status` != 'trash'
											ORDER BY `category_order` ASC";
								$categories = $db->GetAll($query);
								if ($categories) {
									$count = 0;
									foreach ($categories as $category) {
										if($count === $PROCESSED["category_order"]) {
											$count++;
										}
										if (!$db->AutoExecute("`".CLERKSHIP_DATABASE."`.`categories`", array("category_order" => $count), "UPDATE", "`category_id` = ".$db->qstr($category["category_id"]))) {
											$ERROR++;
											$ERRORSTR[] = "There was a problem updating this category in the system. The system administrator was informed of this error; please try again later.";

											application_log("error", "There was an error updating an category. Database said: ".$db->ErrorMsg());
										}
										$count++;
									}
								}
							}
						}
						
						if (!$ERROR) {
						
							$PROCESSED["updated_date"] = time();
							$PROCESSED["updated_by"] = $ENTRADA_USER->getID();

							if (!$db->AutoExecute("`".CLERKSHIP_DATABASE."`.`categories`", $PROCESSED, "UPDATE", "`category_id` = ".$db->qstr($CATEGORY_ID))) {
								
								echo json_encode(array("status" => "error", "msg" => "There was a problem updating this category in the system. The system administrator was informed of this error; please try again later."));

								application_log("error", "There was an error updating an category. Database said: ".$db->ErrorMsg());
							} else {
								$PROCESSED["category_id"] = $CATEGORY_ID;
								echo json_encode(array("status" => "success", "updates" => $PROCESSED));
							}
							
						} else {
							echo json_encode(array("status" => "error", "msg" => implode("<br />", $ERRORSTR)));
						}
					break;
					case "1" :
					default :
                        $PROCESSED = $category_details;
						?>
						<script type="text/javascript">
						function selectCategory(parent_id, category_id) {
							new Ajax.Updater('m_selectCategoryField_<?php echo $time; ?>', '<?php echo ENTRADA_URL; ?>/api/categories-list.api.php', {parameters: {'pid': parent_id, 'id': category_id, 'organisation_id': <?php echo $ENTRADA_USER->GetActiveOrganisation(); ?>}});
							return;
						}
						function selectOrder(category_id, parent_id) {
							new Ajax.Updater('m_selectOrderField_<?php echo $time; ?>', '<?php echo ENTRADA_URL; ?>/api/categories-list.api.php', {parameters: {'id': category_id, 'type': 'order', 'pid': parent_id, 'organisation_id': <?php echo $ENTRADA_USER->GetActiveOrganisation(); ?>}});
							return;
						}
						jQuery(function(){
							selectCategory(<?php echo (isset($PROCESSED["category_parent"]) && $PROCESSED["category_parent"] ? $PROCESSED["category_parent"] : "0"); ?>, <?php echo $CATEGORY_ID; ?>);
							selectOrder(<?php echo $CATEGORY_ID; ?>, <?php echo (isset($PROCESSED["category_parent"]) && $PROCESSED["category_parent"] ? $PROCESSED["category_parent"] : "0"); ?>);
						});
						</script>
						<form id="category-form" action="<?php echo ENTRADA_URL."/admin/clerkship/categories"."?".replace_query(array("action" => "edit", "step" => 2, "mode" => "ajax")); ?>" method="post">
							<h2>Clerkship<?php echo (isset($PROCESSED["ctype_name"]) && $PROCESSED["ctype_name"] ? " ".$PROCESSED["ctype_name"] : ""); ?> Category Details</h2>
                            <div style="display: none;" class="display-error"></div>
                            <div id="category-set-details-section">
                                <div class="control-group row-fluid">
                                    <label for="category_name" class="form-required span4">Category Name:</label>
                                    <span class="controls span7 offset1">
                                        <input type="text" id="category_name" name="category_name" value="<?php echo ((isset($PROCESSED["category_name"])) ? html_encode($PROCESSED["category_name"]) : ""); ?>" maxlength="60" style="width: 300px" />
                                    </span>
                                </div>
                                <div class="control-group row-fluid">
                                    <label for="category_code" class="form-nrequired span4">Category Code:</label>
                                    <span class="controls span7 offset1">
                                        <input type="text" id="category_code" name="category_code" value="<?php echo ((isset($PROCESSED["category_code"])) ? html_encode($PROCESSED["category_code"]) : ""); ?>" maxlength="100" style="width: 300px" />
                                    </span>
                                </div>
                                <div class="control-group row-fluid">
                                    <label for="category_type" class="form-nrequired span4">Category Type:</label>
                                    <span class="controls span7 offset1">
                                        <select id="category_type" name="category_type" value="<?php echo ((isset($PROCESSED["category_type"])) ? html_encode($PROCESSED["category_type"]) : ""); ?>" style="width: 300px">
                                            <option value="0"<?php echo (!isset($PROCESSED["category_type"]) || $PROCESSED["category_type"] == 0 ? " selected=\"selected\"" : ""); ?>>--- Select a Category Type ---</option>
                                            <?php
                                            foreach ($CATEGORY_TYPES as $type) {
                                                echo "<option value=\"".$type["ctype_id"]."\"".($PROCESSED["category_type"] == $type["ctype_id"] ? " selected=\"selected\"" : "").">".html_encode($type["ctype_name"])."</option>\n";
                                            }
                                            ?>
                                        </select>
                                    </span>
                                </div>
								<table style="width:100%">
                                    <?php echo generate_calendars("category", "", true, true, ((isset($PROCESSED["category_start"])) ? $PROCESSED["category_start"] : 0), true, true, ((isset($PROCESSED["category_finish"])) ? $PROCESSED["category_finish"] : 0), false); ?>
                                </table>
								<br />
                                <div class="control-group row-fluid">
                                    <label for="category_desc" class="form-nrequired span4">Category Description: </label>
                                    <span class="controls span7 offset1">
										<textarea id="category_desc" name="category_desc" style="width: 98%; height: 200px" rows="20" cols="70"><?php echo ((isset($PROCESSED["category_desc"])) ? html_encode($PROCESSED["category_desc"]) : ""); ?></textarea>
                                    </span>
                                </div>
                                <br />
                                <div class="control-group row-fluid">
                                    <label for="category_id" class="form-required span4">Category Parent:</label>
                                    <span class="controls span7 offset1">
                                        <div id="m_selectCategoryField_<?php echo $time; ?>"></div>
                                    </span>
                                </div>
                                <br />
                                <div class="control-group row-fluid">
                                    <label for="category_id" class="form-required span4">Category Order:</label>
                                    <span class="controls span7 offset1">
                                        <div id="m_selectOrderField_<?php echo $time; ?>"></div>
                                    </span>
                                </div>
                            </div>
						</form>
						<?php
					break;
				}
			}
			exit;
		} else {
			if ($category_details) {
				$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/clerkship/categories?".replace_query(array("section" => "edit")), "title" => "Editing Clerkship".(isset($category_details["ctype_name"]) && $category_details["ctype_name"] ? " ".$category_details["ctype_name"] : "")." Category");

				// Error Checking
				switch ($STEP) {
					case 2:
						/**
						* Required field "category_name" / Category Name
						*/
						if (isset($_POST["category_name"]) && ($category_name = clean_input($_POST["category_name"], array("notags", "trim")))) {
							$PROCESSED["category_name"] = $category_name;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The <strong>Category".(isset($category_details["ctype_name"]) && $category_details["ctype_name"] ? " ".$category_details["ctype_name"] : "")." Name</strong> is a required field.";
						}

						/**
						* Non-required field "category_code" / Category Code
						*/
						if (isset($_POST["category_code"]) && ($category_code = clean_input($_POST["category_code"], array("notags", "trim")))) {
							$PROCESSED["category_code"] = $category_code;
						} else {
							$PROCESSED["category_code"] = "";
						}
                        
                        $category_dates = validate_calendars("category", true, false, false);
                        if ((isset($category_dates["start"])) && ((int) $category_dates["start"])) {
                            $PROCESSED["category_start"]	= (int) $category_dates["start"];
                        } else {
                            $ERROR++;
                            $ERRORSTR[] = "The <strong>Category Start</strong> field is required.";
                        }
                        
                        if ((isset($category_dates["finish"])) && ((int) $category_dates["finish"])) {
                            $PROCESSED["category_finish"]	= (int) $category_dates["finish"];
                        } else {
                            $ERROR++;
                            $ERRORSTR[] = "The <strong>Category Finish</strong> field is required.";
                        }
                        
                        /**
                         * Required field "category_type" / Category Type.
                         */
                        if (isset($_POST["category_type"]) && ($tmp_input = clean_input($_POST["category_type"], "int")) && array_key_exists($tmp_input, $CATEGORY_TYPES)) {
                            $PROCESSED["category_type"] = $tmp_input;
                        } else {
                            $ERROR++;
                            $ERRORSTR[] = "The <strong>Category Type</strong> field is required.";
                        }

						/**
						* Non-required field "category_parent" / Category Parent
						*/
						if (isset($_POST["category_id"]) && ($category_parent = clean_input($_POST["category_id"], array("int")))) {
							$PROCESSED["category_parent"] = $category_parent;
						} else {
							$PROCESSED["category_parent"] = 0;
						}

						/**
						* Required field "category_order" / Category Order
						*/
						if (isset($_POST["category_order"]) && ($category_order = clean_input($_POST["category_order"], array("int"))) && $category_order != "-1") {
							$PROCESSED["category_order"] = clean_input($_POST["category_order"], array("int")) - 1;
						} else if($category_order == "-1") {
							$PROCESSED["category_order"] = $category_details["category_order"];
						} else {
							$PROCESSED["category_order"] = 0;
						}

						/**
						* Non-required field "category_desc" / Category Description
						*/
						if (isset($_POST["category_desc"]) && ($category_desc = clean_input($_POST["category_desc"], array("notags", "trim")))) {
							$PROCESSED["category_desc"] = $category_desc;
						} else {
							$PROCESSED["category_desc"] = "";
						}
						
						if (!$ERROR) {
							if ($category_details["category_order"] != $PROCESSED["category_order"]) {
								$query = "SELECT `category_id` FROM `".CLERKSHIP_DATABASE."`.`categories`
											WHERE `category_parent` = ".$db->qstr($PROCESSED["category_parent"])."
											AND (`organisation_id` = ".$db->qstr($ENTRADA_USER->GetActiveOrganisation())." OR `organisation_id` IS NULL)
											AND `category_id` != ".$db->qstr($CATEGORY_ID)."
											AND `category_status` != 'trash'
											ORDER BY a.`category_order` ASC";
								$categories = $db->GetAll($query);
								if ($categories) {
									$count = 0;
									foreach ($categories as $category) {
										if($count === $PROCESSED["category_order"]) {
											$count++;
										}
										if (!$db->AutoExecute("`".CLERKSHIP_DATABASE."`.`categories`", array("category_order" => $count), "UPDATE", "`category_id` = ".$db->qstr($category["category_id"]))) {
											$ERROR++;
											$ERRORSTR[] = "There was a problem updating this category in the system. The system administrator was informed of this error; please try again later.";

											application_log("error", "There was an error updating an category. Database said: ".$db->ErrorMsg());
										}
										$count++;
									}
								}
							}
						}

						if (!$ERROR) {
							$PROCESSED["updated_date"] = time();
							$PROCESSED["updated_by"] = $ENTRADA_USER->getID();

							if ($db->AutoExecute("`".CLERKSHIP_DATABASE."`.`categories`", $PROCESSED, "UPDATE", "`category_id` = ".$db->qstr($CATEGORY_ID))) {
								if (!$ERROR) {
									$url = ENTRADA_URL . "/admin/clerkship/categories";

									$SUCCESS++;
									$SUCCESSSTR[] = "You have successfully updated <strong>".html_encode($PROCESSED["category_name"])."</strong> in the system.<br /><br />You will now be redirected to the categories index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

									$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";

									application_log("success", "Category [".$CATEGORY_ID."] updated in the system.");		
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "There was a problem updating this category in the system. The system administrator was informed of this error; please try again later.";

								application_log("error", "There was an error updating an category. Database said: ".$db->ErrorMsg());
							}
						}

						if ($ERROR) {
							$STEP = 1;
						}
					break;
					case 1:
					default:
						$PROCESSED = $category_details;
					break;
				}

				//Display Content
				switch ($STEP) {
					case 2:
						if ($SUCCESS) {
							echo display_success();
						}

						if ($NOTICE) {
							echo display_notice();
						}

						if ($ERROR) {
							echo display_error();
						}
					break;
					case 1:
						if ($ERROR) {
							echo display_error();
						}
						$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
						$HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/picklist.js\"></script>\n";
						$ONLOAD[]	= "$('courses_list').style.display = 'none'";

						$HEAD[]	= "<script type=\"text/javascript\">
									function selectCategory(parent_id, category_id) {
										new Ajax.Updater('selectCategoryField', '".ENTRADA_URL."/api/categories-list.api.php', {parameters: {'pid': parent_id, 'id': category_id, 'organisation_id': ".$ENTRADA_USER->GetActiveOrganisation()."}});
										return;
									}
									function selectOrder(category_id, parent_id) {
										new Ajax.Updater('selectOrderField', '".ENTRADA_URL."/api/categories-list.api.php', {parameters: {'id': category_id, 'type': 'order', 'pid': parent_id, 'organisation_id': ".$ENTRADA_USER->GetActiveOrganisation()."}});
										return;
									}
									</script>";
						$ONLOAD[] = "selectCategory(".(isset($PROCESSED["category_parent"]) && $PROCESSED["category_parent"] ? $PROCESSED["category_parent"] : "0").", ".$CATEGORY_ID.")";
						$ONLOAD[] = "selectOrder(".$CATEGORY_ID.", ".(isset($PROCESSED["category_parent"]) && $PROCESSED["category_parent"] ? $PROCESSED["category_parent"] : "0").")";
						?>
						<script type="text/javascript">
							jQuery(function(){
								jQuery("#category-form").submit(function(){
									jQuery("#PickList").each(function(){
										jQuery("#PickList option").attr("selected", "selected");	
									});
								});
							});
						</script>
						<h1>Edit Clerkship School Category</h1>
						<form id="category-form" action="<?php echo ENTRADA_URL."/admin/clerkship/categories"."?".replace_query(array("action" => "add", "step" => 2)); ?>" method="post">
						<h2 class="collapsable" title="Clerkship School Category Details Section"><?php echo ($PROCESSED["category_parent"] == 0) ? "Clerkship School Category " : "Category"; ?>Details</h2>
						<div id="category-set-details-section">
							<div class="control-group row-fluid">
								<label for="category_name" class="form-required span4">Category Name:</label>
								<span class="controls span7 offset1">
                                    <input type="text" id="category_name" name="category_name" value="<?php echo ((isset($PROCESSED["category_name"])) ? html_encode($PROCESSED["category_name"]) : ""); ?>" maxlength="60" style="width: 300px" />
                                </span>
							</div>
                            <div class="control-group row-fluid">
                                <label for="category_code" class="form-nrequired span4">Category Code:</label>
								<span class="controls span7 offset1">
                                    <input type="text" id="category_code" name="category_code" value="<?php echo ((isset($PROCESSED["category_code"])) ? html_encode($PROCESSED["category_code"]) : ""); ?>" maxlength="100" style="width: 300px" />
                                </span>
							</div>
                            <table style="width:100%">
                            <?php echo generate_calendars("category", "", true, true, ((isset($PROCESSED["category_start"])) ? $PROCESSED["category_start"] : 0), true, true, ((isset($PROCESSED["category_finish"])) ? $PROCESSED["category_finish"] : 0), false); ?>
                            </table>
							<br />
							<div class="control-group row-fluid">
								<label for="category_desc" class="form-nrequired span4">Category Description: </label>
								<span class="controls span7 offset1">
									<textarea id="category_desc" name="category_desc" style="width: 98%; height: 200px" rows="20" cols="70"><?php echo ((isset($PROCESSED["category_desc"])) ? html_encode($PROCESSED["category_desc"]) : ""); ?></textarea>
								</span>
							</div>
							<br />
							<div class="control-group row-fluid">
								<label for="category_id" class="form-required span4">Category Order:</label>
								<span class="controls span7 offset1">
                                    <div id="selectOrderField"></div>
                                </span>
							</div>
                            <input type="button" class="btn" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/clerkship/categories'" />
                            <input type="submit" class="btn btn-primary pull-right" value="<?php echo $translate->_("global_button_save"); ?>" />        
						</div>
						</form>
						<br />
						<script type="text/javascript">
							var SITE_URL = "<?php echo ENTRADA_URL;?>";
							var EDITABLE = true;
						</script>
						<?php $HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/clerkship_categories.js?release=".html_encode(APPLICATION_VERSION)."\"></script>"; ?>

						<div>
						<style>
								.category-title{
									cursor:pointer;
								}
								.category-list{
									padding-left:5px;
								}
								#category_list_0{
									margin-left:0px;
									padding-left: 0px;
								}
								.categories{
									width:48%;
									float:left;
								}
								.remove{
									display:block;
									cursor:pointer;
									float:right;
								}
								.draggable{
									cursor:pointer;
								}
								.droppable.hover{
									background-color:#ddd;
								}
								.category-title{
									font-weight:bold;
								}
								.category-children{
									margin-top:5px;
								}
								.category-container{
									position:relative;
									padding-right:0px!important;
									margin-right:0px!important;
								}
								.category-controls{
									position:absolute;
									top:5px;
									right:0px;
								}
								li.display-notice{
									border:1px #FC0 solid!important;
									padding-top:10px!important;
									text-align:center;
								}
								.hide{
									display:none;
								}
								.category-controls i {
									display:block;
									width:16px;
									height:16px;
									cursor:pointer;
									float:left;
								}
								.category-controls .category-add-control {
									background-image:url("<?php echo ENTRADA_URL; ?>/images/add.png");
								}
								.category-controls .category-edit-control {
									background-image:url("<?php echo ENTRADA_URL; ?>/images/edit_list.png");								
								}
								.category-controls .category-delete-control {
									background-image:url("<?php echo ENTRADA_URL; ?>/images/action-delete.gif");								
								}
							</style>
							<h2 class="collapsable" title="Child Categories Section">Child Categories</h2>
							<div id="child-categories-section">
                                <a href="#" class="category-add-control btn btn-success pull-right" data-id="<?php echo $CATEGORY_ID; ?>"><i class="icon-plus icon-white"></i>Add New Category</a>
								<div style="clear: both"></div>
								<div data-description="" data-id="<?php echo $CATEGORY_ID; ?>" data-title="" id="category_title_<?php echo $CATEGORY_ID; ?>" class="category-title draggable ui-draggable" style="display:none;"></div>
								<div class="half left" id="children_<?php echo $CATEGORY_ID; ?>">
                                    <?php
                                    $query = "SELECT * FROM `".CLERKSHIP_DATABASE."`.`categories`
                                                WHERE `category_parent` = ".$db->qstr($CATEGORY_ID)."
                                                AND (`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())." OR `organisation_id` IS NULL)
                                                AND `category_status` != 'trash'
                                                ORDER BY `category_order`";
                                    $categories = $db->GetAll($query);
                                    if($categories){ ?>
                                        <ul class="category-list" id="category_list_<?php echo $CATEGORY_ID; ?>">
                                            <?php		
                                            foreach($categories as $category){ 
                                                ?>
                                                <li class = "category-container" id = "category_<?php echo $category["category_id"]; ?>">
                                                    <?php $title = ($category["category_code"]?$category["category_code"].': '.$category["category_name"]:$category["category_name"]); ?>
                                                    <div class="category-title draggable" id="category_title_<?php echo $category["category_id"]; ?>" data-title="<?php echo $title;?>" data-id = "<?php echo $category["category_id"]; ?>" data-code = "<?php echo $category["category_code"]; ?>" data-name = "<?php echo $category["category_name"]; ?>" data-description = "<?php echo $category["category_desc"]; ?>">
                                                        <?php echo $title; ?>
                                                    </div>
                                                    <div class="category-controls">
                                                        <i class="category-edit-control" data-id="<?php echo $category["category_id"]; ?>"></i>
                                                        <i class="category-add-control" data-id="<?php echo $category["category_id"]; ?>"></i>
                                                        <i class="category-delete-control" data-id="<?php echo $category["category_id"]; ?>"></i>
                                                    </div>
                                                    <div class="category-children" id="children_<?php echo $category["category_id"]; ?>">
                                                        <ul class="category-list" id="category_list_<?php echo $category["category_id"]; ?>">
                                                        </ul>
                                                    </div>
                                                </li>
                                                <?php 		
                                            } 
                                            ?>
                                        </ul>
                                    <?php 
                                    } else {
                                        echo display_notice("No Child Categories found. Please click <strong>Add New Category</strong> above to create one.");
                                    }
                                    ?>
								</div>
								<div style="clear:both;"></div>
							</div>
						</div>



						<?php
					default:
					break;
				}
			} else {
				$url = ENTRADA_URL."/admin/clerkship/categories";
				$ONLOAD[]	= "setTimeout('window.location=\\'". $url . "\\'', 5000)";

				$ERROR++;
				$ERRORSTR[] = "In order to update an category, a valid category identifier must be supplied. The provided ID does not exist in the system.  You will be redirected to the System Settings page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

				echo display_error();

				application_log("notice", "Failed to provide category identifer when attempting to edit an category.");
			}
		}
	} else {
		$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/settings/manage/".$MODULE."\\'', 15000)";

		$ERROR++;
		$ERRORSTR[] = "In order to update an category a valid category identifier must be supplied.";

		echo display_error();

		application_log("notice", "Failed to provide category identifer when attempting to edit an category.");
	}
}
?>
