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
 * A class to handle course groups.
 * 
 * @author Organisation: David Geffen School of Medicine at UCLA
 * @author Unit: Instructional Design and Technology Unit
 * @author Developer: Sam Payne <spayne@mednet.ucla.edu>
 * @copyright Copyright 2014 David Geffen School of Medicine at UCLA
 *
 */

class Models_Course_Group extends Models_Base {
    
    protected $cgroup_id, $course_id, $group_name, $group_type, $active;
    
    protected $table_name           = "course_groups";
    protected $primary_key          = "cgroup_id";
    protected $default_sort_column  = "cgroup_id";
    
    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }

    public function getID() {
        return $this->cgroup_id;
    }
    
    public function getCgroupID() {
        return $this->cgroup_id;
    }

    public function getCourseID() {
        return $this->course_id;
    }

    public function getGroupName() {
        return $this->group_name;
    }
    
    public function getGroupType() {
        return $this->group_type;
    }
    
    public function getActive() {
        return $this->active;
    }

    /* @return bool|Models_Course_Group */
    public static function fetchRowByID($cgroup_id) {
        $self = new self();
        return $self->fetchRow(array(
                array("key" => "cgroup_id", "value" => $cgroup_id, "method" => "=", "mode" => "AND")
            )
        );
    }

    /* @return bool|Models_Course_Group */
    public static function fetchRowByCgroupID($cgroup_id = 0) {
        $self = new self();
        return $self->fetchRow(array(
                array("key" => "cgroup_id", "value" => $cgroup_id, "method" => "=", "mode" => "AND")
            )
        );
    }

    /* @return ArrayObject|Models_Course_Group[] */
    public static function fetchAllByCourseID($course_id = 0) {
        $self = new self();

        $constraints = array(
            array(
                "mode"      => "AND",
                "key"       => "course_id",
                "value"     => $course_id,
                "method"    => "="
            )
        );

        $objs = $self->fetchAll($constraints, "=", "AND");
        $output = array();

        if (!empty($objs)) {
            foreach ($objs as $o) {
                $output[] = $o;
            }
        }

        return $output;
    }

    /* @return ArrayObject|Models_Course_Group[] */
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

        $objs = $self->fetchAll($constraints, "=", "AND");
        $output = array();

        if (!empty($objs)) {
            foreach ($objs as $o) {
                $output[] = $o;
            }
        }

        return $output;
    }

    /* @return bool|Models_Course_Group */
    public static function fetchRowByGroupNameCourseID($group_name = 0, $course_id = 0) {
        $self = new self();
        $constraints = array(
            array(
                "key" => "group_name",
                "value" => $group_name
            ),
            array(
                "key" => "course_id",
                "value" => $course_id
            )
        );
        $row = $self->fetchRow($constraints);
        if ($row) {
            return $row;
        }
        return false;
    }
}
