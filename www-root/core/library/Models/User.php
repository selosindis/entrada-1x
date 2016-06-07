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
 * A model for handling user data records.
 *
 * @author Organisation: Queen's University
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2015 Queen's University. All Rights Reserved.
 */

class Models_User extends Models_Base {
    protected $id, $number, $username, $password, $salt, $organisation_id, $department, $prefix, $firstname, $lastname, $email, $email_alt, $email_updated, $google_id, $telephone, $fax, $address, $city, $province, $postcode, $country, $country_id, $province_id, $notes, $office_hours, $privacy_level, $copyright, $notifications, $entry_year, $grad_year, $gender, $clinical, $uuid, $updated_date, $updated_by;

    protected $database_name = AUTH_DATABASE;
    protected $table_name = "user_data";
    protected $primary_key = "id";
    protected $default_sort_column = "lastname";



    /**
     * lookup array for formatting user information
     * <code>
     * $format_keys = array(
     *								"f" => "firstname",
     *								"l" => "lastname",
     *								"p" => "prefix"
     *								);
     *
     * //Usage:
     * if ($user->getPrefix()) {
     *   echo $user->getName("%p. %f %l"); //i.e. Dr. John Smith
     * } else {
     *   echo $user->getName("%f %l"); //i.e. John Smith
     * }
     * </code>
     * @var array
     */
    private static $format_keys = array(
        "f" => "firstname",
        "l" => "lastname",
        "p" => "prefix"
    );

    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }

    public function getID() {
        return $this->id;
    }

    public function getNumber() {
        return $this->number;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getSalt() {
        return $this->salt;
    }

    public function getOrganisationID() {
        return $this->organisation_id;
    }

    public function getDepartment() {
        return $this->department;
    }

    public function getPrefix() {
        return $this->prefix;
    }

    public function getFirstname() {
        return $this->firstname;
    }

    public function getLastname() {
        return $this->lastname;
    }

    /**
     * Returns the Last and First names formatted as "lastname, firstname"
     * @return string
     */
    public function getFullname($reverse = true) {
        if ($reverse) {
            return $this->getName("%l, %f");
        } else {
            return $this->getName("%f %l");
        }
    }

    /**
     * Returns the user's name formatted according to the format string supplied. Default format is "%f %l" (firstname, lastname)
     * <code>
     * if ($user->getPrefix()) {
     *   echo $user->getName("%p. %f %l"); //i.e. Dr. John Smith
     * } else {
     *   echo $user->getName("%f %l"); //i.e. John Smith
     * }
     * </code>
     * @see User::$format_keys
     * @param string $format
     * @return string
     */
    public function getName($format = "%f %l") {
        foreach (self::$format_keys as $key => $var) {
            $pattern = "/([^%])%".$key."|^%".$key."|(%%)%".$key."/";
            $format = preg_replace($pattern, "$1$2".$this->{$var}, $format);
        }

        $format = preg_replace("/%%/", "%", $format);

        return $format;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getEmailAlt() {
        return $this->email_alt;
    }

    public function getEmailUpdated() {
        return $this->email_updated;
    }

    public function getGoogleID() {
        return $this->google_id;
    }

    public function getTelephone() {
        return $this->telephone;
    }

    public function getFax() {
        return $this->fax;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getCity() {
        return $this->city;
    }

    public function getProvince() {
        return $this->province;
    }

    public function getPostcode() {
        return $this->postcode;
    }

    public function getCountry() {
        return $this->country;
    }

    public function getCountryID() {
        return $this->country_id;
    }

    public function getProvinceID() {
        return $this->province_id;
    }

    public function getNotes() {
        return $this->notes;
    }

    public function getOfficeHours() {
        return $this->office_hours;
    }

    public function getPrivacyLevel() {
        return $this->privacy_level;
    }

    public function getCopyright() {
        return $this->copyright;
    }

    public function getNotifications() {
        return $this->notifications;
    }

    public function getEntryYear() {
        return $this->entry_year;
    }

    public function getGradYear() {
        return $this->grad_year;
    }

    public function getGender() {
        return $this->gender;
    }

    public function getClinical() {
        return $this->clinical;
    }

    public function getUuid() {
        return $this->uuid;
    }

    public function getUpdatedDate() {
        return $this->updated_date;
    }

    public function getUpdatedBy() {
        return $this->updated_by;
    }

    public static function fetchRowByID($id) {
        $self = new self();
        return $self->fetchRow(array(
            array("key" => "id", "value" => $id, "method" => "=")
        ));
    }

    public static function fetchRowByNumber($number) {
        $self = new self();
        return $self->fetchRow(array(
            array("key" => "number", "value" => $number, "method" => "=")
        ));
    }

    public static function fetchRowByEmail($email) {
        $self = new self();
        return $self->fetchRow(array(
            array("key" => "email", "value" => $email, "method" => "=")
        ));
    }

    public static function fetchRowByUsername($username) {
        $self = new self();
        return $self->fetchRow(array(
            array("key" => "username", "value" => $username, "method" => "=")
        ));
    }

    public static function fetchAllRecords() {
        $self = new self();
        return $self->fetchAll(array(array("key" => "id", "value" => 0, "method" => ">=")));
    }

    public static function fetchAllByCGroupIDSearchTerm($cgroup_id, $search_term = NULL) {
        global $db;

        $output = array();

        if (!$search_term || !trim($search_term)) {
            $search_term = NULL;
        }

        $constraints = array($cgroup_id);

        $query = "SELECT a.* FROM `".static::$database_name."`.`".static::$table_name."` AS a
                    JOIN `course_group_audience` AS b
                    ON a.`id` = b.`proxy_id`
                    JOIN `course_groups` AS c
                    ON b.`cgroup_id` = c.`cgroup_id`
                    JOIN `courses` AS d
                    ON c.`course_id` = d.`course_id`
                    WHERE b.`active` = 1
                    AND b.`cgroup_id` = ?";
        if ($search_term) {
            $query .= "\n AND (CONCAT(a.`firstname`, ' ', a.`lastname`) LIKE ? OR a.`email` LIKE ? OR d.`course_name` LIKE ? OR d.`course_code` LIKE ?)";
            $constraints[] = "%".$search_term."%";
            $constraints[] = "%".$search_term."%";
            $constraints[] = "%".$search_term."%";
            $constraints[] = "%".$search_term."%";
        }
        $query .= "\n GROUP BY a.`id`";
        $users = $db->getAll($query, $constraints);
        if ($users) {
            foreach ($users as $user) {
                $output[] = new self($user);
            }
        }

        return $output;
    }
}