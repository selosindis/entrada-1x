<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * The web-based Entrada setup utility.
 *
 * @author Organisation: University of Calgary
 * @author Unit: Faculty of Medicine
 * @author Developer: Ilya Sorokin <isorokin@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
 * @version $Id: init.inc.php 1086 2010-04-01 22:43:13Z simpson $
 */

ini_set('display_errors', '1');

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

define("DATABASE_TYPE", "mysqli");

/**
 * Register the Zend autoloader so we use any part of Zend Framework without
 * the need to require the specific Zend Framework files.
 */
require_once("Zend/Loader/Autoloader.php");
$loader = Zend_Loader_Autoloader::getInstance();

require_once("Entrada/adodb/adodb.inc.php");

require_once("includes/Entrada_Setup.php");
require_once("includes/functions.inc.php");
require_once("includes/constants.inc.php");

$ERROR = 0;
$NOTICE = 0;
$SUCCESS = 0;

$ERRORSTR = array();
$NOTICESTR = array();
$SUCCESSSTR = array();

if((isset($_GET["step"])) && ((int) trim($_GET["step"]))) {
	$STEP = (int) trim($_GET["step"]);
} elseif((isset($_POST["step"])) && ((int) trim($_POST["step"]))) {
	$STEP = (int) trim($_POST["step"]);
} else {
	$STEP = 1;
}

$PROCESSED = array();

if (isset($_POST["entrada_url"]) && ($url_parts = parse_url($_POST["entrada_url"])) && ($url_scheme = $url_parts["scheme"]) && ($tmp_url = str_replace($url_scheme."://", "", $_POST["entrada_url"])) && ($url = (isset($scheme) && $scheme ? $scheme : "http")."://".clean_input($tmp_url, "url"))) {
	$PROCESSED["entrada_url"] = $url;	
} elseif ($STEP >= 3) {
	$ERROR++;
	$ERRORSTR[] = "The URL where this instance of Entrada will be accessed must be entered before continuing.";
}

if (isset($_POST["entrada_relative"]) && ($entrada_relative = clean_input($_POST["entrada_relative"], "url"))) {
	$PROCESSED["entrada_relative"] = $entrada_relative;	
} elseif ($STEP >= 3) {
	$ERROR++;
	$ERRORSTR[] = "The relative web address where Entrada will be accessed on the host must be entered before continuing. (eg. '/entrada' if installed at the root level.)";
}

if (isset($_POST["entrada_absolute"]) && ($entrada_absolute = clean_input($_POST["entrada_absolute"], "dir"))) {
	$PROCESSED["entrada_absolute"] = $entrada_absolute;	
} elseif ($STEP >= 3) {
	$ERROR++;
	$ERRORSTR[] = "The absolute directory path on the server where Entrada will be installed must be entered before continuing.";
}

