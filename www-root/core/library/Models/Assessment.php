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
 * A model for handeling assessments
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <rw65@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 */

class Models_Assessment extends Models_Base {

    protected $assessment_id, $course_id, $cohort, $name, $description, $type,
              $marking_scheme_id, $numeric_grade_points_total, $grade_weighting,
              $narrative, $required, $characteristic_id, $show_learner, 
              $release_date, $release_until, $order, $grade_threshold, $active;
    
    protected $table_name = "assessments";
    protected $default_sort_column = "order";
    
    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }
    
    public function getAssessmentID() {
        return $this->assessment_id;
    }

    public function getCourseID() {
        return $this->course_id;
    }

    public function getCohort() {
        return $this->cohort;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getType() {
        return $this->type;
    }

    public function getMarkingSchemeID() {
        return $this->marking_scheme_id;
    }

    public function getNumericGradePointsTotal() {
        return $this->numeric_grade_points_total;
    }

    public function getGradeWeighting() {
        return $this->grade_weighting;
    }

    public function getNarrative() {
        return $this->narrative;
    }

    public function getRequired() {
        return $this->required;
    }

    public function getCharacteristicID() {
        return $this->characteristic_id;
    }

    public function getShowLearner() {
        return $this->show_learner;
    }

    public function getReleaseDate() {
        return $this->release_date;
    }

    public function getReleaseUntil() {
        return $this->release_until;
    }

    public function getOrder() {
        return $this->order;
    }

    public function getGradeThreshold() {
        return $this->grade_threshold;
    }

    public function getActive() {
        return $this->active;
    }
    
    public static function fetchRowByID ($assessment_id) {
        $self = new self();
        return $self->fetchRow(array(
            array("key" => "assessment_id", "value" => $assessment_id, "method" => "="),
            array("mode" => "AND", "key" => "active", "value" => 1, "method" => "=")    
        ));
    }
    
}

?>
