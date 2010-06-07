<?php

/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * This file represents Entrada_Setup class. 
 *
 * @author Organisation: University of Calgary
 * @author Unit: Faculty of Medicine
 * @author Developer: Ilya Sorokin <isorokin@ucalgary.ca>
 * @copyright Copyright 2008 University of Calgary. All Rights Reserved.
 *
 * @version $Id$
*/
class Entrada_Setup
{	
	public static $SQL_DUMP_ENTRADA = "install/sql/entrada.sql";
	public static $SQL_DUMP_AUTH = "install/sql/entrada_auth.sql";
	public static $SQL_DUMP_CLERKSHIP = "install/sql/entrada_clerkship.sql";
	public static $HTACCESS_FILE = "/setup/install/dist-htaccess.txt";
	
	public $entrada_url;
	public $entrada_relative;
	public $entrada_absolute;
	public $entrada_storage;
	
	public $database_host;
	public $database_username;
	public $database_password;
	public $entrada_database;
	
	public $auth_database;

	public $clerkship_database;
	
	public $admin_username;
	public $admin_password_hash;
	
	public $admin_firstname;
	public $admin_lastname;
	public $admin_email;
	
	public $config_file_path;

	public function __construct($processed_array)
	{
		$this->entrada_url = (isset($processed_array["entrada_url"]) ? $processed_array["entrada_url"] : "");
		$this->entrada_relative = (isset($processed_array["entrada_relative"]) ? $processed_array["entrada_relative"] : "");
		$this->entrada_absolute = (isset($processed_array["entrada_absolute"]) ? $processed_array["entrada_absolute"] : "");
		$this->entrada_storage = (isset($processed_array["entrada_storage"]) ? $processed_array["entrada_storage"] : "");
		
		$this->database_host = (isset($processed_array["database_host"]) ? $processed_array["database_host"] : "");
		$this->database_username = (isset($processed_array["database_username"]) ? $processed_array["database_username"] : "");
		$this->database_password = (isset($processed_array["database_password"]) ? $processed_array["database_password"] : "");
		$this->entrada_database = (isset($processed_array["entrada_database"]) ? $processed_array["entrada_database"] : "");
		$this->auth_database = (isset($processed_array["auth_database"]) ? $processed_array["auth_database"] : "");
		$this->clerkship_database = (isset($processed_array["clerkship_database"]) ? $processed_array["clerkship_database"] : "");
		
		$this->admin_username = (isset($processed_array["admin_username"]) ? $processed_array["admin_username"] : "");
		$this->admin_password_hash = (isset($processed_array["admin_password_hash"]) ? $processed_array["admin_password_hash"] : "");
		
		$this->admin_firstname = (isset($processed_array["admin_firstname"]) ? $processed_array["admin_firstname"] : "");
		$this->admin_lastname = (isset($processed_array["admin_lastname"]) ? $processed_array["admin_lastname"] : "");
		$this->admin_email = (isset($processed_array["admin_email"]) ? $processed_array["admin_email"] : "");
		
		$this->config_file_path = $this->entrada_absolute . "/core/config/config.inc.php";
	}

