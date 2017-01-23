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
 * Entrada_Utilities
 *
 * The Entrada Utilities class holds all of the globally accessible functions used
 * throughout Entrada. Many of these methods were migrated from functions.inc.php.
 *
 * All methods in this class MUST be public static functions.
 *
 * @author Organisation: Queen's University
 * @author Unit: Faculty of Health Sciences
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @author Developer: Matt Simpson <simpson@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 *
 */
class Entrada_Utilities {
    /**
     * Determines whether or not a PHP session is available.
     *
     * @return bool
     */
    public static function is_session_started() {
        if ( php_sapi_name() !== "cli" ) {
            if ( version_compare(phpversion(), "5.4.0", ">=") ) {
                return session_status() === PHP_SESSION_ACTIVE ? true : false;
            } else {
                return session_id() === "" ? false : true;
            }
        }

        return false;
    }

    /**
     * This function is used to generate the standard start / finish calendars within forms.
     *
     * @param $fieldname
     * @param string $display_name
     * @param bool $show_start
     * @param bool $start_required
     * @param int $current_start
     * @param bool $show_finish
     * @param bool $finish_required
     * @param int $current_finish
     * @param bool $use_times
     * @param bool $add_line_break
     * @param string $display_name_start_suffix
     * @param string $display_name_finish_suffix
     * @return string
     */
    public static function generate_calendars($fieldname, $display_name = "", $show_start = false, $start_required = false, $current_start = 0, $show_finish = false, $finish_required = false, $current_finish = 0, $use_times = true, $add_line_break = false, $display_name_start_suffix = " Start", $display_name_finish_suffix = " Finish") {
        if (!$display_name) {
            $display_name = ucwords(strtolower($fieldname));
        }

        $output = "";

        if ($show_start) {
            $output .= self::generate_calendar($fieldname."_start", $display_name.$display_name_start_suffix, $start_required, $current_start, $use_times, $add_line_break);
        }

        if ($show_finish) {
            $output .= self::generate_calendar($fieldname."_finish", $display_name.$display_name_finish_suffix, $finish_required, $current_finish, $use_times, $add_line_break);
        }

        return $output;
    }

