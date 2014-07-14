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
 * A class to handle course group contacts.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 *
 */

class Models_Course_Group_Contact extends Models_Base {
    
    protected $cgcontact_id, $cgroup_id, $proxy_id, $contact_order, $updated_date, $updated_by;
    
    protected $table_name = "course_group_contacts";
    protected $default_sort_column = "contact_order";
    
    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }
    
    public function getCgcontactID() {
        return $this->cgcontact_id;
    }

    public function getCgroupID() {
        return $this->cgroup_id;
    }

    public function getProxyID() {
        return $this->proxy_id;
    }

    public function getContactOrder() {
        return $this->contact_order;
    }

    public function getUpdatedDate() {
        return $this->updated_date;
    }
    
    public function getUpdatedBy() {
        return $this->updated_by;
    }
    
    public static function fetchRowByID($cgcontact_id = 0) {
        $self = new self();
        return $self->fetchRow(array(
                array("key" => "cgcontact_id", "value" => $cgcontact_id, "method" => "=", "mode" => "AND")
            )
        );
    }
    
    public static function fetchRowByProxyIDCGroupID($proxy_id, $cgroup_id) {
        $self = new self();
        return $self->fetchRow(array(
                array("key" => "proxy_id", "value" => $proxy_id, "method" => "=", "mode" => "AND"),
                array("key" => "cgroup_id", "value" => $cgroup_id, "method" => "=", "mode" => "AND")
            )
        );
    }

    public static function fetchAllByCgroupID($cgroup_id = 0) {
        $self = new self();

        $constraints = array(
            array(
                "mode"      => "AND",
                "key"       => "cgroup_id",
                "value"     => $cgroup_id,
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
    
    public static function fetchAllByProxyID($proxy_id = 0) {
        $self = new self();

        $constraints = array(
            array(
                "mode"      => "AND",
                "key"       => "proxy_id",
                "value"     => $proxy_id,
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

}

?>
