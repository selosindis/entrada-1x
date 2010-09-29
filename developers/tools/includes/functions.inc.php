<?php
/**
 * Entrada Tools
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @copyright Copyright 2010 Queen's University, MEdTech Unit
 *
 * $Id: functions.inc.php 1080 2010-03-26 17:33:23Z simpson $
*/

/**
 * Outputs an error message, and logs it.

 * @param string $message
 * @return string
 */
function output_error($message = "") {
	global $ERROR;
	
	if(isset($ERROR)) {
		$ERROR++;
	}
	
	if($message = clean_input($message)) {
		$message = "[ERROR]   ".$message;

		log_message($message);

		echo "\n".$message;
	}
	
	@flush();
}

/**
 * Outputs a notice message, and logs it.

 * @param string $message
 * @return string
 */
function output_notice($message = "") {
	global $NOTICE;
	
	if(isset($NOTICE)) {
		$NOTICE++;
	}

	if($message = clean_input($message)) {
		$message = "[NOTICE]  ".$message;

		log_message($message);

		echo "\n".$message;
	}

	@flush();
}

/**
 * Outputs a success message, and logs it.

 * @param string $message
 * @return string
 */
function output_success($message = "") {
	global $SUCCESS;
	
	if(isset($SUCCESS)) {
		$SUCCESS++;
	}
	
	if($message = clean_input($message)) {
		$message = "[SUCCESS] ".$message;

		log_message($message);
		
		echo "\n".$message;
	}
	
	@flush();
}

/**
 * Logs any of the messages that are set by output_error(), output_notice() or
 * output_success();

 * @param string $message
 * @return bool

 */
function log_message($message = "") {
	global $ENABLE_LOGGING, $LOG_FILENAME;

	if((isset($ENABLE_LOGGING)) && ((bool) $ENABLE_LOGGING) && (isset($LOG_FILENAME)) && ($LOG_FILENAME != "") && ((is_writable(dirname($LOG_FILENAME))) || (is_writable($LOG_FILENAME)))) {
		if(file_put_contents($LOG_FILENAME, $message."\n", FILE_APPEND)) {
			return true;
		}
	}
	
	return false;
}

/**
 * This function will generate a fairly random hash code which
 * can be used in a number of situations.
 *
 * @param int $num_chars
 * @return string
 */
function generate_hash($num_chars = 32) {
	if(!$num_chars = (int) $num_chars) {
		$num_chars = 32;
	}

	return substr(md5(uniqid(rand(), 1)), 0, $num_chars);
}

/**
 * Function will return an the new release_date / release_until dates
 * for the new event_date based on the old_event data and old release details.
 * 
 * @param int $old_event_date
 * @param int $new_event_date
 * @param int $old_release_date
 * 
 * return int
 */
function offset_validity($old_event_date, $new_event_date, $old_release_date) {
	if((int) $old_event_date && (int) $new_event_date && (int) $old_release_date) {
		return ($new_event_date + ($old_release_date - $old_event_date));
	}

	return 0;
}

/**
 * Function generates a medium-strong password for the account.
 * 
 * @param int $length
 * 
 * @return string
 */
function generate_password($length = 8) {
	$length = (int) $length;

	if(($length < 6) || ($length > 32)) {
		$length = 8;
	}
	
	return substr(md5(uniqid(rand(), true)), 0, $length);
}

/**
 * This function returns the data from the events table for the provided
 * event_id.
 * 
 * @param int $event_id
 * 
 * @return array
 */
function get_event_data($event_id = 0) {
	global $db;

	/**
	 * If we pass this function an array of events, use the first one.
	 */	
	if(is_array($event_id)) {
		$event_id = $event_id[0];
	}
	
	if($event_id = (int) $event_id) {
		$query	= "SELECT * FROM `events` WHERE `event_id` = ".$db->qstr($event_id);
		
		return $db->GetRow($query);
	}
	
	return false;
}

/**
 * This function takes an event_id and checks to see if it exists in the events
 * table.
 * 
 * @param int $event_id
 * 
 * @return bool
 */
function validate_event_id($event_id = 0) {
	global $db;

	if($event_id = (int) $event_id) {
		$query	= "SELECT `event_id` FROM `events` WHERE `event_id` = ".$db->qstr($event_id);
		$result	= $db->GetRow($query);
		
		if($result) {
			return true;	
		}
	}
	
	return false;
}

