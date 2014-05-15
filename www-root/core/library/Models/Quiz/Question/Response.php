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
 * A model to handle quiz question responses
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <rw65@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 */

class Models_Quiz_Question_Response extends Models_Base {

    protected $qqresponse_id, $qquestion_id, $response_text, $response_order, $response_correct, $response_is_html, $response_feedback, $response_active;
    
    protected $table_name = "quiz_question_responses";
    protected $default_sort_column = "response_order";
    
    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }
    
    public static function fetchRowByID($qqresponse_id) {
        $self = new self();
        return $self->fetchRow(array(
                array("key" => "qqresponse_id", "value" => $qqresponse_id, "method" => "=", "mode" => "AND")
            )
        );
    }

    public static function fetchAllRecords($qquestion_id, $response_active = 1) {
        $self = new self();

        $constraints = array(
            array(
                "mode"      => "AND",
                "key"       => "qquestion_id",
                "value"     => $qquestion_id,
                "method"    => "="
            ),
            array(
                "mode"      => "AND",
                "key"       => "response_active",
                "value"     => $response_active,
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
    
    public function getQqresponseID() {
        return $this->qqresponse_id;
    }

    public function getQquestionID() {
        return $this->qquestion_id;
    }

    public function getResponseText() {
        return $this->response_text;
    }

    public function getResponseOrder() {
        return $this->response_order;
    }

    public function getResponseCorrect() {
        return $this->response_correct;
    }

    public function getResponseIsHTML() {
        return $this->response_is_html;
    }

    public function getResponseFeedback() {
        return $this->response_feedback;
    }

    public function getResponseActive() {
        return $this->response_active;
    }


    
}

?>