    /**
     * This function is used to generate a calendar with an optional time selector in a form.
     *
     * @param $fieldname
     * @param string $display_name
     * @param bool $required
     * @param int $current_time
     * @param bool $use_times
     * @param bool $add_line_break
     * @param bool $auto_end_date
     * @param bool $disabled
     * @param bool $optional
     * @return string
     */
    public static function generate_calendar($fieldname = "", $display_name = "", $required = false, $current_time = 0, $use_times = true, $add_line_break = false, $auto_end_date = false, $disabled = false, $optional = true) {
        global $ONLOAD;

        if (!$display_name) {
            $display_name = ucwords(strtolower($fieldname));
        }

        $output = "";

        if ($use_times) {
            $ONLOAD[] = "updateTime('".$fieldname."')";
        }

        if ($optional) {
            $ONLOAD[] = "dateLock('".$fieldname."')";
        }

        if ($current_time) {
            $time = 1;
            $time_date = date("Y-m-d", $current_time);
            $time_hour = (int) date("G", $current_time);
            $time_min = (int) date("i", $current_time);
        } else {
            $time = (($required) ? 1 : 0);
            $time_date = "";
            $time_hour = 0;
            $time_min = 0;
        }

        if ($auto_end_date) {
            $readonly = "disabled=\"disabled\"";
        } else {
            $readonly = "";
        }

        $output .= "<div class=\"control-group\">";
        $output .= "    <label id=\"".$fieldname."_text\" for=\"".$fieldname."\" class=\"control-label ".($required ? "form-required" : "form-nrequired")."\">".html_encode($display_name)."</label>";

        $output .= "	<div id=\"".$fieldname."_row\" class=\"controls\">";
        if ($required) {
            $output .= "    <input type=\"hidden\" name=\"" . $fieldname . "\" id=\"" . $fieldname . "\" value=\"1\" checked=\"checked\" />";
        } else {
            $output .= "    <input type=\"checkbox\" name=\"" . $fieldname . "\" id=\"" . $fieldname . "\" value=\"1\"" . (($time) ? " checked=\"checked\"" : "") . " onclick=\"dateLock('" . $fieldname . "')\" />";
        }

        $output .= "        <div class=\"input-append\">";
        $output .= "		    <input placeholder=\"YYYY-MM-DD\" type=\"text\" class=\"input-small\" name=\"".$fieldname."_date\" id=\"".$fieldname."_date\" value=\"".$time_date."\" $readonly autocomplete=\"off\" ".(!$disabled ? "onfocus=\"showCalendar('', this, this, '', '".$fieldname."_date', 0, 20, 1)\"" : "")." style=\"padding-left: 10px\" />&nbsp;";

        if (!$disabled) {
            $output .= "	    <a class=\"btn\" href=\"javascript: showCalendar('', document.getElementById('".$fieldname."_date'), document.getElementById('".$fieldname."_date'), '', '".$fieldname."_date', 0, 20, 1)\" title=\"Show Calendar\" onclick=\"if (!document.getElementById('".$fieldname."').checked) { return false; }\"><i class=\"icon-calendar\"></i></a>";
        }
        $output .= "        </div>";

        if ($use_times) {
            $output .= "	&nbsp;".(((bool) $add_line_break) ? "<br />" : "");
            $output .= "	<select class=\"input-mini\" name=\"".$fieldname."_hour\" id=\"".$fieldname."_hour\" onchange=\"updateTime('".$fieldname."')\">\n";
            foreach (range(0, 23) as $hour) {
                $output .= "	<option value=\"".(($hour < 10) ? "0" : "").$hour."\"".(($hour == $time_hour) ? " selected=\"selected\"" : "").">".(($hour < 10) ? "0" : "").$hour."</option>\n";
            }

            $output .= "	</select>\n";
            $output .= "	:";
            $output .= "	<select class=\"input-mini\" name=\"".$fieldname."_min\" id=\"".$fieldname."_min\" onchange=\"updateTime('".$fieldname."')\">\n";
            foreach (range(0, 59) as $minute) {
                $output .= "	<option value=\"".(($minute < 10) ? "0" : "").$minute."\"".(($minute == $time_min) ? " selected=\"selected\"" : "").">".(($minute < 10) ? "0" : "").$minute."</option>\n";
            }
            $output .= "	</select>\n";
            $output .= "	<span class=\"time-wrapper\">&nbsp;( <span class=\"content-small\" id=\"".$fieldname."_display\"></span> )</span>\n";
        }

        if ($auto_end_date) {
            $output .= "    <div id=\"auto_end_date\" class=\"content-small\" style=\"display: none\"></div>";
        }

        $output .= "	</div>\n";
        $output .= "</div>\n";

        return $output;
    }