if (isset($_POST["entrada_storage"]) && ($entrada_storage = clean_input($_POST["entrada_storage"], "dir")) && (@is_dir($entrada_storage))) {
	$PROCESSED["entrada_storage"] = $entrada_storage;
} elseif ($STEP >= 3 && !@is_dir($entrada_storage)) {
	$ERROR++;
	$ERRORSTR[] = "The absolute path you have provided for the <strong>Entrada Storage Path</strong> does not not exist. Please ensure this directory exists and that all folders within it can be written to by PHP.";
} elseif ($STEP >= 3) {
	$ERROR++;
	$ERRORSTR[] = "The absolute directory path on the server where Entrada storage will be located must be entered before continuing.";
}
if ($ERROR && $STEP >= 3) {
	$STEP = 2;
}
if (isset($_POST["database_host"]) && ($database_host = clean_input($_POST["database_host"], "url"))) {
	$PROCESSED["database_host"] = $database_host;
} elseif ($STEP >= 4) {
	$ERROR++;
	$ERRORSTR[] = "The host where the entrada databases will be accessed from must be entered before continuing.";
}
if (isset($_POST["database_username"]) && ($database_username = clean_input($_POST["database_username"], "credentials"))) {
	$PROCESSED["database_username"] = $database_username;
} elseif ($STEP >= 4) {
	$ERROR++;
	$ERRORSTR[] = "The username to connect to the Entrada databases must be entered before continuing.";
}
if (isset($_POST["database_password"]) && ($database_password = $_POST["database_password"])) {
	$PROCESSED["database_password"] = $database_password;
} elseif ($STEP >= 4) {
	$ERROR++;
	$ERRORSTR[] = "The password to connect to the Entrada databases must be entered before continuing.";
}
if (isset($_POST["entrada_database"]) && ($entrada_database = clean_input($_POST["entrada_database"], "credentials"))) {
	$PROCESSED["entrada_database"] = $entrada_database;
} elseif ($STEP >= 4) {
	$ERROR++;
	$ERRORSTR[] = "The name of the primary Entrada database must be entered before continuing.";
}
if (isset($_POST["auth_database"]) && ($auth_database = clean_input($_POST["auth_database"], "credentials"))) {
	$PROCESSED["auth_database"] = $auth_database;
} elseif ($STEP >= 4) {
	$ERROR++;
	$ERRORSTR[] = "The name of the Entrada Authentication database must be entered before continuing.";
}
if (isset($_POST["clerkship_database"]) && ($clerkship_database = clean_input($_POST["clerkship_database"], "credentials"))) {
	$PROCESSED["clerkship_database"] = $clerkship_database;
} elseif ($STEP >= 4) {
	$ERROR++;
	$ERRORSTR[] = "The name of the Entrada Clerkship database must be entered before continuing.";
}

$setup = new Entrada_Setup($PROCESSED);
if ($STEP >= 4) {
	if (!$setup->checkEntradaDBConnection()) {
		$ERROR++;
		$ERRORSTR[] = "We were unable to connect to your primary <strong>Entrada Database</strong> [".(isset($PROCESSED["entrada_database"]) ? $PROCESSED["entrada_database"] : "")."].";
	}
	if (!$setup->checkAuthDBConnection()) {
		$ERROR++;
		$ERRORSTR[] = "We were unable to connect to your <strong>Authentication Database</strong> [".(isset($PROCESSED["auth_database"]) ? $PROCESSED["auth_database"] : "")."].";
	}
	if (!$setup->checkClerkshipDBConnection()) {
		$ERROR++;
		$ERRORSTR[] = "We were unable to connect to your <strong>Clerkship Database</strong> [".(isset($PROCESSED["clerkship_database"]) ? $PROCESSED["clerkship_database"] : "")."].";
	}
}

if ($ERROR && $STEP >= 4) {
	$STEP = 3;
}
unset($setup);

if (isset($_POST["admin_firstname"]) && ($admin_firstname = clean_input($_POST["admin_firstname"], "trim"))) {
	$PROCESSED["admin_firstname"] = $admin_firstname;
} elseif ($STEP >= 5) {
	$ERROR++;
	$ERRORSTR[] = "The first name of the administrator for your install of Entrada must be entered before continuing.";
}
if (isset($_POST["admin_lastname"]) && ($admin_lastname = clean_input($_POST["admin_lastname"], "trim"))) {
	$PROCESSED["admin_lastname"] = $admin_lastname;
} elseif ($STEP >= 5) {
	$ERROR++;
	$ERRORSTR[] = "The last name of the administrator for your install of Entrada must be entered before continuing.";
}
if (isset($_POST["admin_email"]) && ($admin_email = clean_input($_POST["admin_email"], array("trim", "lower")))) {
	if (@valid_address($admin_email)) {
		$PROCESSED["admin_email"] = $admin_email;
	} elseif ($STEP >= 5) {
		$ERROR++;
		$ERRORSTR[] = "A valid E-mail for the administrator of your install of Entrada must be entered before continuing.";
	}
} elseif ($STEP >= 5) {
	$ERROR++;
	$ERRORSTR[] = "A valid E-mail for the administrator of your install of Entrada must be entered before continuing.";
}

if (isset($_POST["admin_username"]) && ($admin_username = clean_input($_POST["admin_username"], "credentials"))) {
	$PROCESSED["admin_username"] = $admin_username;
} elseif ($STEP >= 5) {
	$ERROR++;
	$ERRORSTR[] = "The username of the administrator for your install of Entrada must be entered before continuing.";
}

