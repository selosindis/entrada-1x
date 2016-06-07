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
 * A model for handling specific versions of uploaded files for gradebook assignments.
 *
 * @author Organisation: Queen's University
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2015 Queen's University. All Rights Reserved.
 */

class Models_Assignment_File_Version extends Models_Base {
    protected $afversion_id, $afile_id, $assignment_id, $proxy_id, $file_mimetype, $file_version, $file_filename, $file_filesize, $file_active, $updated_date, $updated_by;

    protected static $table_name = "assignment_file_versions";
    protected static $primary_key = "afversion_id";
    protected static $default_sort_column = "updated_date";

    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }

    public function getID() {
        return $this->afversion_id;
    }

    public function getAfversionID() {
        return $this->afversion_id;
    }

    public function getAfileID() {
        return $this->afile_id;
    }

    public function getAssignmentID() {
        return $this->assignment_id;
    }

    public function getProxyID() {
        return $this->proxy_id;
    }

    public function getFileMimetype() {
        return $this->file_mimetype;
    }

    public function getFileVersion() {
        return $this->file_version;
    }

    public function getFileFilename() {
        return $this->file_filename;
    }

    public function getFileFilesize() {
        return $this->file_filesize;
    }

    public function getFileActive() {
        return $this->file_active;
    }

    public function getUpdatedDate() {
        return $this->updated_date;
    }

    public function getUpdatedBy() {
        return $this->updated_by;
    }

    public static function fetchMostRecentByAFileID($afile_id, $file_active = true) {
        $self = new self();
        $output = false;
        $results = $self->fetchAll(array(
            array("key" => "afile_id", "value" => $afile_id, "method" => "="),
            array("key" => "file_active", "value" => $file_active, "method" => "=")
        ), "=", "AND", "file_version", "DESC");
        if ($results) {
            $output = $results[0];
        }
        return $output;
    }

    public static function fetchRowByID($afversion_id, $file_active = true) {
        $self = new self();
        return $self->fetchRow(array(
            array("key" => "afversion_id", "value" => $afversion_id, "method" => "="),
            array("key" => "file_active", "value" => $file_active, "method" => "=")
        ));
    }

    public static function fetchAllRecords($file_active = true) {
        $self = new self();
        return $self->fetchAll(array(array("key" => "file_active", "value" => $file_active, "method" => "=")));
    }
}