    /**
     * Function will validate the calendar that is generated by generate_calendars().
     * @param $fieldname
     * @param bool $require_start
     * @param bool $require_finish
     * @param bool $use_times
     * @return array
     */
    public static function validate_calendars($fieldname = "", $require_start = true, $require_finish = true, $use_times = true) {
        $timestamp_start = 0;
        $timestamp_finish = 0;

        if (($require_start) && ((!isset($_POST[$fieldname."_start"])) || (!$_POST[$fieldname."_start_date"]))) {
            add_error("You must select a start date for the ".$fieldname." calendar entry.");
        } elseif (isset($_POST[$fieldname."_start"]) && $_POST[$fieldname."_start"] == "1") {
            if ((!isset($_POST[$fieldname."_start_date"])) || (!trim($_POST[$fieldname."_start_date"]))) {
                add_error("You have checked <strong>".ucwords(strtolower($fieldname))." Start</strong> but not selected a calendar date.");
            } else {
                if (($use_times) && ((!isset($_POST[$fieldname."_start_hour"])))) {
                    add_error("You have checked <strong>".ucwords(strtolower($fieldname))." Start</strong> but not selected an hour of the day.");
                } else {
                    if (($use_times) && ((!isset($_POST[$fieldname."_start_min"])))) {
                        add_error("You have checked <strong>".ucwords(strtolower($fieldname))." Start</strong> but not selected a minute of the hour.");
                    } else {
                        $pieces	= explode("-", $_POST[$fieldname."_start_date"]);
                        $hour = (($use_times) ? (int) trim($_POST[$fieldname."_start_hour"]) : 0);
                        $minute	= (($use_times) ? (int) trim($_POST[$fieldname."_start_min"]) : 0);
                        $second	= 0;
                        $month = (int) trim($pieces[1]);
                        $day = (int) trim($pieces[2]);
                        $year = (int) trim($pieces[0]);

                        if (checkdate($month, $day, $year)) {
                            $timestamp_start = mktime($hour, $minute, $second, $month, $day, $year);
                        } else {
                            add_error("Invalid format for calendar date.");
                        }
                    }
                }
            }
        }

        if (($require_finish) && ((!isset($_POST[$fieldname."_finish"])) || (!$_POST[$fieldname."_finish_date"]))) {
            add_error("You must select a finish date for the ".$fieldname." calendar entry.");
        } elseif (isset($_POST[$fieldname."_finish"]) && $_POST[$fieldname."_finish"] == "1") {
            if ((!isset($_POST[$fieldname."_finish_date"])) || (!trim($_POST[$fieldname."_finish_date"]))) {
                add_error("You have checked <strong>".ucwords(strtolower($fieldname))." Finish</strong> but not selected a calendar date.");
            } else {
                if (($use_times) && ((!isset($_POST[$fieldname."_finish_hour"])))) {
                    add_error("You have checked <strong>".ucwords(strtolower($fieldname))." Finish</strong> but not selected an hour of the day.");
                } else {
                    if (($use_times) && ((!isset($_POST[$fieldname."_finish_min"])))) {
                        add_error("You have checked <strong>".ucwords(strtolower($fieldname))." Finish</strong> but not selected a minute of the hour.");
                    } else {
                        $pieces	= explode("-", trim($_POST[$fieldname."_finish_date"]));
                        $hour = (($use_times) ? (int) trim($_POST[$fieldname."_finish_hour"]) : 23);
                        $minute	= (($use_times) ? (int) trim($_POST[$fieldname."_finish_min"]) : 59);
                        $second	= ((($use_times) && ((int) trim($_POST[$fieldname."_finish_min"]))) ? 59 : 0);
                        $month = (int) trim($pieces[1]);
                        $day = (int) trim($pieces[2]);
                        $year = (int) trim($pieces[0]);

                        if (checkdate($month, $day, $year)) {
                            $timestamp_finish = mktime($hour, $minute, $second, $month, $day, $year);
                        } else {
                            add_error("Invalid format for calendar date.");
                        }
                    }
                }
            }
        }

        if (($timestamp_start) && ($timestamp_finish) && ($timestamp_finish < $timestamp_start)) {
            add_error("The <strong>".ucwords(strtolower($fieldname))." Finish</strong> date &amp; time you have selected is before the <strong>".ucwords(strtolower($fieldname))." Start</strong> date &amp; time you have selected.");
        }

        return array("start" => $timestamp_start, "finish" => $timestamp_finish);
    }

