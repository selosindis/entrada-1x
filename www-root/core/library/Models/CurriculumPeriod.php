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
 * A model for handeling a Curriculum Periods
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Josh Dillon <jdillon@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 */

class Models_CurriculumPeriod extends Models_Base {
    protected $cperiod_id,
            $curriculum_type_id,
            $start_date,
            $finish_date,
            $curriculum_period_title,
            $active;

    protected static $primary_key = "cperiod_id";
    protected static $table_name = "curriculum_periods";
    protected static $default_sort_column = "cperiod_id";
    
    public function getID () {
        return $this->cperiod_id;
    }
    
    public function getCurriculumTypeID () {
        return $this->curriculum_type_id;
    }
    
    public function getStartDate () {
        return $this->start_date;
    }
    
    public function getFinishDate () {
        return $this->finish_date;
    }
    
    public function getCurriculumPeriodTitle () {
        return $this->curriculum_period_title;
    }

    public function getAudienceValue () {
        return $this->audience_value;
    }
    
    public function getActive () {
        return $this->active;
    }
    
    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }

    public function getDateRangeString() {
        global $translate;

        if ($this->getStartDate() && $this->getFinishDate()) {
            return date("F jS, Y", html_encode($this->getStartDate()))." ".$translate->_("to")." ".date("F jS, Y", html_encode($this->getFinishDate()));
        }
    }
	
    public static function fetchRowByCurriculumTypeIdDates ($curriculum_type_id = null, $start_date = null, $finish_date = null, $active = 1) {
        global $db;
        $period = false;
        
        $query = "  SELECT * FROM `curriculum_periods`
                    WHERE `curriculum_type_id` = ?
                    AND `start_date` < ?
                    AND `finish_date` > ?
                    AND `active` = ?";
        
        $result = $db->GetRow($query, array($curriculum_type_id, $start_date, $finish_date, $active));
        if ($result) {
            $period = new self($result);
        }
        return $period;
    }
    
    public static function fetchRowByCurriculumTypeIDCourseID ($curriculum_type_id = null, $course_id = null, $active = 1) {
        global $db;
        $periods = false;
        
        $query = "	SELECT * FROM `curriculum_periods` a
                    JOIN `course_audience` b
                    ON a.`cperiod_id` = b.`cperiod_id`
                    WHERE a.`curriculum_type_id` = ? 
                    AND a.`active` = ?
                    AND b.`course_id` = ?
                    GROUP BY a.`cperiod_id`
                    ORDER BY a.`start_date` DESC";

        $results = $db->GetAll($query, array($curriculum_type_id, $active, $course_id));
        if ($results) {
            foreach ($results as $result) {
                $period = new self($result);
                $periods[] = $period;
            }
        }
        return $periods;
    }
    
    public static function fetchRowByID ($cperiod_id = null) {
        global $db;
        $period = false;

        $query = "SELECT * FROM `curriculum_periods` WHERE `cperiod_id` = ?";
        $result = $db->GetRow($query, $cperiod_id);
        if ($result) {
            $period = new self($result);
        }
        return $period;
    }

    public static function fetchAllByCurriculumTypeID($curriculum_type_id = null, $active = 1) {
        $self = new self();
        return $self->fetchAll(array(
            array("key" => "curriculum_type_id", "value" => $curriculum_type_id, "method" => "="),
            array("key" => "active", "value" => $active, "method" => "="),
        ));
    }

    public static function fetchLastActiveByCurriculumTypeID ($curriculum_type_id = null, $date = null, $active = 1) {
        global $db;
        $curriculum_period = false;

        $query = "  SELECT * FROM `curriculum_periods`
                    WHERE `curriculum_type_id` = ?
                    AND `finish_date` < ?
                    AND `active` = ?
                    GROUP BY `cperiod_id`
                    ORDER BY `finish_date` DESC";

        $result = $db->GetRow($query, array($curriculum_type_id, $date, $active));
        if ($result) {
            $curriculum_period  = new self($result);
        }

        return $curriculum_period;
    }

    public function fetchAllCurrent($active = 1) {
        $self = new self();
        return $self->fetchAll(array(
            array("key" => "start_date", "value" => time(), "method" => "<="),
            array("key" => "finish_date", "value" => time(), "method" => ">="),
            array("key" => "active", "value" => $active, "method" => "="),
        ));
    }
}
?>