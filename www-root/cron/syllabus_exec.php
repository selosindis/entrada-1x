<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 * 
 * Cron needs run the following command at any interval of your choice in order to update the syllabi.
 * I would suggest running it every 6 hours, because it's sort of extensive on the server.
 * /home/ccs/qlib/apps/php/bin/php -f /export/home/hippocrates/courses/www/cron/syllabus_exec.php
 *
 * $Id: syllabus_exec.php 1103 2010-04-05 15:20:37Z simpson $
*/

@set_time_limit(0);
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if((is_array($APPLICATION_PATH)) && (isset($APPLICATION_PATH["htmldoc"])) && (@is_executable($APPLICATION_PATH["htmldoc"]))) {
	$output_file	= ENTRADA_ABSOLUTE."/syllabi/class-of-%GRADYEAR%_phase-%PHASE%.pdf";
	$exec_command	= $APPLICATION_PATH["htmldoc"]." \
	--format pdf14 \
	--charset ".DEFAULT_CHARSET." \
	--size Letter \
	--pagemode document \
	--portrait \
	--no-duplex \
	--encryption \
	--compression=6 \
	--permissions print \
	--permissions no-modify \
	--browserwidth 800 \
	--top 1cm \
	--bottom 1cm \
	--left 2cm \
	--right 2cm \
	--header \
	--footer \
	--embedfonts \
	--bodyfont Helvetica \
	--headfootsize 8 \
	--headfootfont Courier \
	--firstpage p1 \
	--titleimage ".ENTRADA_URL."/images/syllabus_logo.gif \
	--quiet \
	--book '".ENTRADA_URL."/cron/syllabus_gen.php?grad=%GRADYEAR%&phase=%PHASE%' \
	--outfile ".$output_file;
	
	$timestamp	= time();
	$graduation	= fetch_first_year();
	
	$phases = array("1", "2", "T3", "T4", "2A", "2B", "2C", "2E", "3");
	
	for($grad_year = $graduation; $grad_year > ($graduation - 3); $grad_year--) {
		foreach($phases as $phase) {
			$command	= str_replace(array("%GRADYEAR%", "%PHASE%"), array($grad_year, $phase), $exec_command);
			$filename	= str_replace(array("%GRADYEAR%", "%PHASE%"), array($grad_year, $phase), $output_file);
			
			@exec($command);
			@chmod($filename, 0644);
		}
	}
} else {
	application_log("error", "Unable to locate the executable HTMLDoc application that is required to generate the syllabus'");
}
?>