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
 * This file is intended to test the <entrada-root>/www-root/core/library/Models/eportfolio/Advisor.php file.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 *
 */

/**
 * Class AdvisorTest
 *
 * This class contains the tests for each function in the <entrada-root>/www-root/core/library/Models/eportfolio/Advisor.php file.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 */
require_once(dirname(__FILE__) . "/../../BaseTestCase.php");

class AdvisorTest extends BaseTestCase
{
    /**
     * Setup and Teardown functions required by PHP Unit.
     */
    public function setup() {
        parent::setUp();
    }
    public function tearDown() {
        parent::tearDown();
    }

    /**
     * Test inserting and fetching a record.
     *
     * @covers Models_Eportfolio_Advisor::fetchRow
     */
    public function test_fetch_row() {
        //expected
        $data = array("padvisor_id" => "1", "proxy_id" => "2", "eportfolio_id" => "", "active" => "1");
        $test_padvisor = new Models_Eportfolio_Advisor($data);
        //database has 0 advisors in it at first.
        $test_padvisor->insert();
        //actual
        $advisor = Models_Eportfolio_Advisor::fetchRow($test_padvisor->getProxyID());

        $this->assertEquals($test_padvisor->getProxyID(), $advisor->getProxyID(), "The expected advisor was not found in the database.");
    }

    /**
     * Tests updating a record.
     *
     * @covers Models_Eportfolio_Advisor::update
     */
    public function test_update() {
        $data = array("padvisor_id" => "1", "proxy_id" => "3", "eportfolio_id" => "", "active" => "1");
        $test_padvisor = new Models_Eportfolio_Advisor($data);
        $test_padvisor->update();

        $expected_proxy_id = 3;

        $this->assertEquals($expected_proxy_id, $test_padvisor->getProxyID(), "The expected proxy id did not match the actual proxy id after update.");
    }
}
?>