if (isset($_POST["admin_password"]) && ($admin_password = $_POST["admin_password"])) {
	if (isset($_POST["re_admin_password"]) && ($re_admin_password = $_POST["re_admin_password"]) && $re_admin_password == $admin_password) {
		$PROCESSED["admin_password"] = $admin_password;
		$PROCESSED["admin_password_hash"] = md5($PROCESSED["admin_password"]);
	} elseif ($STEP >= 5) {
		$ERROR++;
		$ERRORSTR[] = "The two passwords you have entered for the administrator of your install of Entrada must match before continuing, please re-enter them now.";
	}
} elseif (isset($_POST["admin_password_hash"]) && ($admin_password_hash = $_POST["admin_password_hash"])) {
	$PROCESSED["admin_password_hash"] = $admin_password_hash;
} elseif ($STEP >= 5) {
		$ERROR++;
		$ERRORSTR[] = "The password of the administrator for your install of Entrada must be entered before continuing.";
}

if ($ERROR && $STEP >= 5) {
	$STEP = 4;
}

if (!isset($setup)) {
	$setup = new Entrada_Setup($PROCESSED);
}

if ($STEP == 5) {
	if ((!isset($_POST["htaccess_text"]) || !$_POST["htaccess_text"]) && !$setup->writeHTAccess()) {
		$display_htaccess = true;
	} else {
		$display_htaccess = false;
	}
	if ((!isset($_POST["config_text"]) || !$_POST["config_text"]) && !$setup->writeConfigData()) {
		$config_text = $setup->outputConfigData();

		$display_config = true;
	} else {
		$display_config = false;
	}
}
if ($STEP == 6) {
	if (isset($PROCESSED["entrada_absolute"])) {
		if (@file_exists($PROCESSED["entrada_absolute"]."/.htaccess")) {
			if (@file_exists($PROCESSED["entrada_absolute"]."/core/config/config.inc.php")) {
				try {
					if (isset($setup)) {
						unset($setup);
					}

					$setup = new Entrada_Setup($PROCESSED);
					if (!$setup->loadDumpData()) {
						$ERROR++;
					}
				} catch(Exception $e) {
					$ERROR++;
				}
			} else {
				$config_text = $setup->outputConfigData();

				$display_config = true;

				$ERROR++;
				$ERRORSTR[] = "Please make sure that you have saved the <strong>config.inc.php</strong> file before continuing.";
			}
		} else {
			$display_htaccess = true;
			
			$ERROR++;
			$ERRORSTR[] = "Please make sure that you have saved the <strong>.htaccess</strong> file before continuing.";
		}
	} else {
		$ERROR++;
	}

	if ($ERROR) {
		$STEP = 5;
	}
}

$abs_url = 'http';
if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
	$abs_url .= "s";
}
$abs_url .= "://";
if ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" && $_SERVER["SERVER_PORT"] != "443") || (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "on" && $_SERVER["SERVER_PORT"] != "80")) {
	$abs_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].implode(DIRECTORY_SEPARATOR, array_pop(explode(DIRECTORY_SEPARATOR, $_SERVER["REQUEST_URI"])));
} else {
	$abs_url .= $_SERVER["SERVER_NAME"].implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $_SERVER["REQUEST_URI"]), 0, (count(explode(DIRECTORY_SEPARATOR, $_SERVER["REQUEST_URI"])) - 2)) );
}

$rel_url = implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $_SERVER["REQUEST_URI"]), 0, (count(explode(DIRECTORY_SEPARATOR, $_SERVER["REQUEST_URI"])) - 2)) );

$abs_path = implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_FILENAME']), 0, (count(explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_FILENAME'])) - 2)) );

