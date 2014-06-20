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
 * A model for handeling Course Groups
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Josh Dillon <jdillon@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 */

class Models_Group {
    private $group_id,
            $group_name,
            $group_type,
            $group_value,
            $start_date,
            $expire_date,
            $group_active,
            $updated_date,
            $updated_by;
    
    protected $table_name = "groups";
    
    public function getID () {
        return $this->group_id;
    }
    
    public function getGroupName () {
        return $this->group_name;
    }
    
    public function getGroupType () {
        return $this->group_type;
    }
    
    public function getGroupValue () {
        return $this->group_value;
    }
    
    public function getStartDate () {
        return $this->start_date;
    }
    
    public function getExpireDate () {
        return $this->expire_date;
    }
    
    public function getGroupActive () {
        return $this->group_active;
    }
    
    public function getUpdatedDate () {
        return $this->updated_date;
    }
    
    public function getUpdatedBy () {
        return $this->updated_by;
    }


    public function __construct($arr = NULL) {
		if (is_array($arr)) {
			$this->fromArray($arr);
		}
	}
	
	public function toArray() {
		$arr = false;
		$class_vars = get_class_vars(get_called_class());
		if (isset($class_vars)) {
			foreach ($class_vars as $class_var => $value) {
				$arr[$class_var] = $this->$class_var;
			}
		}
		return $arr;
	}
	
	public function fromArray($arr) {
		foreach ($arr as $class_var_name => $value) {
			$this->$class_var_name = $value;
		}
		return $this;
	}
    
    public static function fetchRowByID($group_id) {
        $self = new self();
        return $self->fetchRow(array("group_id" => $group_id, "active" => 1));
    }
    
    /**
     * @param array $constraints
     * @param string $default_method
     * @param string $default_mode
     * @return bool|Models_InformationStatement
     */
    private function fetchRow($constraints = array("group_id" => "0"), $default_method = "=", $default_mode = "AND") {
        global $db;

        $self = false;
        if (is_array($constraints) && !empty($constraints)) {
            $where = array();
            $replacements = "";
            $class_vars = array_keys(get_class_vars(get_called_class()));
            foreach ($constraints as $index => $constraint) {
                $key = false;
                $value = false;
                if (is_array($constraint) && in_array($constraint["key"], $class_vars)) {
                    $mode = (isset($constraint["mode"]) && in_array(strtoupper($constraint["mode"]), array("OR", "AND")) ? $constraint["mode"] : $default_mode);
                    $key = clean_input($constraint["key"], array("trim", "striptags"));
                    $method = (isset($constraint["method"]) && in_array(strtoupper($constraint["method"]), array("=", ">", ">=", "<", "<=", "!=", "<>", "BETWEEN", "LIKE", "IS NOT", "IS")) ? $constraint["method"] : $default_method);
                    if (strtoupper($method) == "BETWEEN" && is_array($constraint["value"]) && @count($constraint["value"]) == 2) {
                        $value = clean_input($constraint["value"][0], array("trim", "striptags"))." AND ".clean_input($constraint["value"][1], array("trim", "striptags"));
                    } elseif ($constraint["value"]) {
                        $value = clean_input($constraint["value"], array("trim", "striptags"));
                    } elseif ($constraint["value"] || $constraint["value"] === "0" || $constraint["value"] === 0) {
                        $value = clean_input($constraint["value"], array("trim", "striptags"));
                    } else {
                        $value = NULL;
                    }
                } elseif (!is_array($constraint) && in_array($index, $class_vars)) {
                    $key = clean_input($index, array("trim", "striptags"));
                    $value = clean_input($constraint, array("trim", "striptags"));
                    $method = $default_method;
                    $mode = $default_mode;
                }
                if (isset($key) && $key && isset($value) && ($value || $value === 0 || $value === "0")) {
                    $replacements .= "\n ".(empty($where) ? "WHERE " : (isset($mode) && $mode ? $mode : $default_mode))." `".$key."` ".(isset($method) && $method ? $method : $default_method)." ?";
                    $where[] = $value;
                }
            }
            if (!empty($where)) {
                $query = "SELECT * FROM `".$this->table_name."` ".$replacements;
                $result = $db->GetRow($query, $where);
                if ($result) {
                    $self = new self();
                    $self = $self->fromArray($result);
                }
            }
        }
        return $self;
    }


