<?php

class Models_Curriculum_Period extends Models_Base {

    protected $cperiod_id, $curriculum_type_id, $curriculum_period_title, $start_date, $finish_date, $active;

    protected static $table_name = "curriculum_periods";
    protected static $default_sort_column = "start_date";
    protected static $primary_key = "cperiod_id";

    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }

    public function getCperiodID()
    {
        return $this->cperiod_id;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function getCurriculumPeriodTitle()
    {
        return $this->curriculum_period_title;
    }

    public function getCurriculumTypeID()
    {
        return $this->curriculum_type_id;
    }

    public function getFinishDate()
    {
        return $this->finish_date;
    }

    public function getStartDate()
    {
        return $this->start_date;
    }

    public static function fetchRowByID($cperiod_id) {
        $self = new self();
        return $self->fetchRow(array("cperiod_id" => $cperiod_id));
    }

    public static function fetchRowByMultipleIDAsc($cperiod_id_array = array()) {
        $self = new self();

        $constraints = array(
            array(
                "key"       => "creport_id",
                "value"     => $cperiod_id_array,
                "method"    => "IN"
            )
        );

        $results = $self->fetchAll($constraints, "=", "AND", "start_date", "start_date");

        if ($results) {
            return $results[0]->toArray();
        }

        return false;

    }

    public static function fetchAllByCurriculumType($curriculum_type_id) {
        $self = new self();
        return $self->fetchAll(array("curriculum_type_id" => $curriculum_type_id), $default_method = "=", $default_mode = "AND", $sort_column = "cperiod_id", $sort_order = "DESC", $limit = null);
    }

    /**
     * Takes in a curriculum type id and an option search value
     * Gets all curriculum periods using the title or start and finsh date for filters
     * Returns a list of curriculum periods
     * @param $curriculum_type_id
     * @param $search_value
     * @return array
     */
    
    public static function fetchAllByCurriculumTypeSearchTerm($curriculum_type_id, $search_value = null) {
        global $db;

        $output = array();

        $query = "      SELECT * FROM `curriculum_periods`
                        WHERE `curriculum_type_id` = ? 
                        AND `active` = 1";

        if($search_value != null) {
            $first_pos = strpos($search_value, ' ');
            $second_pos = strpos($search_value, ' ', $first_pos + 1);
            if($first_pos) {
                if (!$second_pos || $second_pos == strlen($search_value) - 1) {
                    $search_value = substr_replace($search_value, "", $first_pos, strlen($search_value) - 1);
                } else if ($first_pos != $second_pos) {
                    $search_value = substr_replace($search_value, " ", $first_pos, $second_pos - $first_pos + 1);
                }
            }

            $query .= " AND (
                                `curriculum_period_title` LIKE (" . $db->qstr("%" . $search_value . "%") . ")
                                OR CONCAT( FROM_UNIXTIME(`start_date`,'%Y-%m-%d'), ' ', FROM_UNIXTIME(`finish_date`,'%Y-%m-%d') ) LIKE (" . $db->qstr("%" . $search_value . "%") . ")
                            )";
        }

        $query .= " ORDER BY `finish_date` DESC";

        $results = $db->GetAll($query, array($curriculum_type_id));

        if ($results) {
            foreach ($results as $result) {
                $output[] = new self($result);
            }
        }

        return $output;
    }

    public static function fetchAllByDateRangeCourseID($start_date, $end_date, $course_id) {
        global $db;

        $output = array();

        $query = "SELECT a.* FROM `".static::$table_name."` AS a
                  JOIN `course_audience` AS b
                  ON a.`cperiod_id` = b.`cperiod_id`
                  WHERE b.`course_id` = ?
                  AND (
                         (a.`start_date` <= ? AND a.`end_date` >= ?)
                         OR (a.`start_date` <= ? AND a.`end_date` >= ?)
                         OR (a.`start_date` >= ? AND a.`end_date` <= ?)
                      )
                  AND `active` = 1";
        $results = $db->GetAll($query, array($course_id, $start_date, $start_date, $end_date, $end_date, $start_date, $end_date));
        if ($results) {
            foreach ($results as $result) {
                $output[] = new self($result);
            }
        }
    }

    public function getAllByFinishDateCurriculumType($curriculum_type_id = null, $finish_date = 0){
        global $db;

        $additional_sql = "";
        $constrains = array($curriculum_type_id);

        if ($finish_date) {
            $additional_sql .= " AND `finish_date` >= ? ";
            $constrains[]= $finish_date;
        }

        $query = "SELECT * FROM `curriculum_periods`
                  WHERE `curriculum_type_id` = ? 
                  AND `active` = 1 ".$additional_sql;

        $curriculum_periods = $db->GetAll($query, $constrains);

        if ($curriculum_periods) {
            return $curriculum_periods;
        }

        return false;
       
    }

}