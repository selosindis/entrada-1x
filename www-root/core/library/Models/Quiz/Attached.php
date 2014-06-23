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
 * A model to handle generic quiz attachments
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <rw65@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 */
class Models_Quiz_Attached extends Models_Base {
    
    protected $aquiz_id, $content_type, $content_id, $required, $require_attendance, 
              $random_order, $timeframe, $quiz_id, $quiz_title, $quiz_notes, 
              $quiztype_id, $quiz_timeout, $quiz_attempts, $accesses, $release_date, 
              $release_until, $updated_date, $updated_by;
    
    protected $table_name = "attached_quizzes";
    protected $default_sort_column = "aquiz_id";
    
    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }
    
    public function getCompletedAttempts() {
        global $db;
        $query = "SELECT COUNT(DISTINCT `proxy_id`) FROM `quiz_progress` WHERE `progress_value` = 'complete' AND `aquiz_id` = ".$db->qstr($this->aquiz_id);
        $result = $db->GetOne($query);
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }
    
    public static function getAttachedContact($quiz_id, $proxy_id) {
        global $db;
        
        $query = "SELECT b.`proxy_id`
                    FROM `attached_quizzes` AS a
                    LEFT JOIN `event_contacts` AS b
                    ON a.`content_type` = 'event'
                    AND a.`content_id` = b.`event_id`
                    LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS c
                    ON b.`proxy_id` = c.`id`
                    WHERE a.`quiz_id` = ".$db->qstr($quiz_id)."
                    AND b.`proxy_id` = ".$db->qstr($proxy_id);
        return $db->GetRow();
    }
    
    public function getAquizID() {
        return $this->aquiz_id;
    }

    public function getContentType() {
        return $this->content_type;
    }

    public function getContentID() {
        return $this->content_id;
    }

    public function getRequired() {
        return $this->required;
    }

    public function getRequireAttendance() {
        return $this->require_attendance;
    }

    public function getRandomOrder() {
        return $this->random_order;
    }

    public function getTimeframe() {
        return $this->timeframe;
    }

    public function getQuizID() {
        return $this->quiz_id;
    }

    public function getQuizTitle() {
        return $this->quiz_title;
    }

    public function getQuizNotes() {
        return $this->quiz_notes;
    }

    public function getQuiztypeID() {
        return $this->quiztype_id;
    }

    public function getQuizTimeout() {
        return $this->quiz_timeout;
    }

    public function getQuizAttempts() {
        return $this->quiz_attempts;
    }

    public function getAccesses() {
        return $this->accesses;
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
    
    public function insert() {
        global $db;
        
        if ($db->AutoExecute($this->table_name, $this->toArray(), "INSERT")) {
            $this->aquiz_id = $db->Insert_ID();
            return $this;
        } else {
            echo $db->ErrorMsg();
            return false;
        }
    }
    
    public function update() {
        global $db;
        
        if ($db->AutoExecute($this->table_name, $this->toArray(), "UPDATE", "`aquiz_id` = ".$db->qstr($this->aquiz_id))) {
            return $this;
        } else {
            return false;
        }
    }
    
}

?>