/**
 * This function takes a proxy_id and returns basic information about this user.
 * 
 * @param int $proxy_id
 * 
 * @return array
 */
function get_user_info($proxy_id = 0) {
	global $db;

	if($proxy_id = (int) $proxy_id) {
		$query	= "SELECT `number`, `firstname`, `lastname` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($proxy_id);
		$result	= $db->GetRow($query);
		if($result) {
			return $result;
		}
	}

	return false;
}

/**
 * This function attempts to get the course_id of a course based on the title.
 * 
 * @param string $course_name
 * 
 * @return int
 */
function get_course_id($course_name = "") {
	global $db;

	if(trim($course_name) != "") {
		$query	= "SELECT `course_id` FROM `courses` WHERE `course_name` LIKE ".$db->qstr($course_name);
		$result	= $db->GetRow($query);
		if($result) {
			return $result["course_id"];
		}
	}
	
	return 0;
}

/**
 * This function attempts to get the eventtype_id of a event based on the event type title provided.
 *
 * @param string $eventtype_title
 *
 * @return int
 */
function get_eventtype_id($eventtype_title = "") {
	global $db;

	if(trim($eventtype_title) != "") {
		$query	= "SELECT `eventtype_id` FROM `events_lu_eventtypes` WHERE `eventtype_title` LIKE ".$db->qstr($eventtype_title);
		$result	= $db->GetRow($query);
		if($result) {
			return $result["eventtype_id"];
		}
	}

	return 0;
}

/**
 * This function takes the given staff number and returns the users
 * proxy_id (entrada_auth.user_data.id).
 * 
 * @param int $staff_number
 * 
 * @return int
 */
function get_proxy_id($staff_number = 0) {
	global $db;

	if($staff_number = (int) $staff_number) {
		$query	= "
				SELECT a.`id` AS `proxy_id`
				FROM `".AUTH_DATABASE."`.`user_data` AS a
				LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
				ON b.`user_id` = a.`id`
				WHERE a.`number` = ".$db->qstr($staff_number);
		$result	= $db->GetRow($query);
		if($result) {
			return $result["proxy_id"];
		}
	}
	
	return 0;
}

/**
 * Wrapper function to clean_input.
 *
 * @param string $string
 * @param mixed $rules
 * @return string
 */
function clean_data($string = "", $rules = array()) {
	return clean_input($string, $rules);
}
	
/**
 * This function cleans a string with any valid rules that have been provided in the $rules array.
 * Note that $rules can also be a string if you only want to apply a single rule.
 * If no rules are provided, then the string will simply be trimmed using the trim() function.
 * @param string $string
 * @param array $rules
 * @return string
 * @example $variable = clean_input(" 1235\t\t", array("nows", "int")); // $variable will equal an integer value of 1235.
 */
