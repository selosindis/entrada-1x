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
 * The default file that is loaded when /admin/users is accessed.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

abstract class Award {
	protected $title;
	protected $terms;
	protected $awarding_body;
	
	function __construct($title, $terms, $awarding_body) {
		$this->title = $title;
		$this->terms = $terms;
		$this->awarding_body = $awarding_body; 
	}
	
	function compare ($award) {
		return strcasecmp($this->title,  $award->title);
	}
	
	static function compare_awards(Award $award_1, Award $award_2) {
		return $award_1->compare($award_2);
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function getTerms() {
		return $this->terms;
	}
	
	public function getAwardingBody() {
		return $this->awarding_body;
	}
}