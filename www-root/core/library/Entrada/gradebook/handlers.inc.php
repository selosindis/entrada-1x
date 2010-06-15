<?php

function assessment_handler($assessment) {
	if(isset($assessment["handler"])) {
		$klass = $assessment["handler"] . "GradeHandler";
		return new $klass($assessment);
	}
	return false;
}

function assessment_suffix($assessment) {
	$handler = assessment_handler($assessment);
	return $handler->presentationSuffix(); 
}

function format_input_grade($grade, $assessment) {
	$handler = assessment_handler($assessment);
	return $handler->getFormattedGradeFromInput($grade);
}

function format_retrieved_grade($grade, $assessment) {
	$handler = assessment_handler($assessment);
	return $handler->getFormattedGradeFromDecimal($grade);	
}

abstract class MarkingSchemeHandlerAbstract {
	private $assessment;
	function __construct($assessment) {
		$this->assessment = $assessment;
	}
	public abstract function getDecimalGrade($input);
	public abstract function getFormattedGradeFromDecimal($decimal);
	
	public function getFormattedGradeFromInput($input) {
 		$input = $this->getDecimalGrade($input);
		return $this->getFormattedGradeFromDecimal($input);
	}
	
	public function stripNumericInput($input) {
	 	$input = preg_replace("/(?![0-9\.])/", "", $input);
		if($input > 100) {
			$input = 100;
		}
		if($input < 0) {
			$input = 0;
		}
		if(!($input >= 0) && !($input <= 100)) {
			$input = 0;
		}
		return $input;
	}
	
	public function presentationSuffix() {
		return "";
	}
}

class PercentageGradeHandler extends MarkingSchemeHandlerAbstract {
	public function getDecimalGrade($input) {
		$input = $this->stripNumericInput($input);
		return intval($input);
	}
	public function getFormattedGradeFromDecimal($decimal) {
		$decimal = $this->stripNumericInput($decimal);
		return $decimal;
	}
	public function presentationSuffix() {
		return "%";
	}
}

class NumericGradeHandler extends MarkingSchemeHandlerAbstract {
	public function getDecimalGrade($input) {
		$input = $this->stripNumericInput($input);
		if($input >= 0) {
			return intval($input)/(20);
		} else {
			return "";
		}
	}

	public function getFormattedGradeFromDecimal($decimal) {
		if($decimal >= 0) {
			return $decimal*20;
		} else {
			return "";
		}
	}
	
	public function presentationSuffix() {
		return "/20";
	}
}

class BooleanGradeHandler extends MarkingSchemeHandlerAbstract {
	
	private $pass_values = array("p", "pass", "1", 100);
	private $pass_text = "P";
	private $fail_text = "F";
	
	public function getDecimalGrade($input) {
		$input = strtolower($input);
		
		if(in_array($input, $this->pass_values)) {
			return 100;
		} else {
			return 0;
		}
	} 
	
	public function getFormattedGradeFromDecimal($decimal) {
		if($decimal == 100) {
			return $this->pass_text;
		} else {
			return $this->fail_text;
		}
	}
}

class IncompleteCompleteGradeHandler extends BooleanGradeHandler {
	private $pass_values = array("p", "pass", "1", "c", "complete", 100);
	private $pass_text = "Complete";
	private $fail_text = "Incomplete";
}