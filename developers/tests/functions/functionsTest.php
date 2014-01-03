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
 * This file is intended to test the <entrada-root>/www-root/core/includes/functions.inc.php file.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 *
 */

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../../../www-root/core",
    dirname(__FILE__) . "/../../../www-root/core/includes",
    dirname(__FILE__) . "/../../../www-root/core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

/**
 * Class FunctionsTest
 *
 * This class contains the tests for each function in the <entrada-root>/www-root/core/includes/functions.inc.php file.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 */
class FunctionsTest extends PHPUnit_Extensions_Database_TestCase
{
    /**
     * Setup function required by PHP Unit.
     */
    public function setup() {

    }

    /**
     * Required function to return a connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection() {
        $config = new Zend_Config(require "config/config.inc.php");
        $dsn = "mysql:host={$config->database->host};dbname={$config->database->entrada_database}";
        $pdo = new PDO($dsn, $config->database->username, $config->database->password);

        return $this->createDefaultDBConnection($pdo);
    }

    /**
     * Required function to return a data set.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet() {
        $ds1 = $this->createMySQLXMLDataSet(dirname(__FILE__) . '/../fixtures/entrada.xml');
        $ds2 = $this->createMySQLXMLDataSet(dirname(__FILE__) . '/../fixtures/entrada_auth.xml');

        $composite_ds = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array());
        $composite_ds->addDataSet($ds1);
        $composite_ds->addDataSet($ds2);

        return $composite_ds;
    }

    /**
     * Tests that the events_fetch_event_attendance_for_user function returns the expected
     * results.
     */
    public function test_events_fetch_event_attendance_for_user() {
        //expected
        $data_set = $this->getDataSet()->getTable("event_attendance")->getRow(0);
        //actual
        $event_attendance = events_fetch_event_attendance_for_user(1, 1);

        $this->assertEquals($data_set, $event_attendance, "The expected event attendance for user did not match the actual results.");
    }

    /**
     * Serves as a failing test for the continuous integration server.
     */
    public function test_failing_test() {
        $this->assertEquals(0, 1);
    }
}
?>