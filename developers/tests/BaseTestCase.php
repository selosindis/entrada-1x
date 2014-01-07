<?php
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../../www-root/core",
    dirname(__FILE__) . "/../../www-root/core/includes",
    dirname(__FILE__) . "/../../www-root/core/library",
    get_include_path(),
)));

require_once "PHPUnit/Extensions/Database/TestCase.php";

abstract class BaseTestCase extends PHPUnit_Extensions_Database_TestCase {
    protected $db;

    public function setUp() {
        self::prevent_redundant_ADOdbLib_include();

        $ADODB_QUOTE_FIELDNAMES = true;	// Whether or not you want ADOdb to backtick field names in AutoExecute, GetInsertSQL and GetUpdateSQL.
        define("ADODB_QUOTE_FIELDNAMES", $ADODB_QUOTE_FIELDNAMES);
        $this->db = NewADOConnection(DATABASE_TYPE);
        $this->db->Connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
        $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
        global $db;
        $db = $this->db;
    }

    public function tearDown() {

    }

    public static function setUpBeforeClass() {
        require_once "Zend/Loader/Autoloader.php";
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->registerNamespace('Entrada_');
        $loader->registerNamespace('Models_');
        require_once("config/settings.inc.php");
        require_once("Entrada/adodb/adodb.inc.php");
        require_once("functions.inc.php");
     }

    public static function tearDownAfterClass() {

    }

    static function prevent_redundant_ADOdbLib_include() {
        require_once('Entrada/adodb/adodb-lib.inc.php');
        global $ADODB_INCLUDED_LIB;
        $ADODB_INCLUDED_LIB = 1;
    }

    /**
     * Required function to return a connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    final public function getConnection() {
        $config = new Zend_Config(require "config/config.inc.php");
        $dsn = "mysql:host={$config->database->host};dbname={$config->database->entrada_database}";
        $pdo = new PDO($dsn, $config->database->username, $config->database->password);

        return $this->createDefaultDBConnection($pdo);
    }

    /**
     * Required function to return a data set.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    final public function getDataSet() {
        $ds1 = $this->createMySQLXMLDataSet(dirname(__FILE__) . '/fixtures/entrada.xml');
        $ds2 = $this->createMySQLXMLDataSet(dirname(__FILE__) . '/fixtures/entrada_auth.xml');

        $composite_ds = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array());
        $composite_ds->addDataSet($ds1);
        $composite_ds->addDataSet($ds2);

        return $composite_ds;
    }
}

?>