    /**
     * Function will validate the calendar that is generated by generate_calendar().
     *
     * @param string $label
     * @param string $fieldname
     * @param bool $use_times
     * @param bool $required
     * @return int|void
     */
    public static function validate_calendar($label = "", $fieldname = "", $use_times = true, $required = true) {
        if ((!isset($_POST[$fieldname."_date"])) || (!trim($_POST[$fieldname."_date"]))) {
            if ($required) {
                add_error("<strong>".$label."</strong> date not entered.");
            } else {
                return;
            }
        } elseif (!checkDateFormat($_POST[$fieldname."_date"])) {
            add_error("Invalid format for <strong>".$label."</strong> date.");
        } else {
            if (($use_times) && ((!isset($_POST[$fieldname."_hour"])))) {
                add_error("<strong>".$label."</strong> hour not entered.");
            } else {
                if (($use_times) && ((!isset($_POST[$fieldname."_min"])))) {
                    add_error("<strong>".$label."</strong> minute not entered.");
                } else {
                    $pieces	= explode("-", $_POST[$fieldname."_date"]);
                    $hour	= (($use_times) ? (int) trim($_POST[$fieldname."_hour"]) : 0);
                    $minute	= (($use_times) ? (int) trim($_POST[$fieldname."_min"]) : 0);
                    $second	= 0;
                    $month	= (int) trim($pieces[1]);
                    $day	= (int) trim($pieces[2]);
                    $year	= (int) trim($pieces[0]);

                    $timestamp = mktime($hour, $minute, $second, $month, $day, $year);
                }
            }
        }

        return $timestamp;
    }

    public static function fetchUserPhotoDetails($proxy_id, $privacy_level = 1) {
        global $ENTRADA_ACL, $db;

        $photo_details                      = array();
        $photo_details["official_active"]	= false;
        $photo_details["official_url"]	    = false;
        $photo_details["uploaded_active"]	= false;
        $photo_details["uploaded_url"]	    = false;
        $photo_details["default_photo"]     = "official";

        /**
         * If the photo file actually exists, and either
         * 	If the user is in an administration group, or
         *  If the user is trying to view their own photo, or
         *  If the proxy_id has their privacy set to "Any Information"
         */
        if ((@file_exists(STORAGE_USER_PHOTOS."/".$proxy_id."-official")) && ($ENTRADA_ACL->amIAllowed(new PhotoResource($proxy_id, (int) $privacy_level, "official"), "read"))) {
            $photo_details["official_active"]	= true;
            $photo_details["official_url"]= webservice_url("photo", array($proxy_id, "official"));
        } else {
            $photo_details["official_url"] = ENTRADA_URL."/images/headshot-male.gif";
        }

        /**
         * If the photo file actually exists, and
         * If the uploaded file is active in the user_photos table, and
         * If the proxy_id has their privacy set to "Basic Information" or higher.
         */
        $query			= "SELECT `photo_active` FROM `".AUTH_DATABASE."`.`user_photos` WHERE `photo_type` = '1' AND `photo_active` = '1' AND `proxy_id` = ".$db->qstr($proxy_id);
        $photo_active	= $db->GetOne($query);
        if ((@file_exists(STORAGE_USER_PHOTOS."/".$proxy_id."-upload")) && ($photo_active) && ($ENTRADA_ACL->amIAllowed(new PhotoResource($proxy_id, (int) $privacy_level, "upload"), "read"))) {
            $photo_details["uploaded_active"] = true;
            $photo_details["uploaded_url"] = webservice_url("photo", array($proxy_id, "upload"));
            if (!$photo_details["official_active"]) {
                $photo_details["default_photo"] = "uploaded";
            }
        }


        return $photo_details;
    }

    /**
     * Given an associative array of URL params and filters, output the sanitized version of each param
     * @param  array  $params_to_clean  array("id" => "int", "encoded_param" => "decode")
     * @return array                    array("id" => 123, "encoded_param" => "now decoded")
     */
    public static function getCleanUrlParams($params_to_clean = array()) {
        // Get URL params, clean each then return as variable name ready for use
        parse_str($_SERVER['QUERY_STRING'], $url_params);

        $clean_params = array();

        // clean each requested param using the associated filter
        foreach($params_to_clean as $param => $filter) {
            if (isset($url_params[$param]) && $cleaned_param = clean_input($url_params[$param], $filter)) {
                $clean_params[$param] = $cleaned_param;
            }
        }

        // return array of param => cleaned_value
        return $clean_params;
    }

