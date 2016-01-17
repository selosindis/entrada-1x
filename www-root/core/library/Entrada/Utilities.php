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
        $output .= "		    <input type=\"text\" class=\"input-small\" name=\"".$fieldname."_date\" id=\"".$fieldname."_date\" value=\"".$time_date."\" $readonly autocomplete=\"off\" ".(!$disabled ? "onfocus=\"showCalendar('', this, this, '', '".$fieldname."_date', 0, 20, 1)\"" : "")." style=\"padding-left: 10px\" />&nbsp;";

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

                        $timestamp_start = mktime($hour, $minute, $second, $month, $day, $year);
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

                        $timestamp_finish = mktime($hour, $minute, $second, $month, $day, $year);
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
}