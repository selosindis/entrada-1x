<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 *
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
*/

class Template {
	
	/**
	 * 
	 * @var DOMDocument
	 */
	private $_template_data;
	
	function __construct($filename="") {
		if ($filename) {
			$this->loadFile($filename);
		}
	}
	
	/**
	 * 
	 * @param string $filename file with xml template data
	 */
	public function loadFile($filename) {
		$filename = realpath($filename);
		if (file_exists($filename)) {
			$this->_template_data = new DOMDocument();
			$this->_template_data->preserveWhiteSpace = false;
			$this->_template_data->load($filename);
			//note the above instruction to NOT preserve whitespace is required. PHP does not handle cdata sections properly if there is *any* text around them. this could obviously mess up a <pre> section if you have one 
		} else {
			throw new RuntimeException("File not found: " .$filename);
		} 
	}
	
	public function getResult($language="", array $bind_array = array(), array $select = array()) {
		if (!$language) {
			if (defined("DEFAULT_LANGUAGE")) {
				$language = DEFAULT_LANGUAGE;
			} else {
				throw new RuntimeException("Invalid language provided and no default language set.");
			}
		}
		
		if ($this->_template_data) {
			$xpath = new DOMXpath($this->_template_data);
			$t_query = "//template[@lang='".$language."'";
			if ($select) {
				foreach ($select as $attribute=>$criteria) {
					$t_query .= " and @".$attribute."='".$criteria."'";
				}
			}
			$t_query .= "]";
			$t_data = $xpath->query($t_query);
			
			//if the selected language is not available, try the default
			if (($t_data->length == 0) && (defined("DEFAULT_LANGUAGE"))) {
				$language = DEFAULT_LANGUAGE;
				$t_query = "//template[@lang='".$language."']";
				if ($select) {
					foreach ($select as $attribute=>$criteria) {
						$t_query .= " and @".$attribute."='".$criteria."'";
					}
				}
				$t_data = $xpath->query($t_query);
			}
			
			//only take the first result, if any
			if ($t_data->length > 0) {
				$template = $t_data->item(0);
				$working_template = $template->cloneNode(true);
				//$this->_template_data->documentElement->appendChild($working_template);
				//now get every descendent and process with bound values
				$n_query = "./descendant-or-self::text()";
				$nodes = $xpath->query($n_query, $working_template);
				if ($nodes->length > 0) {
					for($i=0,$len = $nodes->length; $i<$len;$i++) {
						$node = $nodes->item($i);
						$text = $node->wholeText;
						$text_len = strlen($text);
						foreach ($bind_array as $key=>$value) {
							$text = str_ireplace("%".strtoupper($key)."%",$value,$text);
						}
						$node->replaceData(0,$text_len,$text);
					}
					
				}
				$sxl = simplexml_import_dom($working_template);
				return $sxl;
				
			}

		} else {
			throw new Exception("Template not loaded.");
		}
	}	
}