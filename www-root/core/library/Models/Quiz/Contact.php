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
 * A model to handle quiz contacts
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <rw65@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 */

class Models_Quiz_Contact extends Models_Base {

    protected $qcontact_id, $quiz_id, $proxy_id, $updated_date, $updated_by;
    
    protected $table_name = "quiz_contacts";
    protected $default_sort_column = "qcontact_id";
    
    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }
    
    public static function fetchRowByID($question_id) {
        $self = new self();
        return $self->fetchRow(array(
                array("key" => "question_id", "value" => $question_id, "method" => "=", "mode" => "AND")
            )
        );
    }

    public static function fetchAllRecords($quiz_id, $question_active = 1) {
        $self = new self();

        $constraints = array(
            array(
                "mode"      => "AND",
                "key"       => "quiz_id",
                "value"     => $quiz_id,
                "method"    => "="
            ),
            array(
                "mode"      => "AND",
                "key"       => "question_active",
                "value"     => $question_active,
                "method"    => "="
            )
        );

        $objs = $self->fetchAll($constraints, "=", "AND", $sort_col, $sort_order);
        $output = array();

        if (!empty($objs)) {
            foreach ($objs as $o) {
                $output[] = $o;
            }
        }

        return $output;
    }
    
    public function getQcontactID() {
        return $this->qcontact_id;
    }

    public function getQuizID() {
        return $this->quiz_id;
    }

    public function getProxyID() {
        return $this->proxy_id;
    }

    public function getUpdatedDate() {
        return $this->updated_date;
    }

    public function getUpdatedBy() {
        return $this->updated_by;
    }


    
}