    /**
     * @param array $constraints
     * @param string $default_method
     * @param string $default_mode
     * @return array
     */
    private function fetchAll($constraints = array("group_id" => "0"), $default_method = "=", $default_mode = "AND") {
        global $db;
        $output = array();
        if (is_array($constraints) && !empty($constraints)) {
            $where = array();
            $replacements = "";
            $class_vars = array_keys(get_class_vars(get_called_class()));
            foreach ($constraints as $index => $constraint) {
                $key = false;
                $value = false;
                if (is_array($constraint) && in_array($constraint["key"], $class_vars)) {
                    $mode = (isset($constraint["mode"]) && in_array(strtoupper($constraint["mode"]), array("OR", "AND")) ? $constraint["mode"] : $default_mode);
                    $key = "`".clean_input($constraint["key"], array("trim", "striptags"))."`";
                    $method = (isset($constraint["method"]) && in_array(strtoupper($constraint["method"]), array("=", ">", ">=", "<", "<=", "!=", "<>", "BETWEEN", "LIKE", "IS NOT", "IS")) ? $constraint["method"] : $default_method);
                    if (strtoupper($method) == "BETWEEN" && is_array($constraint["value"]) && @count($constraint["value"]) == 2) {
                        $value = array(
                            clean_input($constraint["value"][0], array("trim", "striptags")),
                            clean_input($constraint["value"][1], array("trim", "striptags"))
                        );
                    } elseif ($constraint["value"]) {
                        $value = clean_input($constraint["value"], array("trim", "striptags"));
                    } elseif ($constraint["value"] || $constraint["value"] === "0") {
                        $value = clean_input($constraint["value"], array("trim", "striptags"));
                    } else {
                        $value = NULL;
                    }
                } elseif (!is_array($constraint) && in_array($index, $class_vars)) {
                    $key = "`".clean_input($index, array("trim", "striptags"))."`";
                    $value = clean_input($constraint, array("trim", "striptags"));
                    $method = $default_method;
                    $mode = $default_mode;
                }
                if (isset($key) && $key && isset($value) && ($value || $value === 0)) {
                    $replacements .= "\n ".(empty($where) ? "WHERE " : (isset($mode) && $mode ? $mode : $default_mode))." ".$key." ".(isset($method) && $method ? $method : $default_method).($method == "BETWEEN" ? " ? AND ?" : " ?");
                    if (is_array($value) && @count($value) == 2) {
                        $where[] = $value[0];
                        $where[] = $value[1];
                    } else {
                        $where[] = $value;
                    }
                }
            }
            if (!empty($where)) {
                $query = "SELECT * FROM `".$this->table_name."` ".$replacements;
                $results = $db->GetAll($query, $where);
                if ($results) {
                    foreach ($results as $result) {
                        $output[] = new self($result);
                    }
                }
            }
        }
        return $output;
    }
    
    public static function getGroupMembers ($group_id = null, $active = 1) {
        global $db;
        $members = false;
        
        $query	= "	SELECT a.`id`, a.`number`, a.`firstname`, a.`lastname`, c.`gmember_id`, c.`member_active`,
                    a.`username`, a.`email`, a.`organisation_id`, a.`username`, b.`group`, b.`role`
                    FROM `".AUTH_DATABASE."`.`user_data` AS a
                    LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
                    ON a.`id` = b.`user_id`
                    INNER JOIN `group_members` c ON a.`id` = c.`proxy_id`
                    WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
                    AND b.`account_active` = 'true'
                    AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
                    AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
                    AND c.`group_id` = ?
                    AND c.`member_active` = ?
                    GROUP BY a.`id`
                    ORDER BY a.`lastname` ASC, a.`firstname` ASC";

        $results = $db->GetAll($query, array($group_id, $active));
        if ($results) {
            foreach ($results as $result) {
                $member = new User();
                $members[] = User::fromArray($result, $member);
            }
        }
        return $members;
    }
    
    public static function getIndividualMembers ($proxy_id = null) {
        global $db;
        $member = false;
        
        $query = "	SELECT a.`id` AS `user_id`, a.`number`, a.`firstname`, a.`lastname`,
                    a.`username`, a.`email`, a.`organisation_id`, b.`group`, b.`role`
                    FROM `".AUTH_DATABASE."`.`user_data` AS a
                    LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
                    ON a.`id` = b.`user_id`
                    WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
                    AND b.`account_active` = 'true'
                    AND (b.`access_starts` = '0' OR b.`access_starts` <= ?)
                    AND (b.`access_expires` = '0' OR b.`access_expires` > ?)
                    AND a.`id` = ?
                    GROUP BY a.`id`
                    ORDER BY a.`lastname` ASC, a.`firstname` ASC";
        
        $result = $db->GetRow($query, array(time(), time(), $proxy_id));
        if ($result) {
            $m = new User();
            $member = User::fromArray($result, $m);
        }
        return $member;
    }
}
?>