$storage_path = implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_FILENAME']), 0, (count(explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_FILENAME'])) - 2)) )."/core/storage";
?>
<html>
<head>
	<title>Entrada: Setup</title>
	<script type="text/javascript" src="../javascript/scriptaculous/prototype.js"></script>
	<script type="text/javascript" src="../javascript/scriptaculous/scriptaculous.js"></script>
	<style type="text/css">
		html, body {
			height: 100%;
			margin: 0;
			padding: 0;
			background-color: #F0F0EE;
		}
		button,form.inplaceeditor-form input[type=submit],input[type=button],input[type=submit],input.button-add,input.button-remove,input.button-red,input.button {
			color:#000;
			font-family:'Lucida Grande', Geneva, Verdana, Arial, Helvetica, sans-serif;
			font-size:12px;
			width:115px;
			height:22px;
			border:none;
			background:#EEE url(../images/btn_bg.gif);
		}
		.setup-window {
			line-height:150%;
			font-family:'Lucida Grande', Geneva, Verdana, Arial, Helvetica, sans-serif;
			font-size:12px;
			color:#333;
			-moz-border-radius:10px;
			-webkit-border-radius:10px;
			margin:35px 0;
			padding:15px;
			width: 700px;
			margin-left: auto;
			margin-right: auto;
			padding: 10px 40px 10px 40px;
		}

		.display-generic,.display-notice,.display-error,.display-success {
			line-height:150%;
			font-family:'Lucida Grande', Geneva, Verdana, Arial, Helvetica, sans-serif;
			font-size:12px;
			color:#333;
			-moz-border-radius:10px;
			-webkit-border-radius:10px;
			margin:25px 0;
			padding:15px;
			width: 85%;
			padding: 20px;
		}
		
		.display-success {
			background-color:#DEE6E3;
			border:1px #A9D392 solid;
		}
		
		.display-notice {
			background-color:#FFC;
			border:1px #FC0 solid;
		}
		
		.display-error {
			background-color:#FFD9D0;
			border:1px #C00 solid;
		}
		
		.setup-window {
			background-color:#FFF;
			border:1px #CCC solid;
		}
		.display-generic {
			background-color:#F0F0EE;
			border:1px #CCC solid;
		}
		.display-generic ul,.display-success ul,.display-notice ul,.display-error ul,ul.notify-communities {
			list-style-type:none;
			margin:0;
			padding:4px 10px 10px;
		}
		
		.display-generic ul li,.display-success ul li,.display-notice ul li,.display-error ul li {
			vertical-align:middle;
			padding:0 0 0 18px;
		}
		
		.display-success ul li {
			background:transparent url(../images/list-success.gif) no-repeat 0 2px;
		}
		
		.display-notice ul li {
			background:transparent url(../images/list-notice.gif) no-repeat 0 2px;
		}
		
		.display-error ul li {
			background:transparent url(../images/list-error.gif) no-repeat 0 2px;
		}
		table.setup-list {
			width: 100%;
			border-collapse: collapse;
			font-size: 12px;
		}
		table.setup-list tr.line {
			background: none repeat scroll 0 0 #FAFAFA;
		}
		table.setup-list tr {
			margin-top: 20px;
			min-height: 20px;
		}
		table.setup-list th.left {
			min-height: 20px;
			padding-left: 10px;
		}
		table.setup-list td.middle {
			min-height: 20px;
		}
		table.setup-list td.right {
			min-height: 20px;
			padding: 10px 10px 10px 10px;
		}
		table.setup-list label {
			font-weight: bold;
		}
		table.setup-list input[type=text], table.setup-list input[type=password] {
			width: 350px;
		}
		div.valign {
			display: table-cell;
			vertical-align: middle;
			position: relative;
			text-align: left;
		}
		li {
			padding-top: 3px;
		}
		textarea {
			font-size: 11px;
		}
		.content-small {
			font-family:'Lucida Grande', Geneva, Verdana, Arial, Helvetica, sans-serif;
			font-size:11px;
			font-style:normal;
			color:#666;
		}
	</style>
