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
 * A model for handling gradebook assignments
 *
 * @author Organisation: Queen's University
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2015 Queen's University. All Rights Reserved.
 */

class Models_Assignment extends Models_Base {
    protected $assignment_id, $course_id, $assessment_id, $notice_id, $assignment_title, $assignment_description, $assignment_active, $required, $due_date, $assignment_uploads, $max_file_uploads, $release_date, $release_until, $updated_date, $updated_by;

    protected static $table_name = "assignments";
    protected static $primary_key = "assignment_id";
    protected static $default_sort_column = "assignment_title";

    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }

    public function getID() {
        return $this->assignment_id;
    }

    public function getAssignmentID() {
        return $this->assignment_id;
    }

    public function getCourseID() {
        return $this->course_id;
    }

    public function getAssessmentID() {
        return $this->assessment_id;
    }

    public function getNoticeID() {
        return $this->notice_id;
    }

    public function getAssignmentTitle() {
        return $this->assignment_title;
    }

    public function getAssignmentDescription() {
        return $this->assignment_description;
    }

    public function getAssignmentActive() {
        return $this->assignment_active;
    }

    public function getRequired() {
        return $this->required;
    }

    public function getDueDate() {
        return $this->due_date;
    }

    public function getAssignmentUploads() {
        return $this->assignment_uploads;
    }

    public function getMaxFileUploads() {
        return $this->max_file_uploads;
    }

    public function getReleaseDate() {
        return $this->release_date;
    }

    public function getReleaseUntil() {
        return $this->release_until;
    }

    public function getUpdatedDate() {
        return $this->updated_date;
    }

    public function getUpdatedBy() {
        return $this->updated_by;
    }

    public static function fetchRowByID($assignment_id, $assignment_active = true) {
        $self = new self();
        return $self->fetchRow(array(
            array("key" => "assignment_id", "value" => $assignment_id, "method" => "="),
            array("key" => "assignment_active", "value" => $assignment_active, "method" => "=")
        ));
    }

    public static function fetchAllRecords($assignment_active = true) {
        $self = new self();
        return $self->fetchAll(array(array("key" => "assignment_active", "value" => $assignment_active, "method" => "=")));
    }
}