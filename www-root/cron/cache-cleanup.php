<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Cron job responsible for cleaning up the cache directory after ADODB.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <simpson@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
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

$command = "find ".CACHE_DIRECTORY." -mtime +7 | grep '\.cache' | xargs rm -f";
exec($command);