</head>
<body>
	<div class="setup-window">
		<img src="../images/entrada-logo.gif" width="296" height="50" alt="Entrada Logo" title="Welcome to Entrada" style="margin-top: 5px" />
		<?php
		if ($ERROR && count($ERRORSTR)) {
			echo display_error();
		}
		if ($NOTICE) {
			echo display_notice();
		}
		if ($SUCCESS) {
			echo display_success();
		}
		?>
		<form action="index.php?<?php echo replace_query(array("step" => (!$ERROR || count($ERRORSTR) ? $STEP + 1 : $STEP))); ?>" method="post">
			<input name="step" id="step" type="hidden" value="<?php echo $STEP; ?>" />
			<div id="step_1"<?php echo ($STEP == 1 ? "" : " style=\"display: none;\""); ?>>
				<div class="display-generic">
					Welcome to the <strong>Entrada setup</strong> program. Before we begin please be aware that Entrada is open source software, and is licensed under the GNU General Public License (GPL v3). By continuing you acknowledge that you have read and agree to the terms of the license.
				</div>
				<h2>Step 1: Software License Agreement</h2>
				<textarea cols="80" rows="15" style="width: 637px; height: 200px;" readonly="readonly"><?php echo $GNU; ?></textarea>
			</div>
			<div id="step_2"<?php echo ($STEP == 2 ? "" : " style=\"display: none;\""); ?>>
				<div class="display-generic">
					Entrada requires a bit of information about where this installation is located on your server, and how it will be accessed via the web-browser. We have tried to pre-populate this information, but please review each field and confirm it is correct before continuing.
					<br /><br />
					This data will be written to your <span style="font-family: monospace">core/config/config.inc.php</span> file later in the setup process.
				</div>
				<h2>Step 2: URL &amp; Path Information</h2>
				<table class="setup-list">
					<colgroup>
						<col width="25%" />
						<col width="75%" />
					</colgroup>
					<tr>
						<td>
							<div class="valign">
								<label for="entrada_url">Entrada URL</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="text" id="entrada_url" name="entrada_url" value="<?php echo (isset($PROCESSED["entrada_url"]) && $PROCESSED["entrada_url"] ? $PROCESSED["entrada_url"] : $abs_url); ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							Full URL to application's index file without a trailing slash.
						</td>
					</tr>
					<tr>
						<td>
							<div class="valign">
								<label for="entrada_relative">Entrada Relative URL</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="text" id="entrada_relative" name="entrada_relative" value="<?php echo (isset($PROCESSED["entrada_relative"]) && $PROCESSED["entrada_relative"] ? $PROCESSED["entrada_relative"] : $rel_url); ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							The relative URL where Entrada will live on your host.
						</td>
					</tr>
					<tr>
						<td>
							<div class="valign">
								<label for="entrada_absolute">Entrada Absolute Path</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="text" value="<?php echo (isset($PROCESSED["entrada_absolute"]) && $PROCESSED["entrada_absolute"] ? $PROCESSED["entrada_absolute"] : $abs_path); ?>" name="entrada_absolute"/>
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							Full Directory Path to application's index file without a trailing slash.
						</td>
					</tr>
					<tr>
						<td>
							<div class="valign">
								<label for="entrada_storage">Entrada Storage Path</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="text" value="<?php echo (isset($PROCESSED["entrada_storage"]) && $PROCESSED["entrada_storage"] ? $PROCESSED["entrada_storage"] : $storage_path); ?>" name="entrada_storage"/>
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							Full Directory Path to the Entrada storage folder.
						</td>
					</tr>
				</table>
			</div>
			<div id="step_3"<?php echo ($STEP == 3 ? "" : " style=\"display: none;\""); ?>>
				<div class="display-generic">
					<strong>Before completing this step</strong> please log into your MySQL server and create <strong>three</strong> new databases (i.e. entrada, entrada_auth, and entrada_clerkship) that Entrada will use to store its data. Also you will need to create a new MySQL user account that has full privileges to each of these databases.
				</div>
				<h2>Step 3: Database Connection Information</h2>
				<table class="setup-list">
					<colgroup>
						<col width="25%" />
						<col width="75%" />
					</colgroup>
					<tr>
						<td>
							<div class="valign">
								<label for="database_host">MySQL Hostname</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="text" id="database_host" name="database_host" value="<?php echo (isset($PROCESSED["database_host"]) && $PROCESSED["database_host"] ? $PROCESSED["database_host"] : "localhost"); ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							The hostname of your MySQL server.
						</td>
					</tr>
					<tr>
						<td>
							<div class="valign">
								<label for="database_username">MySQL Username</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="text" id="database_username" name="database_username" value="<?php echo (isset($PROCESSED["database_username"]) && $PROCESSED["database_username"] ? $PROCESSED["database_username"] : "entrada"); ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							The MySQL user with full privileges to each of the databases below.
						</td>
					</tr>

					<tr>
						<td>
							<div class="valign">
								<label for="database_password">MySQL Password</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="text" id="database_password" name="database_password" value="<?php echo (isset($PROCESSED["database_password"]) && $PROCESSED["database_password"] ? $PROCESSED["database_password"] : ""); ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							The password of your new MySQL user.
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td>
							<div class="valign">
								<label for="entrada_database">Entrada Database</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="text" id="entrada_database" name="entrada_database" value="<?php echo (isset($PROCESSED["entrada_database"]) && $PROCESSED["entrada_database"] ? $PROCESSED["entrada_database"] : "entrada"); ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							1 of 3: The name of your primary Entrada database.
						</td>
					</tr>

					<tr>
						<td>
							<div class="valign">
								<label for="auth_database">Authentication Database</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="text" id="auth_database" name="auth_database" value="<?php echo (isset($PROCESSED["auth_database"]) && $PROCESSED["auth_database"] ? $PROCESSED["auth_database"] : "entrada_auth"); ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							2 of 3: The name of your Entrada authentication database.
						</td>
					</tr>

					<tr>
						<td>
							<div class="valign">
								<label for="clerkship_database">Clerkship Database</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="text" id="clerkship_database" name="clerkship_database" value="<?php echo (isset($PROCESSED["clerkship_database"]) && $PROCESSED["clerkship_database"] ? $PROCESSED["clerkship_database"] : "entrada_clerkship"); ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							3 of 3: The name of your Entrada Clerkship database.
						</td>
					</tr>

				</table>
			</div>
			<div id="step_4"<?php echo ($STEP == 4 ? "" : " style=\"display: none;\""); ?>>
				<div class="display-generic">
					Please create a new <strong>system administrator account</strong> that you will use to manage your Entrada installation. Additional accounts can be created later in the <strong>Admin &gt; Manage Users</strong> section.
				</div>
				<h2>Step 4: System Administrator Account</h2>
				<table class="setup-list">
					<colgroup>
						<col width="25%" />
						<col width="75%" />
					</colgroup>
					<tr>
						<td>
							<div class="valign">
								<label for="admin_firstname">Firstname</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="text" id="admin_firstname" name="admin_firstname" value="<?php echo (isset($PROCESSED["admin_firstname"]) && $PROCESSED["admin_firstname"] ? $PROCESSED["admin_firstname"] : "System"); ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							The first name of the system administrator.
						</td>
					</tr>

					<tr>
						<td>
							<div class="valign">
								<label for="admin_lastname">Lastname</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="text" id="admin_lastname" name="admin_lastname" value="<?php echo (isset($PROCESSED["admin_lastname"]) && $PROCESSED["admin_lastname"] ? $PROCESSED["admin_lastname"] : "Administrator"); ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							The lastname name of the system administrator.
						</td>
					</tr>

					<tr>
						<td>
							<div class="valign">
								<label for="admin_email">E-Mail Address</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="text" id="admin_email" name="admin_email" value="<?php echo (isset($PROCESSED["admin_email"]) && $PROCESSED["admin_email"] ? $PROCESSED["admin_email"] : ""); ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							The email address of the system administrator.
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td>
							<div class="valign">
								<label for="admin_username">Username</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="text" id="admin_username" name="admin_username" value="<?php echo (isset($PROCESSED["admin_username"]) && $PROCESSED["admin_username"] ? $PROCESSED["admin_username"] : ""); ?>" />
							</div>
						</td>
						<td class="right">
							<span></span>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							A username for the system administrator account.
						</td>
					</tr>

					<tr>
						<td>
							<div class="valign">
								<label for="admin_password">Password</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="password" id="admin_password" name="admin_password" value="" />
								<input type="hidden" id="admin_password_hash" name="admin_password_hash" value="<?php echo (isset($PROCESSED["admin_password_hash"]) && $PROCESSED["admin_password_hash"] ? $PROCESSED["admin_password_hash"] : ""); ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							A secure password for the administrator account.
						</td>
					</tr>

					<tr>
						<td>
							<div class="valign">
								<label for="re_admin_password">Confirm Password</label>
							</div>
						</td>
						<td>
							<div class="valign">
								<input type="password" id="re_admin_password" name="re_admin_password" value="" />
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="content-small" style="padding-bottom: 15px">
							Please re-type the new administrator password from above.
						</td>
					</tr>
				</table>
			</div>
			<div id="step_5"<?php echo ($STEP == 5 ? "" : " style=\"display: none;\""); ?>>
				<div class="display-generic">
					Lastly we need to <strong>save your configuration data</strong> to the <span style="font-family: monospace">core/config/config.inc.php</span> file and write a new <span style="font-family: monospace">.htaccess</span> file to your Entrada directory. We will try to do this for you, but if the setup tool does not have the proper permissions you will be asked to save this yourself before continuing.
				</div>
				<h2>Step 5: Save Config Data &amp; .htaccess File</h2>
				<div id="config"<?php echo (isset($display_config) && $display_config ? "" : " style=\"display: none;\"") ?>>
					<label for="config_text">
						1. <strong>Copy and paste</strong> the following text into the <span style="font-family: monospace">core/config/config.inc.php</span> file.
					</label>
					<br />
					<textarea id="config_text" name="config_text" style="width: 80%; height: 305px; font-size: 11px" onclick="this.select()" readonly="readonly"><?php
						if (isset($display_config) && $display_config) {
							echo $config_text;
						}
					?></textarea>
				</div>
				<div id="htaccess" style="margin-top: 15px;<?php echo ((isset($display_htaccess) && $display_htaccess) ? "" : " display: none;"); ?>">
					<label for="htaccess_text">
						2. <strong>Copy and paste</strong> the following text into a new file named <span style="font-family: monospace">.htaccess</span> in your Entrada root.
					</label>
					<br />
					<textarea id="htaccess_text" name="htaccess_text" style="width: 80%; height: 330px; font-size: 11px" onclick="this.select()" readonly="readonly"><?php
						if (isset($display_htaccess) && $display_htaccess) {
							$htaccess_text = file_get_contents($setup->entrada_absolute.Entrada_Setup::$HTACCESS_FILE);
							$htaccess_text = str_replace("ENTRADA_RELATIVE", $setup->entrada_relative, $htaccess_text);
							echo $htaccess_text;
						}
					?></textarea>
				</div>
				<?php
				if (!$display_htaccess && !$display_config) {
					?>
					<div class="display-success">
						<ul>
							<li>We have successfully saved your configuration information and created a new .htaccess file in your Entrada directory. We are now ready to create the MySQL database tables that Entrada needs to operate.</li>
						</ul>
					</div>
					<?php
				}
				?>
			</div>
			<div id="step_6"<?php echo ($STEP == 6 ? "" : " style=\"display: none;\""); ?>>
				<p id="success"<?php echo ($STEP != 6 || $ERROR ? " style=\"display: none;\"" : "");?>>
					You have successfully installed Entrada. You may view the site at this url: <strong><?php echo $PROCESSED["entrada_url"]; ?></strong> or by clicking the "View Site" button below. Once on the site, you may log in using the admin username and password you entered during the setup process.
				</p>
				<p id="success"<?php echo ($STEP != 6 || !$ERROR ? " style=\"display: none;\"" : "");?>>
					There was an issue while attempting to load the table information into your databases. Please ensure all three databases are completely empty before clicking the 'Refresh' button.
			</div>

			<div style="margin: 15px 25px 10px 0; padding-right: 40px; text-align: right">
				<input type="submit" value="Continue" name="continue"<?php echo ($STEP > 5 ? " style=\"display: none;\"" : "");?> />
				<input type="button" value="View Site" onclick="window.location= '<?php echo (isset($PROCESSED["entrada_url"]) && $PROCESSED["entrada_url"] ? $PROCESSED["entrada_url"] : "../.."); ?>';" name="view"<?php echo ($STEP != 6 || $ERROR ? " style=\"display: none;\"" : "");?> />
				<input type="submit" value="Refresh" name="refresh"<?php echo ($STEP != 6 || !$ERROR ? " style=\"display: none;\"" : "");?> />
			</div>
		</form>
	</div>
</body>
</html>