    /** 
     * Retrieves the value of a param coming from among $_GET variables or from module session
     * @param  string       $param
     * @return $param|false
     */
    public static function getSessionParam($param, $filter = 'int') {
        if ($_GET[$param]) {
            return clean_input($_GET[$param], $filter);
        }
        elseif (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE][$param]) && $_SESSION[APPLICATION_IDENTIFIER][$MODULE][$param]) {
            return $_SESSION[APPLICATION_IDENTIFIER][$MODULE][$param];
        }

        return false;
    }

    /**
     * Sets a session variable in the current module
     * @param string  $param
     * @param any     $value 
     */
    public static function setSessionParam($param, $value) {
        $_SESSION[APPLICATION_IDENTIFIER][$MODULE][$param] = $value;
    }

    /**
     * Get calculated grade from the response items
     * @param array  $items 
     */
    public static function calculate_grade_from_items($items) {
        $weightedScore = 0.0;

        foreach($items as $item) {
            $multi_types = array("DropdownMultipleResponse", "HorizontalMultipleChoiceMultipleResponse", "VerticalMultipleChoiceMultipleResponse");
            $item_type = in_array($item["details"]["type"], $multi_types) ? "multi" : "single";
            $highest = 0;
            $checked_score = 0;
            $score = 0;
            
            foreach($item["item"]["item_responses"] AS $response) {
                $score = (!is_null($response['proxy_score']) ? intval($response["proxy_score"]) : intval($response["item_response_score"]));
                $highest = ($item_type == "single" ? max($score, $highest) : $highest + $score);
                
                if (!is_null($response["score"])) { 
                    $checked_score += $score; 
                }
            }
            $weight = intval($item['item']['weight']);
            $weightedItemScore = ($highest > 0 ? ($checked_score / $highest) * $weight : 0); 
            $weightedScore += $weightedItemScore;
        }

        return round($weightedScore, 2);
    }

    public static function buildInsertUpdateDelete($array) {
        $return = array(
            "insert" => "",
            "update" => "",
            "delete" => ""
        );

        if (isset($array) && is_array($array)) {
            $insert = array();
            $update = array();
            $delete = array();
            $add    = $array["add"];
            $remove = $array["remove"];
            if (isset($add) && is_array($add) && !empty($add)) {
                foreach($add as $key => $item) {
                    // If the key is not set in the remove
                    if (is_array($remove)) {
                        if (!array_key_exists($key, $remove)) {
                            $insert[$key] = unserialize($item);
                        } else {
                            $update[$key] = unserialize($item);
                            unset($remove[$key]);
                        }
                    }
                }
                if (is_array($remove) && !empty($remove)) {
                    foreach ($remove as $key => $item) {
                        $delete[$key] = unserialize($item);
                    }
                }
            } else if (isset($remove) && is_array($remove) && !empty($remove)) {
                // There is no add and there are some to remove, so they're delete not update
                foreach($remove as $key => $item) {
                    $delete[$key] = unserialize($item);
                }
            }
            $return = array(
                "insert" => $insert,
                "update" => $update,
                "delete" => $delete
            );
        }
        return $return;
    }

    public static function buildAudienceArray($audience_array) {
        if (isset($audience_array) && is_array($audience_array)) {
            $new_audience = array(
                "audience_type"     => $audience_array["audience_type"],
                "audience_value"    => (int)$audience_array["audience_value"],
                "custom_time"       => (int)$audience_array["custom_time"],
                "custom_time_start" => (int)$audience_array["custom_time_start"],
                "custom_time_end"   => (int)$audience_array["custom_time_end"]
            );
        }
        return $new_audience;
    }

}