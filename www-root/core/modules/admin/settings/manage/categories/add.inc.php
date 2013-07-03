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
 * This file is used to add categories in the entrada_clerkship.categories table.
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
} elseif (!$ENTRADA_ACL->amIAllowed('categories', 'create', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/settings/manage/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	
	if (isset($_GET["mode"]) && $_GET["mode"] == "ajax") {
		$MODE = "ajax";
	}
	if (isset($_GET["parent_id"]) && ($id = clean_input($_GET["parent_id"], array("notags", "trim")))) {
		$PARENT_ID = $id;
	}
	
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

    if (isset($MODE) && $MODE == "ajax" && isset($PARENT_ID) && $PARENT_ID) {
        ob_clear_open_buffers();
        $time = time();

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
                    $query = "SELECT MAX(`category_order`) FROM `".CLERKSHIP_DATABASE."`.`categories`
                                WHERE `category_parent` = ".$db->qstr($PARENT_ID)."
                                AND `category_status` != 'trash'
                                AND (`organisation_id` = ".$db->qstr($ORGANISATION_ID)." OR `organisation_id` IS NULL)";
                    $count = $db->GetOne($query);
                    if (($count + 1) != $PROCESSED["category_order"]) {
                        $query = "SELECT `category_id` FROM `".CLERKSHIP_DATABASE."`.`categories`
                                    WHERE `category_parent` = ".$db->qstr($PARENT_ID)."
                                    AND (`organisation_id` = ".$db->qstr($ORGANISATION_ID)." OR `organisation_id` IS NULL)
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
                    $PROCESSED["category_parent"] = $PARENT_ID;
                    $PROCESSED["organisation_id"] = $ORGANISATION_ID;
                    $PROCESSED["updated_date"] = time();
                    $PROCESSED["updated_by"] = $ENTRADA_USER->getID();

                    if (!$db->AutoExecute("`".CLERKSHIP_DATABASE."`.`categories`", $PROCESSED, "INSERT") || !($category_id = $db->Insert_Id())) {

                        echo json_encode(array("status" => "error", "msg" => "There was a problem updating this category in the system. The system administrator was informed of this error; please try again later."));

                        application_log("error", "There was an error updating an category. Database said: ".$db->ErrorMsg());
                    } else {
                        $PROCESSED["category_id"] = $category_id;
                        echo json_encode(array("status" => "success", "updates" => $PROCESSED));
                    }

                } else {
                    echo json_encode(array("status" => "error", "msg" => implode("<br />", $ERRORSTR)));
                }
            break;
            case "1" :
            default :
                ?>
                <script type="text/javascript">
                function selectCategory(parent_id) {
                    new Ajax.Updater('m_selectCategoryField_<?php echo $time; ?>', '<?php echo ENTRADA_URL; ?>/api/categories-list.api.php', {parameters: {'pid': parent_id, 'organisation_id': <?php echo $ORGANISATION_ID; ?>}});
                    return;
                }
                function selectOrder(parent_id) {
                    new Ajax.Updater('m_selectOrderField_<?php echo $time; ?>', '<?php echo ENTRADA_URL; ?>/api/categories-list.api.php', {parameters: {'type': 'order', 'pid': parent_id, 'organisation_id': <?php echo $ORGANISATION_ID; ?>}});
                    return;
                }
                jQuery(function(){
                    selectCategory(<?php echo (isset($PARENT_ID) && $PARENT_ID ? $PARENT_ID : "0"); ?>);
                    selectOrder(<?php echo (isset($PARENT_ID) && $PARENT_ID ? $PARENT_ID : "0"); ?>);
                });
                </script>
                <form id="category-form" action="<?php echo ENTRADA_URL."/admin/settings/manage/categories"."?".replace_query(array("action" => "add", "step" => 2, "mode" => "ajax")); ?>" method="post">
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
                            <?php echo generate_calendars("category", "", true, false, ((isset($PROCESSED["category_start"])) ? $PROCESSED["category_start"] : 0), true, false, ((isset($PROCESSED["category_finish"])) ? $PROCESSED["category_finish"] : 0), false); ?>
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
                                <div id="m_selectOrderField_<?php echo $time; ?>"></div>
                            </span>
                        </div>
                    </div>
                </form>
                <?php
            break;
        }
        exit;
    } else {
        $BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/settings/manage/categories?".replace_query(array("section" => "add")), "title" => "Adding Clerkship Category");

        // Error Checking
        if ($STEP == 2) {
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
                                WHERE `category_parent` = ".$db->qstr($PARENT_ID)."
                                AND (`organisation_id` = ".$db->qstr($ORGANISATION_ID)." OR `organisation_id` IS NULL)
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
                $PROCESSED["category_parent"] = 0;
                $PROCESSED["organisation_id"] = $ORGANISATION_ID;
                $PROCESSED["updated_date"] = time();
                $PROCESSED["updated_by"] = $ENTRADA_USER->getID();

                if ($db->AutoExecute("`".CLERKSHIP_DATABASE."`.`categories`", $PROCESSED, "INSERT") || !($category_id = $db->Insert_Id())) {
                    if (!$ERROR) {
                        $url = ENTRADA_URL . "/admin/settings/manage/categories?org=".$ORGANISATION_ID;

                        $SUCCESS++;
                        $SUCCESSSTR[] = "You have successfully added <strong>".html_encode($PROCESSED["category_name"])."</strong> to the system.<br /><br />You will now be redirected to the categories index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

                        $ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";

                        application_log("success", "New Category [".$category_id."] added to the system.");		
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
                            function selectCategory(parent_id) {
                                new Ajax.Updater('selectCategoryField', '".ENTRADA_URL."/api/categories-list.api.php', {parameters: {'pid': parent_id, 'organisation_id': ".$ORGANISATION_ID."}});
                                return;
                            }
                            function selectOrder(parent_id) {
                                new Ajax.Updater('selectOrderField', '".ENTRADA_URL."/api/categories-list.api.php', {parameters: {'type': 'order', 'pid': parent_id, 'organisation_id': ".$ORGANISATION_ID."}});
                                return;
                            }
                            </script>";
                $ONLOAD[] = "selectCategory(".(isset($PARENT_ID) && $PARENT_ID ? $PARENT_ID : "0").")";
                $ONLOAD[] = "selectOrder(".(isset($PARENT_ID) && $PARENT_ID ? $PARENT_ID : "0").")";
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
                <h1>Add Clerkship Category</h1>
                <form id="category-form" action="<?php echo ENTRADA_URL."/admin/settings/manage/categories"."?".replace_query(array("action" => "add", "step" => 2)); ?>" method="post">
                <h2 class="collapsable" title="Clerkship Category Details Section">Category Details</h2>
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
                    <input type="button" class="btn" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/settings/manage/categories'" />
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
                <?php
            default:
            break;
        }
    }
}
?>
