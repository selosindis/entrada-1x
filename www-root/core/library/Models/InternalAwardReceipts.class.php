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
 * This file contains all of the functions used within Entrada.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

require_once("SimpleCache.class.php");
require_once("InternalAwardReceipt.class.php");
require_once("Collection.class.php");

/**
 * Utility Class for getting a list of AwardRecipients
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@quensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class InternalAwardReceipts extends Collection {
	
	/**
	 * Returns an array of AwardRecipient objects representing students who have been given the award provided by $award_id 
	 * @param int $award_id
	 * @return InternalAwardReceipts
	 */
	static public function get($obj) {
		if ($obj instanceof Award) {
			$receipts = self::getByAward($obj);
		} elseif ($obj instanceof User) {
			$receipts = self::getByUser($obj);
		}
		return $receipts;
	}
	static private function getByAward(Award $award) {
		global $db;
		$query		= "SELECT a.id as `award_receipt_id`, user_id, a.year 
				FROM `". DATABASE_NAME ."`.`student_awards_internal` a 
				WHERE a.`award_id` = ".$db->qstr($award->getID()) ." 
				order by a.year desc";
		
		$results	= $db->GetAll($query);
		$receipts = array();
		if ($results) {
			foreach ($results as $result) {
				
				//$award = new Award($result['award_receipt_id'], $result['title'], $result['terms'], $result['disabled']);
				
				$receipt = new InternalAwardReceipt( $result['user_id'], $award, $result['award_receipt_id'], $result['year']);
				$receipts[] = $receipt;
			}
		}
		return new self($receipts);
	}
	
	static private function getByUser(User $user) {
		global $db;
		$query		= "SELECT a.id as `award_receipt_id`, c.id, a.`user_id`, c.title, c.award_terms, c.disabled, a.year 
				FROM `". DATABASE_NAME ."`.`student_awards_internal` a 
				left join `". DATABASE_NAME ."`.`student_awards_internal_types` c on c.id = a.award_id 
				WHERE a.`user_id` = ".$db->qstr($user->getID()) ." 
				order by a.year desc";
		
		$results	= $db->GetAll($query);
		$receipts = array();
		if ($results) {
			foreach ($results as $result) {
				
				//$user = new User($result['id'], null, $result['lastname'], $result['firstname']);
				$award = new InternalAward($result['id'], $result['title'], $result['award_terms'], $result['disabled']);
				
				$receipt = new InternalAwardReceipt( $result['user_id'], $award, $result['award_receipt_id'], $result['year']);
				$receipts[] = $receipt;
			}
		}
		return new self($receipts);
	}
}