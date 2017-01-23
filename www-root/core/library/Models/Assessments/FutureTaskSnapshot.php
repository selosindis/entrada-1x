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
 * @author Organisation: Queen's University
 * @author Developer: Joshua Belanger <jb301@queensu.ca>
 * @copyright Copyright 2016 Queen's University. All Rights Reserved.
 */
class Models_Assessments_FutureTaskSnapshot extends Models_Base {
    protected $future_task_id, $adistribution_id, $assessor_type, $assessor_value, $target_type, $target_value, $title, $rotation_start_date, $rotation_end_date, $delivery_date, $schedule_details, $created_date, $created_by;

    protected static $table_name = "cbl_assessment_ss_future_tasks";
    protected static $primary_key = "future_task_id";
    protected static $default_sort_column = "future_task_id";

    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }

    public function getID() {
        return $this->future_task_id;
    }

    public function getDistributionID() {
        return $this->adistribution_id;
    }

    public function getAssessorType() {
        return $this->assessor_type;
    }

    public function getAssessorValue() {
        return $this->assessor_value;
    }

    public function getTargetType() {
        return $this->target_type;
    }

    public function getTargetValue() {
        return $this->target_value;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getRotationStartDate() {
        return $this->rotation_start_date;
    }

    public function getRotationEndDate() {
        return $this->rotation_end_date;
    }

    public function getDeliveryDate() {
        return $this->delivery_date;
    }

    public function getScheduleDetails() {
        return $this->schedule_details;
    }

    public function getTarget() {

        if ($this->getTargetValue() && $this->getTargetType()) {
            switch ($this->getTargetType()) {
                case "proxy_id":
                    $member_details = Models_User::fetchRowByID($this->getTargetValue());
                    if ($member_details) {
                        $prefix = $member_details->getPrefix();
                        $target_name = (($prefix) ? $prefix . " " : "") . $member_details->getFirstname() . " " . $member_details->getLastname();
                    } else {
                        $target_name = "N/A";
                    }
                    break;
                case "schedule_id":
                    $schedule = Models_Schedule::fetchRowByID($this->getTargetValue());
                    if ($schedule) {
                        $target_name = $schedule->getTitle();
                    } else {
                        $target_name = "N/A";
                    }
                    break;
                case "course_id":
                    $course = Models_Course::fetchRowByID($this->getTargetValue());
                    if ($course) {
                        $target_name = $course->getCourseName() . " (" . $course->getCourseCode() . ")";
                    } else {
                        $target_name = "N/A";
                    }
                    break;
                default:
                    $target_name = "N/A";
                    break;
            }

            return $target_name;
        } else {
            return false;
        }
    }

    public function getTargetGroup($organisation_id) {
        if ($this->getTargetValue()) {
            global $db;
            $query = "  SELECT `group`
                        FROM `" . AUTH_DATABASE . "`.`user_access`
                        WHERE `user_id` = ?
                        AND `organisation_id` = ?";
            $results = $db->GetRow($query, array($this->getTargetValue(), $organisation_id));
            if ($results) {
                return $results["group"];
            }
        }
        return false;
    }

    public function getTargetRole($organisation_id) {
        if ($this->getTargetValue()) {
            global $db;
            $query = "  SELECT `role`
                        FROM `" . AUTH_DATABASE . "`.`user_access`
                        WHERE `user_id` = ?
                        AND `organisation_id` = ?";
            $results = $db->GetRow($query, array($this->getTargetValue(), $organisation_id));
            if ($results) {
                return $results["role"];
            }
        }
        return false;
    }

    public function getAssessorGroup($organisation_id) {
        if ($this->getAssessorValue()) {
            global $db;
            $query = "  SELECT `group`
                        FROM `" . AUTH_DATABASE . "`.`user_access`
                        WHERE `user_id` = ?
                        AND `organisation_id` = ?";
            $results = $db->GetRow($query, array($this->getAssessorValue(), $organisation_id));
            if ($results) {
                return $results["group"];
            }
        }
        return false;
    }

    public function getAssessorRole($organisation_id) {
        if ($this->getAssessorValue()) {
            global $db;
            $query = "  SELECT `role`
                        FROM `" . AUTH_DATABASE . "`.`user_access`
                        WHERE `user_id` = ?
                        AND `organisation_id` = ?";
            $results = $db->GetRow($query, array($this->getAssessorValue(), $organisation_id));
            if ($results) {
                return $results["role"];
            }
        }
        return false;
    }

    public static function fetchRowByDistributionIDAssessorValueTargetValueDeliveryDate($distribution_id, $assessor_value, $target_value, $delivery_date, $deleted_date = NULL) {
        $self = new self();
        return $self->fetchRow(array(
            array("key" => "adistribution_id", "value" => $distribution_id, "method" => "="),
            array("key" => "assessor_value", "value" => $assessor_value, "method" => "="),
            array("key" => "target_value", "value" => $target_value, "method" => "="),
            array("key" => "delivery_date", "value" => $delivery_date, "method" => "="),
            array("key" => "deleted_date", "value" => ($deleted_date ? $deleted_date : NULL), "method" => ($deleted_date ? "<=" : "IS"))
        ));
    }

    public static function fetchAllByAssessorTypeAssessorValueSortDeliveryDateRotationDatesDesc($assessor_value, $current_section = "assessments", $filters = array(), $search_value = null, $start_date = null, $end_date = null, $is_external = false, $limit = 0, $offset = 0) {
        global $db;
        $assessments = false;

        $course_id_list = Models_Course::getActiveUserCoursesIDList();

        $AND_course_in = ($current_section == "assessments" || empty($course_id_list) ? " " : "  AND b.`course_id` IN (" . implode(",", $course_id_list) . ") ");
        $AND_cperiod_in = "";
        $AND_course_filter_in = "";
        $AND_title_like = "";
        $AND_date_greater = "";
        $AND_date_less = "";
        $LIMIT = "";
        $OFFSET = "";

        if ($filters) {
            if (array_key_exists("cperiod", $filters)) {
                $AND_cperiod_in = " AND b.`cperiod_id` IN (" . implode(",", array_keys($filters["cperiod"])) . ") ";
            }

            if (array_key_exists("program", $filters)) {
                $AND_course_filter_in = "  AND b.`course_id` IN (" . implode(",", array_keys($filters["program"])) . ") ";
            }
        }

        if ($search_value != "" && $search_value != null) {
            $AND_title_like = "     AND b.`title` LIKE (". $db->qstr("%". $search_value ."%") .") ";
        }

        if ($start_date != "" && $start_date != null) {
            $AND_date_greater = "   AND a.`delivery_date` >= ". $db->qstr($start_date) . "";
        }

        if ($end_date != "" && $end_date != null) {
            $AND_date_less = "      AND a.`delivery_date` <= ". $db->qstr($end_date) . "";
        }

        if ($limit) {
            $LIMIT = " LIMIT $limit";
        }

        if ($offset) {
            $OFFSET = " OFFSET $offset";
        }

        $assessor_type = ($is_external) ? 'external' : 'internal';

        $query = "          SELECT a.* FROM `cbl_assessment_ss_future_tasks` AS a
                            JOIN `cbl_assessment_distributions` AS b
                            ON a.`adistribution_id` = b.`adistribution_id`                    
                            JOIN `courses` AS c
                            ON b.`course_id` = c.`course_id`                            
                            WHERE a.`assessor_type` = ?
                            AND a.`assessor_value` = ?
                            AND b.`deleted_date` IS NULL
                            
                            $AND_course_in
                            $AND_course_filter_in
                            $AND_cperiod_in
                            $AND_title_like
                            $AND_date_greater
                            $AND_date_less
                            
                            ORDER BY a.`delivery_date` DESC, 
                            a.`rotation_start_date` DESC,
                            a.`rotation_end_date` DESC
                            $LIMIT $OFFSET
                            ";

        $results = $db->GetAll($query, array($assessor_type, $assessor_value));
        if ($results) {
            foreach ($results as $result) {
                $assessments[] = new self($result);
            }
        }

        return $assessments;
    }

    public static function fetchAllByTargetTypeTargetValueSortDeliveryDateRotationDatesDesc($target_type, $target_value, $current_section = "assessments", $filters = array(), $search_value = null, $start_date = null, $end_date = null, $limit = 10, $offset = 0) {
        global $db;
        $assessments = false;

        $course_id_list = Models_Course::getActiveUserCoursesIDList();

        $AND_course_in = ($current_section == "assessments" || empty($course_id_list) ? " " : "  AND b.`course_id` IN (" . implode(",", $course_id_list) . ") ");
        $AND_cperiod_in = "";
        $AND_course_filter_in = "";
        $AND_title_like = "";
        $AND_date_greater = "";
        $AND_date_less = "";
        $LIMIT = "";
        $OFFSET = "";

        if ($filters) {
            if (array_key_exists("cperiod", $filters)) {
                $AND_cperiod_in = " AND b.`cperiod_id` IN (" . implode(",", array_keys($filters["cperiod"])) . ") ";
            }

            if (array_key_exists("program", $filters)) {
                $AND_course_filter_in = "  AND b.`course_id` IN (" . implode(",", array_keys($filters["program"])) . ") ";
            }
        }

        if ($search_value != "" && $search_value != null) {
            $AND_title_like = "     AND b.`title` LIKE (". $db->qstr("%". $search_value ."%") .") ";
        }

        if ($start_date != "" && $start_date != null) {
            $AND_date_greater = "   AND b.`delivery_date` >= ". $db->qstr($start_date) . "";
        }

        if ($end_date != "" && $end_date != null) {
            $AND_date_less = "      AND b.`delivery_date` <= ". $db->qstr($end_date) . "";
        }

        if ($limit) {
            $LIMIT = " LIMIT $limit";
        }

        if ($offset) {
            $OFFSET = " OFFSET $offset";
        }

        $query = "          SELECT a.* FROM `cbl_assessment_ss_future_tasks` AS a
                            JOIN `cbl_assessment_distributions` AS b
                            ON a.`adistribution_id` = b.`adistribution_id`                            
                            JOIN `courses` AS c
                            ON b.`course_id` = c.`course_id`                         
                            WHERE a.`target_type` = ?
                            AND a.`target_value` = ?
                            AND b.`deleted_date` IS NULL
                            
                            $AND_course_in
                            $AND_course_filter_in
                            $AND_cperiod_in
                            $AND_title_like
                            $AND_date_greater
                            $AND_date_less
                            
                            ORDER BY a.`delivery_date` DESC, 
                            a.`rotation_start_date` DESC,
                            a.`rotation_end_date` DESC
                            $LIMIT $OFFSET
                            ";

        $results = $db->GetAll($query, array($target_type, $target_value));
        if ($results) {
            foreach ($results as $result) {
                $assessments[] = new self($result);
            }
        }

        return $assessments;
    }

    public static function truncate() {
        global $db;
        $query = "TRUNCATE `" . static::$table_name . "`";
        if (!$db->Execute($query)) {
            application_log("error", "Unable to truncate " . static::$table_name . ". DB said: " . $db->ErrorMsg());
        }
    }

    public function delete() {
        global $db;

        $query = "DELETE FROM " . static::$table_name . " WHERE `future_task_id` = ?";
        $result = $db->Execute($query, array($this->getID()));

        return $result;
    }
}