function clean_input($string, $rules = array()) {
	if (is_scalar($rules)) {
		if (trim($rules) != "") {
			$rules = array($rules);
		} else {
			$rules = array();
		}
	}

	if (count($rules) > 0) {
		foreach ($rules as $rule) {
			switch ($rule) {
				case "page_url" :		// Acceptable characters for community page urls.
				case "module" :
					$string = preg_replace("/[^a-z0-9_\-]/i", "", $string);
				break;
				case "url" :			// Allows only a minimal number of characters
					$string = preg_replace(array("/[^a-z0-9_\-\.\/\~\?\&\:\#\=\+]/i", "/(\.)\.+/", "/(\/)\/+/"), "$1", $string);
				break;
				case "file" :
				case "dir" :			// Allows only a minimal number of characters
					$string = preg_replace(array("/[^a-z0-9_\-\.\/]/i", "/(\.)\.+/", "/(\/)\/+/"), "$1", $string);
				break;
				case "int" :			// Change string to an integer.
					$string = (int) $string;
				break;
				case "float" :			// Change string to a float.
					$string = (float) $string;
				break;
				case "bool" :			// Change string to a boolean.
					$string = (bool) $string;
				break;
				case "nows" :			// Trim all whitespace anywhere in the string.
					$string = str_replace(array(" ", "\t", "\n", "\r", "\0", "\x0B", "&nbsp;"), "", $string);
				break;
				case "trim" :			// Trim whitespace from ends of string.
					$string = trim($string);
				break;
				case "trimds" :			// Removes double spaces.
					$string = str_replace(array(" ", "\t", "\n", "\r", "\0", "\x0B", "&nbsp;", "\x7f", "\xff", "\x0", "\x1f"), " ", $string);
					$string = html_decode(str_replace("&nbsp;", "", html_encode($string)));
				break;
				case "nl2br" :
					$string = nl2br($string);
				break;
				case "underscores" :	// Trim all whitespace anywhere in the string.
					$string = str_replace(array(" ", "\t", "\n", "\r", "\0", "\x0B", "&nbsp;"), "_", $string);
				break;
				case "lower" :			// Change string to all lower case.
				case "lowercase" :
					$string = strtolower($string);
				break;
				case "upper" :			// Change string to all upper case.
				case "uppercase" :
					$string = strtoupper($string);
				break;
				case "ucwords" :		// Change string to correct word case.
					$string = ucwords(strtolower($string));
				break;
				case "boolops" :		// Removed recognized boolean operators.
					$string = str_replace(array("\"", "+", "-", "AND", "OR", "NOT", "(", ")", ",", "-"), "", $string);
				break;
				case "quotemeta" :		// Quote's meta characters
					$string = quotemeta($string);
				break;
				case "credentials" :	// Acceptable characters for login credentials.
					$string = preg_replace("/[^a-z0-9_\-\.]/i", "", $string);
				break;
				case "alphanumeric" :	// Remove anything that is not alphanumeric.
					$string = preg_replace("/[^a-z0-9]+/i", "", $string);
				break;
				case "alpha" :			// Remove anything that is not an alpha.
					$string = preg_replace("/[^a-z]+/i", "", $string);
				break;
				case "numeric" :		// Remove everything but numbers 0 - 9 for when int won't do.
					$string = preg_replace("/[^0-9]+/i", "", $string);
				break;
				case "name" :			// @todo jellis ?
					$string = preg_replace("/^([a-z]+(\'|-|\.\s|\s)?[a-z]*){1,2}$/i", "", $string);
				break;
				case "emailcontent" :	// Check for evil tags that could be used to spam.
					$string = str_ireplace(array("content-type:", "bcc:","to:", "cc:"), "", $string);
				break;
				case "postclean" :		// @todo jellis ?
					$string = preg_replace('/\<br\s*\/?\>/i', "\n", $string);
					$string = str_replace("&nbsp;", " ", $string);
				break;
				case "decode" :			// Returns the output of the html_decode() function.
					$string = html_decode($string);
				break;
				case "encode" :			// Returns the output of the html_encode() function.
					$string = html_encode($string);
				break;
				case "htmlspecialchars" : // Returns the output of the htmlspecialchars() function.
				case "specialchars" :
					$string = htmlspecialchars($string, ENT_QUOTES, DEFAULT_CHARSET);
				break;
				case "htmlbrackets" :	// Converts only brackets into entities.
					$string = str_replace(array("<", ">"), array("&lt;", "&gt;"), $string);
				break;
				case "notags" :			// Strips tags from the string.
				case "nohtml" :
				case "striptags" :
					$string = strip_tags($string);
				break;
				default :				// Unknown rule, log notice.
					application_log("notice", "Unknown clean_input function rule [".$rule."]");
				break;
			}
		}

		return $string;
	} else {
		return trim($string);
	}
}

/**
 * Processes / resizes and creates properly sized image and thumbnail image
 * for images uploaded to the galleries module.
 *
 * @param string $original_file
 * @param int $photo_id
 * @return bool
 */
