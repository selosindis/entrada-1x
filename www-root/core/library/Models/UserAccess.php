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
 * A model for handling user access records.
 *
 * @author Organisation: Queen's University
 * @author Developer: Travis Obregon <travismobregon@gmail.com>
 * @copyright Copyright 2016 Queen's University. All Rights Reserved.
 */

class Models_UserAccess extends Models_Base {
    protected $id, $user_id, $app_id, $organisation_id, $account_active, $access_starts, $access_expires, $last_login, $last_ip, $login_attempts, $locked_out_until, $role, $group, $extras, $private_hash, $notes;

    protected $database_name = AUTH_DATABASE;
    protected $table_name = "user_access";
    protected $primary_key = "id";
    protected $default_sort_column = "user_id";

    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }

    public function getID() {
        return $this->id;
    }

    public function getUserID() {
        return $this->user_id;
    }

    public function getAppID() {
        return $this->app_id;
    }

    public function getOrganisationID() {
        return $this->organisation_id;
    }

    public function getAccountActive() {
        return $this->account_active;
    }

    public function getAccessStarts() {
        return $this->access_starts;
    }

    public function getAccessExpires() {
        return $this->access_expires;
    }

    public function getLastLogin() {
        return $this->last_login;
    }

    public function getLastIp() {
        return $this->last_ip;
    }

    public function getLoginAttempts() {
        return $this->login_attempts;
    }

    public function getLockedOutUntil() {
        return $this->locked_out_until;
    }

    public function getRole() {
        return $this->role;
    }

    public function getGroup() {
        return $this->group;
    }

    public function getExtras() {
        return $this->extras;
    }

    public function getPrivateHash() {
        return $this->private_hash;
    }

    public function getNotes() {
        return $this->notes;
    }

    public static function fetchRowByUserIDAppID($user_id) {
        $self = new self();
        return $self->fetchRow(array(
            array("key" => "user_id", "value" => $user_id, "method" => "="),
            array("key" => "app_id", "value" => AUTH_APP_ID, "method" => "=")
        ));
    }

    public static function fetchAllByUserIDAppID($user_id) {
        $self = new self();
        return $self->fetchAll(array(
            array("key" => "user_id", "value" => $user_id, "method" => "="),
            array("key" => "app_id", "value" => AUTH_APP_ID, "method" => "=")
        ));
    }

    public static function fetchAllRecords() {
        $self = new self();
        return $self->fetchAll(array(array("key" => "id", "value" => 0, "method" => ">=")));
    }
}