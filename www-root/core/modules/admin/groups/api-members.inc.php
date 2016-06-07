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
 * API to handle interaction with adding members to groups
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Travis Obregon <travismobregon@gmail.com>
 * @copyright Copyright 2015 Queen's University. All Rights Reserved.
 *
 */
if ((!defined("PARENT_INCLUDED")) || (!defined("IN_GROUPS"))) {
    exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
    header("Location: ".ENTRADA_URL);
    exit;
} elseif (!$ENTRADA_ACL->amIAllowed("group", "update", false)) {
    add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

    echo display_error();

    application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
    ob_clear_open_buffers();

    $request_method = strtoupper(clean_input($_SERVER['REQUEST_METHOD'], "alpha"));

    $request = ${"_" . $request_method};

    switch ($request_method) {
        case "GET" :
            switch ($request["method"]) {
                case "get-users-by-group" :
                    if (isset($request["search_value"]) && $tmp_input = clean_input(strtolower($request["search_value"]), array("trim", "striptags"))) {
                        $PROCESSED["search_value"] = $tmp_input;
                    } else {
                        $PROCESSED["search_value"] = "";
                    }

                    if (isset($request["group"]) && $tmp_input = clean_input(strtolower($request["group"]), array("trim", "striptags"))) {
                        $PROCESSED["group"] = $tmp_input;
                    } else {
                        $PROCESSED["group"] = "";
                    }

                    if (isset($request["excluded_target_ids"]) && $tmp_input = clean_input(strtolower($request["excluded_target_ids"]), array("trim", "striptags"))) {
                        $PROCESSED["excluded_target_ids"] = $tmp_input;
                    } else {
                        $PROCESSED["excluded_target_ids"] = 0;
                    }

                    $users = User::fetchUsersByGroups($PROCESSED["search_value"], $PROCESSED["group"], null, $PROCESSED["excluded_target_ids"]);

                    $data = array();

                    if ($users) {
                        foreach ($users as $user) {
                            $data[] = array("target_id" => $user["proxy_id"], "target_label" => $user["firstname"] . " " . $user["lastname"], "lastname" => $user["lastname"], "role" => $translate->_(ucfirst($user["role"])), "email" => $user["email"]);
                        }
                    }

                    if ($data) {
                        echo json_encode(array("status" => "success", "data" => $data));
                    } else {
                        echo json_encode(array("status" => "error", "data" => $translate->_("No Users found")));
                    }
                break;
                case "get-resident-users" :
                    if (isset($request["search_value"]) && $tmp_input = clean_input(strtolower($request["search_value"]), array("trim", "striptags"))) {
                        $PROCESSED["search_value"] = $tmp_input;
                    } else {
                        $PROCESSED["search_value"] = "";
                    }

                    if (isset($request["excluded_target_ids"]) && $tmp_input = clean_input(strtolower($request["excluded_target_ids"]), array("trim", "striptags"))) {
                        $PROCESSED["excluded_target_ids"] = $tmp_input;
                    } else {
                        $PROCESSED["excluded_target_ids"] = 0;
                    }

                    $users = User::fetchAllResidents($PROCESSED["search_value"], $PROCESSED["excluded_target_ids"]);

                    $data = array();

                    if ($users) {
                        foreach ($users as $user) {
                            $data[] = array("target_id" => $user["proxy_id"], "target_label" => $user["firstname"] . " " . $user["lastname"], "lastname" => $user["lastname"], "role" => $translate->_(ucfirst($user["role"])), "email" => $user["email"]);
                        }
                    }

                    if ($data) {
                        echo json_encode(array("status" => "success", "data" => $data));
                    } else {
                        echo json_encode(array("status" => "error", "data" => $translate->_("No Residents found")));
                    }
                break;
                case "get-organisations" :
                    if (isset($request["search_value"]) && $tmp_input = clean_input(strtolower($request["search_value"]), array("trim", "striptags"))) {
                        $PROCESSED["search_value"] = $tmp_input;
                    } else {
                        $PROCESSED["search_value"] = "";
                    }

                    $organisations = Models_Organisation::fetchAllOrganisations($PROCESSED["search_value"]);

                    if ($organisations) {
                        $data = array();

                        foreach ($organisations as $organisation) {
                            $data[] = array("target_id" => $organisation["organisation_id"], "target_parent" => "0", "target_label" => $organisation["organisation_title"], "target_children" => "1");
                        }

                        echo json_encode(array("status" => "success", "data" => $data, "parent_id" => "0", "parent_name" => "0", "level_selectable" => false));
                    } else {
                        echo json_encode(array("status" => "error", "data" => $translate->_("No Organisations found")));
                    }
                break;
                case "get-students" :
                    if (isset($request["search_value"]) && $tmp_input = clean_input(strtolower($request["search_value"]), array("trim", "striptags"))) {
                        $PROCESSED["search_value"] = $tmp_input;
                    } else {
                        $PROCESSED["search_value"] = "";
                    }

                    if (isset($request["parent_id"]) && $tmp_input = clean_input(strtolower($request["parent_id"]), array("trim", "int"))) {
                        $PROCESSED["parent_id"] = $tmp_input;
                    } else {
                        $PROCESSED["parent_id"] = "0";
                    }

                    if (isset($request["context"]) && $tmp_input = clean_input(strtolower($request["context"]), array("trim", "striptags"))) {
                        $PROCESSED["context"] = $tmp_input;
                    }

                    if (isset($request["previous_context"]) && $tmp_input = clean_input(strtolower($request["previous_context"]), array("trim", "striptags"))) {
                        $PROCESSED["previous_context"] = $tmp_input;
                    }

                    if (isset($request["next_context"]) && $tmp_input = clean_input(strtolower($request["next_context"]), array("trim", "striptags"))) {
                        $PROCESSED["next_context"] = $tmp_input;
                    }

                    if (isset($request["current_context"]) && $tmp_input = clean_input(strtolower($request["current_context"]), array("trim", "striptags"))) {
                        $PROCESSED["current_context"] = $tmp_input;
                    }

                    if (isset($request["organisation_id"]) && $tmp_input = clean_input(strtolower($request["organisation_id"]), array("trim", "int"))) {
                        $PROCESSED["organisation_id"] = $tmp_input;
                    } else {
                        $PROCESSED["organisation_id"] = 0;
                    }

                    if (isset($request["group_type"]) && $tmp_input = clean_input(strtolower($request["group_type"]), array("trim", "int"))) {
                        $PROCESSED["group_type"] = $tmp_input;
                    } else {
                        $PROCESSED["group_type"] = 0;
                    }

                    if (isset($request["excluded_target_ids"]) && $tmp_input = clean_input(strtolower($request["excluded_target_ids"]), array("trim", "striptags"))) {
                        $PROCESSED["excluded_target_ids"] = $tmp_input;
                    } else {
                        $PROCESSED["excluded_target_ids"] = 0;
                    }

                    if ($PROCESSED["parent_id"] != "0") {
                        if ($PROCESSED["context"] == "previous" && $PROCESSED["previous_context"] == "organisation_id" ||
                            $PROCESSED["context"] == "next" && $PROCESSED["next_context"] == "organisation_id" ||
                            $PROCESSED["context"] == "search" && $PROCESSED["current_context"] == "organisation_id") {

                            $organisation = Models_Organisation::fetchRowByID($PROCESSED["parent_id"]);

                            $data = array();
                            $data[] = array("target_id" => 1, "target_parent" => strval($PROCESSED["parent_id"]), "target_label" => "All Students", "target_children" => "1");
                            $data[] = array("target_id" => 2, "target_parent" => strval($PROCESSED["parent_id"]), "target_label" => "Students By Cohort", "target_children" => "1");
                            $data[] = array("target_id" => 3, "target_parent" => strval($PROCESSED["parent_id"]), "target_label" => "Students By Course List", "target_children" => "1");

                            if ($PROCESSED["context"] == "search" && $PROCESSED["search_value"]) {
                                $search_value = $PROCESSED["search_value"];

                                $data = array_filter($data, function ($element) use ($search_value) {
                                    $pos = stripos($element["target_label"], $search_value);

                                    return $pos !== false;
                                });
                            }

                            if ($data) {
                                echo json_encode(array("status" => "success", "data" => $data, "parent_id" => "0", "parent_name" => ($organisation ? $organisation->getOrganisationTitle() : "0"), "level_selectable" => false, "next_context" => "group_type", "current_context" => "organisation_id", "organisation_id" => $PROCESSED["parent_id"]));
                            } else {
                                echo json_encode(array("status" => "error", "data" => $translate->_("No Search Results Found")));
                            }
                        } elseif ($PROCESSED["context"] == "previous" && $PROCESSED["previous_context"] == "group_type" ||
                                  $PROCESSED["context"] == "next" && $PROCESSED["next_context"] == "group_type" ||
                                  $PROCESSED["context"] == "search" && $PROCESSED["current_context"] == "group_type") {
                            $group_type = "";

                            switch ($PROCESSED["parent_id"]) {
                                case 1 :
                                    $group_type = "all_students";
                                break;
                                case 2 :
                                    $group_type = "cohort";
                                break;
                                case 3 :
                                    $group_type = "course_list";
                                break;
                            }

                            if ($group_type == "all_students") {
                                $users = Models_Organisation::fetchOrganisationUsersWithoutAppID($PROCESSED["search_value"], $PROCESSED["organisation_id"], "student", $PROCESSED["excluded_target_ids"]);

                                $data = array();

                                if ($users) {
                                    foreach ($users as $user) {
                                        $data[] = array("target_id" => $user["proxy_id"], "target_parent" => strval($PROCESSED["parent_id"]), "target_label" => $user["firstname"] . " " . $user["lastname"], "lastname" => $user["lastname"], "role" => $translate->_(ucfirst($user["role"])), "email" => $user["email"]);
                                    }
                                }

                                if ($data) {
                                    echo json_encode(array("status" => "success", "data" => $data, "parent_id" => $PROCESSED["organisation_id"], "parent_name" => "All Students in " . Models_Organisation::fetchRowByID($PROCESSED["organisation_id"])->getOrganisationTitle(), "previous_context" => "organisation_id", "current_context" => "group_type"));
                                } else {
                                    echo json_encode(array("status" => "error", "data" => $translate->_("No Students Found")));
                                }
                            } else {
                                $groups = Models_Group::fetchAllByGroupType($group_type, $PROCESSED["organisation_id"], $PROCESSED["search_value"]);

                                if ($groups) {
                                    $data = array();

                                    foreach ($groups as $group) {
                                        $users = Models_Group_Member::getUsersByGroupIDWithoutAppID($group->getID(), $PROCESSED["search_value"], 1, $PROCESSED["excluded_target_ids"]);

                                        $data[] = array("target_id" => $group->getID(), "target_parent" => strval($PROCESSED["parent_id"]), "target_label" => $group->getGroupName(), "target_children" => strval(count($users)));
                                    }

                                    echo json_encode(array("status" => "success", "data" => $data, "parent_id" => strval($PROCESSED["organisation_id"]), "parent_name" => ($group_type == "cohort" ? "Students By Cohort" : "Students By Course List"), "level_selectable" => false, "previous_context" => "organisation_id", "next_context" => "group_id", "current_context" => "group_type", "group_type" => $PROCESSED["parent_id"]));
                                } else {
                                    echo json_encode(array("status" => "error", "data" => $translate->_("No " . (!$group_type ? ucfirst(str_replace("_", " ", $group_type)) : "Group") . "s found")));
                                }
                            }
                        } elseif ($PROCESSED["context"] == "next" && $PROCESSED["next_context"] == "group_id" ||
                                  $PROCESSED["context"] == "search" && $PROCESSED["current_context"] == "group_id") {
                            $group = Models_Group::fetchRowByID($PROCESSED["parent_id"]);

                            if ($group) {
                                $users = Models_Group_Member::getUsersByGroupIDWithoutAppID($PROCESSED["parent_id"], $PROCESSED["search_value"], 1, $PROCESSED["excluded_target_ids"]);
                            }

                            if ($users) {
                                $data = array();

                                foreach ($users as $user) {
                                    $data[] = array("target_id" => $user->getProxyId(), "target_parent" => strval($PROCESSED["parent_id"]), "target_label" => $user->getFirstname() . " " . $user->getLastname(), "target_children" => "0", "lastname" => $user->getLastname(), "role" => $translate->_("Learner"), "email" => $user->getEmail());
                                }

                                echo json_encode(array("status" => "success", "data" => $data, "parent_id" => strval($PROCESSED["group_type"]), "parent_name" => $group->getGroupName(), "previous_context" => "group_type", "current_context" => "group_id"));
                            } else {
                                echo json_encode(array("status" => "error", "data" => $translate->_("No Users found"), "parent_id" => strval($PROCESSED["group_type"]), "parent_name" => $group->getGroupName(), "previous_context" => "group_type", "current_context" => "group_id"));
                            }
                        }
                    } else {
                        $organisations = Models_Organisation::fetchAllOrganisations($PROCESSED["search_value"]);

                        if ($organisations) {
                            $data = array();

                            foreach ($organisations as $organisation) {
                                $data[] = array("target_id" => $organisation["organisation_id"], "target_parent" => "0", "target_label" => $organisation["organisation_title"], "target_children" => "1");
                            }

                            echo json_encode(array("status" => "success", "data" => $data, "parent_id" => "0", "parent_name" => "0", "level_selectable" => false, "next_context" => "organisation_id"));
                        } else {
                            echo json_encode(array("status" => "error", "data" => $translate->_("No Organisations found")));
                        }
                    }
                break;
            }
        break;
    }
    exit;
}