function process_user_photo_official($original_file, $proxy_id = 0) {
	global $VALID_MAX_DIMENSIONS;

	if(!@function_exists("gd_info")) {
		echo "Error: ".__LINE__;

		return false;
	}

	if((!@file_exists($original_file)) || (!@is_readable($original_file))) {
		echo "Error: ".__LINE__;

		return false;
	}

	if(!$proxy_id = (int) $proxy_id) {
		echo "Error: ".__LINE__;

		return false;
	}

	$new_file		= STORAGE_USER_PHOTOS."/".$proxy_id."-official";
	$img_quality	= 85;

	if($original_file_details = @getimagesize($original_file)) {
		$original_file_width	= $original_file_details[0];
		$original_file_height	= $original_file_details[1];

		/**
		 * Check if the original_file needs to be resized or not.
		 */
		if(($original_file_width > $VALID_MAX_DIMENSIONS["photo-width"]) || ($original_file_height > $VALID_MAX_DIMENSIONS["photo-height"])) {
			switch($original_file_details["mime"]) {
				case "image/pjpeg":
				case "image/jpeg":
				case "image/jpg":
					$original_img_resource = @imagecreatefromjpeg($original_file);
				break;
				case "image/png":
					$original_img_resource = @imagecreatefrompng($original_file);
				break;
				case "image/gif":
					$original_img_resource = @imagecreatefromgif($original_file);
				break;
				default :
					echo "Error: ".__LINE__;

					return false;
				break;
			}
			if($original_img_resource) {
				/**
				 * Determine whether it's a horizontal / vertical image and calculate the new smaller size.
				 */
				if($original_file_width > $original_file_height) {
					$new_file_width		= $VALID_MAX_DIMENSIONS["photo-width"];
					$new_file_height	= (int) (($VALID_MAX_DIMENSIONS["photo-width"] * $original_file_height) / $original_file_width);
				} else {
					$new_file_width		= (int) (($VALID_MAX_DIMENSIONS["photo-height"] * $original_file_width) / $original_file_height);
					$new_file_height	= $VALID_MAX_DIMENSIONS["photo-height"];
				}

				if($original_file_details["mime"] == "image/gif") {
					$new_img_resource = @imagecreate($new_file_width, $new_file_height);
				} else {
					$new_img_resource = @imagecreatetruecolor($new_file_width, $new_file_height);
				}

				if($new_img_resource) {
					if(@imagecopyresampled($new_img_resource, $original_img_resource, 0, 0, 0, 0, $new_file_width, $new_file_height, $original_file_width, $original_file_height)) {
						switch($original_file_details["mime"]) {
							case "image/pjpeg":
							case "image/jpeg":
							case "image/jpg":
								if(!imagejpeg($new_img_resource, $new_file, $img_quality)) {
									echo "Error: ".__LINE__;

									return false;
								}
							break;
							case "image/png":
								if(!@imagepng($new_img_resource, $new_file)) {
									echo "Error: ".__LINE__;

									return false;
								}
							break;
							case "image/gif":
								if(!@imagegif($new_img_resource, $new_file)) {
									echo "Error: ".__LINE__;

									return false;
								}
							break;
							default :
								echo "Error: ".__LINE__;

								return false;
							break;
						}

						@chmod($new_file, 0644);

						/**
						 * Frees the memory this used, so it can be used again for the thumbnail.
						 */
						@imagedestroy($original_img_resource);
						@imagedestroy($new_img_resource);
					} else {
						echo "Error: ".__LINE__;

						return false;
					}
				} else {
					echo "Error: ".__LINE__;

					return false;
				}
			} else {
				echo "Error: ".__LINE__;

				return false;
			}
		} else {
			if(@move_uploaded_file($original_file, $new_file)) {
				@chmod($new_file, 0644);

				/**
				 * Create the new width / height so we can use the same variables
				 * below for thumbnail generation.
				 */
				$new_file_width		= $original_file_width;
				$new_file_height	= $original_file_height;
			} else {
				echo "Error: ".__LINE__;

				return false;
			}
		}

		/**
		 * Check that the new_file exists, and can be used, then proceed
		 * with Thumbnail generation ($new_file-thumbnail).
		 */
		if((@file_exists($new_file)) && (@is_readable($new_file))) {

			switch($original_file_details["mime"]) {
				case "image/pjpeg":
				case "image/jpeg":
				case "image/jpg":
					$original_img_resource = @imagecreatefromjpeg($new_file);
				break;
				case "image/png":
					$original_img_resource = @imagecreatefrompng($new_file);
				break;
				case "image/gif":
					$original_img_resource = @imagecreatefromgif($new_file);
				break;
				default :
					echo "Error: ".__LINE__;

					return false;
				break;
			}

			if(($new_file_width > $VALID_MAX_DIMENSIONS["thumb-width"]) || ($new_file_height > $VALID_MAX_DIMENSIONS["thumb-height"])) {
				$dest_x			= 0;
				$dest_y			= 0;
				$ratio_orig		= ($new_file_width / $new_file_height);
				$cropped_width	= $VALID_MAX_DIMENSIONS["thumb-width"];
				$cropped_height	= $VALID_MAX_DIMENSIONS["thumb-height"];

				if($ratio_orig > 1) {
				   $cropped_width	= ($cropped_height * $ratio_orig);
				} else {
				   $cropped_height	= ($cropped_width / $ratio_orig);
				}
			} else {
				$cropped_width	= $new_file_width;
				$cropped_height	= $new_file_height;

				$dest_x			= ($VALID_MAX_DIMENSIONS["thumb-width"] / 2) - ($cropped_width / 2);
				$dest_y			= ($VALID_MAX_DIMENSIONS["thumb-height"] / 2) - ($cropped_height / 2 );
			}

			if($original_file_details["mime"] == "image/gif") {
				$new_img_resource = @imagecreate($VALID_MAX_DIMENSIONS["thumb-width"], $VALID_MAX_DIMENSIONS["thumb-height"]);
			} else {
				$new_img_resource = @imagecreatetruecolor($VALID_MAX_DIMENSIONS["thumb-width"], $VALID_MAX_DIMENSIONS["thumb-height"]);
			}

			if($new_img_resource) {
				if(@imagecopyresampled($new_img_resource, $original_img_resource, $dest_x, $dest_y, 0, 0, $cropped_width, $cropped_height, $new_file_width, $new_file_height)) {
					switch($original_file_details["mime"]) {
						case "image/pjpeg":
						case "image/jpeg":
						case "image/jpg":
							if(!@imagejpeg($new_img_resource, $new_file."-thumbnail", $img_quality)) {
								echo "Error: ".__LINE__;

								return false;
							}
						break;
						case "image/png":
							if(!@imagepng($new_img_resource, $new_file."-thumbnail")) {
								echo "Error: ".__LINE__;

								return false;
							}
						break;
						case "image/gif":
							if(!@imagegif($new_img_resource, $new_file."-thumbnail")) {
								echo "Error: ".__LINE__;

								return false;
							}
						break;
						default :
							echo "Error: ".__LINE__;

							return false;
						break;
					}

					@chmod($new_file."-thumbnail", 0644);

					/**
					 * Frees the memory this used, so it can be used again.
					 */
					@imagedestroy($original_img_resource);
					@imagedestroy($new_img_resource);

					/**
					 * Keep a copy of the original file, just in case it is needed.
					 */
					if(@copy($original_file, $new_file."-original")) {
						@chmod($new_file."-original", 0644);
					}

					return true;
				}
			} else {
				echo "Error: ".__LINE__;

				return false;
			}
		} else {
			echo "Error: ".__LINE__;

			return false;
		}
	} else {
		echo "Error: ".__LINE__;

		return false;
	}
}

/**
 * Wrapper function for html_entities.
 *
 * @param string $string
 * @return string
 */
function html_encode($string) {
	return htmlentities($string, ENT_QUOTES, DEFAULT_CHARSET);
}

/**
 * Wrapper for PHP's html_entities_decode function.
 *
 * @param string $string
 * @return string
 */
function html_decode($string) {
	return html_entity_decode($string, ENT_QUOTES, DEFAULT_CHARSET);
}

/**
 * Function to select and return all event ids which have a possible connected
 * rotation id, and return them as an array.
 */
function get_event_rotation_ids() {
	global $db;
	$events = array();
	$query = "SELECT `category_id`, `rotation_id` FROM `categories` WHERE `rotation_id` > 0";
	$categories = $db->GetAll($query);
	if ($categories) {
		foreach ($categories as $category) {
			$query = "SELECT `event_id` FROM `events` WHERE `category_id` = ".$db->qstr($category["category_id"]);
			$event_ids = $db->GetAll($query);
			if ($event_ids) {
				foreach ($event_ids as $event_id) {
					$events[] = array(	"event_id" => $event_id["event_id"],
										"rotation_id" => $category["rotation_id"]);
				}
			}
		}
	}
	return $events;
}
?>