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
 * @author Developer: Jordan L <jl250@queensu.ca>
 * @copyright Copyright 2016 Queen's University. All Rights Reserved.
 */

class Models_Assessments_Distribution_Releases extends Models_Base {
    protected $adreleasor_id, $aprogress_id, $adistribution_id, $releasor_id, $release_status = 0, $comments, $created_date, $created_by;

    protected static $table_name = "cbl_assessment_progress_releases";
    protected static $primary_key = "adreleasor_id";
    protected static $default_sort_column = "adreleasor_id";

    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }

    public function getID() {
        return $this->adreleasor_id;
    }

    public function getProgressID() {
        return $this->aprogress_id;
    }

    public function getAdistributionID() {
        return $this->adistribution_id;
    }

    public function getReleasorID() {
        return $this->releasor_id;
    }

    public function getReleaseStatus() {
        return $this->release_status;
    }

    public function getComments() {
        return $this->comments;
    }
    
    public function getCreatedDate() {
        return $this->created_date;
    }

    public function getCreatedBy() {
        return $this->created_by;
    }

    public function fetchRowByID($adreleasor_id) {
        return $this->fetchRow(array(
            array("key" => "adreleasor_id", "value" => $adreleasor_id, "method" => "=")
        ));
    }

    public function fetchRowByReleasorID($releasor_id) {
        return $this->fetchRow(array(
            array("key" => "releasor_id", "value" => $releasor_id, "method" => "=")
        ));
    }

    public function fetchRowByProgressIDDistributionID($aprogress_id, $adistribution_id) {
        return $this->fetchRow(array(
            array("key" => "aprogress_id", "value" => $aprogress_id, "method" => "="),
            array("key" => "adistribution_id", "value" => $adistribution_id, "method" => "=")
        ));
    }
    
    public function fetchAllRecords() {
        return $this->fetchAll(array(array("key" => "adreleasor_id", "value" => 0, "method" => ">=")));
    }

    public function fetchAllByDistributionID($adistribution_id) {
        $params = array(array("key" => "adistribution_id", "value" => $adistribution_id, "method" => "="));
        return $this->fetchAll($params);
    }

    public function fetchAllByReleasorID($releasor_id = "") {
        $params = array(array("key" => "proxy_id", "value" => $releasor_id == "" ? $this->releasor_id : $releasor_id, "method" => "="));
        return $this->fetchAll($params);
    }
    
    public function getReleasorName($releasor_id = "") {
        $name = false;
        $user = Models_User::fetchRowByID($releasor_id == "" ? $this->releasor_id : $releasor_id);

        if ($user) {
            $name = $user->getFirstname() . " " . $user->getLastname();
        }
        return $name;
    }

    public function delete() {
        global $db;
        if ($db->Execute("DELETE FROM `" . static::$table_name . "` WHERE `" . static::$primary_key . "` = " . $this->getID())) {
            return true;
        } else {
            application_log("error", "Error deleting " . get_called_class(). " id[" . $this->{static::$primary_key} . "]. DB Said: " . $db->ErrorMsg());
            return false;
        }
    }
}