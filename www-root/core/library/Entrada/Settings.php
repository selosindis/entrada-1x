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
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 */
class Entrada_Settings {

    private $setting_id,
        $shortname,
        $organisation_id,
        $value;

    protected $table_name = "settings";

    public function __construct($arr = NULL) {
        if (is_array($arr)) {
            $this->fromArray($arr);
        }
    }

    public function getID() {
        return $this->setting_id;
    }

    public function getShortname() {
        return $this->shortname;
    }

    public function getOrganisationID() {
        return $this->organisation_id;
    }

    public function getValue() {
        return $this->value;
    }

    public function insert() {
        global $db;

        if ($db->AutoExecute($this->table_name, $this->toArray(), "INSERT")) {
            $this->setting_id = $db->Insert_ID();
            return $this;
        } else {
            return false;
        }
    }

    public function update() {
        global $db;
        if (isset($this->setting_id)) {
            if ($db->AutoExecute($this->table_name, $this->toArray(), "UPDATE", "`setting_id` = ".$db->qstr($this->setting_id))) {
                return $this;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function delete() {
        $this->active = 0;
        return $this->update();
    }

    /**
     * @param array $constraints
     * This array can have either of two possible formats, or even a blend of the two.
     * Each element in the array should either be a key-value pair with the key of the array
     * being the field name, and the value being the value in the field, or an array holding
     * at least elements, with the "key" key, and the "value" key, and then the optional
     * inclusion of a "mode" key holding 'AND' or 'OR' which will come before the line in the where statement
     * (only if it is not the first constraint), and a "method" which determines which operator will be used
     * out of the following: "=", ">", ">=", "<", "<=", "!=", "<>", "BETWEEN", "LIKE", "IS NOT", "IS".
     * For an example, here is an array, and the query which it would build with the rest of the parameters
     * left unset on the method call:
     *
     *Array:
    $constraints = array(
        "shortname" => "export_weighted_grade",
        array(
        "mode"      => "AND",
        "key"       => "organisation_id" ,
        "value"     => "1"
        ),
        array(
        "mode"      => "OR",
        "key"       => "shortname" ,
        "value"     => "export_weighted_grade"
        ),
        array(
        "mode"      => "AND",
        "key"       => "organisation_id" ,
        "value"     => NULL,
        "method"    => "IS"
        )
    );
     *
     *
    Query:
        SELECT * FROM `settings`
        WHERE `shortname` = 'export_weighted_grade'
        AND `organisation_id` = '1'
        OR `shortname` = 'export_weighted_grade'
        AND `organisation_id` IS NULL
        ORDER BY `organisation_id` DESC
     *
     * @param string $default_method
     * @param string $default_mode
     * @param string $sort_column
     * @param string $sort_order
     * @return bool|Entrada_Settings
     */
    private function fetchRow($constraints = array("setting_id" => "0"), $default_method = "=", $default_mode = "AND", $sort_column = "organisation_id", $sort_order = "DESC") {
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
                if (isset($key) && $key) {
                    $replacements .= "\n ".(empty($where) ? "WHERE " : (isset($mode) && $mode ? $mode : $default_mode))." `".$key."` ".(isset($method) && $method ? $method : $default_method)." ".(!isset($value) ? "NULL" : "?");
                    if (isset($value)) {
                        $where[] = $value;
                    }
                }
            }
            if (!empty($where)) {
                if (!in_array($sort_column, $class_vars)) {
                    $sort_column = "organisation_id";
                }
                if ($sort_order == "ASC") {
                    $sort_order = "ASC";
                } else {
                    $sort_order = "DESC";
                }
                $query = "SELECT * FROM `".$this->table_name."` ".$replacements." ORDER BY `".$sort_column."` ".$sort_order;
                $result = $db->GetRow($query, $where);
                if ($result) {
                    $self = new self();
                    $self = $self->fromArray($result);
                }
            }
        }
        return $self;
    }

    public static function fetchByShortname($shortname, $organisation_id) {
        $self = new self();

        return $self->fetchRow(array(array("key" => "shortname", "value" => $shortname, "mode" => "AND"), array("key" => "organisation_id", "value" => $organisation_id, "mode" => "AND"), array("key" => "shortname", "value" => $shortname, "mode" => "OR"), array("key" => "organisation_id", "value" => NULL, "method" => "IS", "mode" => "AND")));
    }

    public static function fetchByID($setting_id) {
        $self = new self();

        return $self->fetchRow(array(array("key" => "setting_id", "value" => $setting_id)));
    }

    /**
     * Returns objects values in an array.
     * @return Array
     */
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

    /**
     * @param array $arr
     * @return Entrada_Settings
     */
    public function fromArray(array $arr) {
        $class_vars = array_keys(get_class_vars(get_called_class()));
        foreach ($arr as $class_var_name => $value) {
            if (in_array($class_var_name, $class_vars)) {
                $this->$class_var_name = $value;
                unset($arr[$class_var_name]);
            }
        }
        return $this;
    }

}
?>