	public function checkEntradaDBConnection()
	{
		try
		{
			$db = NewADOConnection(DATABASE_TYPE);
			return @$db->Connect($this->database_host, $this->database_username, $this->database_password, $this->entrada_database);
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	public function checkAuthDBConnection()
	{
		try
		{
			$db = NewADOConnection(DATABASE_TYPE);
			return @$db->Connect($this->database_host, $this->database_username, $this->database_password, $this->auth_database);
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	public function checkClerkshipDBConnection() {
		try {
			$db = NewADOConnection(DATABASE_TYPE);
			return @$db->Connect($this->database_host, $this->database_username, $this->database_password, $this->clerkship_database);
		} catch (Exception $e) {
			return false;
		}
	}
		
	public function writeHTAccess() {
		try {
			$htaccess_text = @file_get_contents($this->entrada_absolute.self::$HTACCESS_FILE);
			$htaccess_text = str_replace("ENTRADA_RELATIVE", $this->entrada_relative, $htaccess_text);
	
			if (!@file_put_contents($this->entrada_absolute."/.htaccess", $htaccess_text)) {
				return false;
			}

			if (!@file_exists($this->entrada_absolute."/.htaccess")) {
				return false;
			}
		} catch (Exception $e) {
			return false;
		}
		
		return true;
	}
    
	public function writeConfigData() {
		try
		{
			$configArray = array(
				"entrada_url"  => $this->entrada_url,
				"entrada_relative"  => $this->entrada_relative,
				"entrada_absolute" => $this->entrada_absolute,
				"entrada_storage" => $this->entrada_storage,
				"database" => array(
					"host"						=> $this->database_host,
					"username"					=> $this->database_username,
					"password"					=> $this->database_password,
					"entrada_database"			=> $this->entrada_database,
					"auth_database"				=> $this->auth_database,
					"clerkship_database"		=> $this->clerkship_database,
					),
					"admin" => array(
						"firstname"					=> $this->admin_firstname,
						"lastname"					=> $this->admin_lastname,
						"email"						=> $this->admin_email,
					)
			);
			$config = new Zend_Config($configArray);
			$writer = new Zend_Config_Writer_Array();
			$writer->write($this->config_file_path, $config);
		}
		catch(Zend_Config_Exception $e)
		{
			return false;
		}
		
		return true;
	}

	public function outputConfigData() {
$config_text = <<<CONFIGTEXT
<?php
return array (
  'entrada_url' => '{$this->entrada_url}',
  'entrada_relative' => '{$this->entrada_relative}',
  'entrada_absolute' => '{$this->entrada_absolute}',
  'entrada_storage' => '{$this->entrada_storage}',
  'database' =>
  array (
    'host' => '{$this->database_host}',
    'username' => '{$this->database_username}',
    'password' => '{$this->database_password}',
    'entrada_database' => '{$this->entrada_database}',
    'auth_database' => '{$this->auth_database}',
    'clerkship_database' => '{$this->clerkship_database}',
  ),
  'admin' =>
  array (
    'firstname' => '{$this->admin_firstname}',
    'lastname' => '{$this->entrada_relative}',
    'email' => '{$this->admin_email}',
  ),
);
CONFIGTEXT;
		return $config_text;
	}

	public function configFileExists()
	{
		if (!@file_exists($this->config_file_path))
		{
			return false;
		}
		
		return true;
	}

	public function loadDumpData()
	{
		$db_dump_files = array(
			$this->entrada_database => self::$SQL_DUMP_ENTRADA,
			$this->auth_database => self::$SQL_DUMP_AUTH,
			$this->clerkship_database => self::$SQL_DUMP_CLERKSHIP
		);
		try
		{
			foreach($db_dump_files as $database_name => $dump_file)
			{
				$db = NewADOConnection(DATABASE_TYPE);
				$db->Connect($this->database_host, $this->database_username, $this->database_password, $database_name);
				$queries = $this->parseDatabaseDump($dump_file);
				foreach ($queries as $query)
				{
					$db->Execute($query);
				}
			}
		}
		catch(Exception $e)
		{
			return false;
		}
		return true;
	}

	/**
	 * Returns array of queries from dump file
	 * @todo check dump file existence before parsing
	 *
	 * @param  $dump_file Path to dump file
	 * @return array
	 */
	public function parseDatabaseDump($dump_file)
	{
		$handle = @fopen($dump_file, "r");
		
		$sql_dump = array();
		$query = "";

		if ($handle = @fopen($dump_file, "r")) do {
			$sql_line = fgets($handle);
			if ((trim($sql_line) != "") && (strpos($sql_line, "--") === false)) {
				$query .= $sql_line;
				if (preg_match("/;[\040]*\$/", $sql_line)) {
					$query = str_replace(array("%ADMIN_FIRSTNAME%", "%ADMIN_LASTNAME%", "%ADMIN_EMAIL%", "%ADMIN_USERNAME%", "%ADMIN_PASSWORD_HASH%", ), array($this->admin_firstname, $this->admin_lastname, $this->admin_email, $this->admin_username, $this->admin_password_hash), $query);
					$sql_dump[] = $query;
					$query = "";
				}
			}
		} while (!@feof($handle));
		
		return $sql_dump;
	}
}