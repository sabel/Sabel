<?php

/**
 * Sabel_DB_Schema_Accessor
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage schema
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Accessor
{
  private $connectName = '';
  private $schemaClass = null;

  public function __construct($connectName, $schema = null)
  {
    $dbName    = ucfirst(Sabel_DB_Connection::getDB($connectName));
    $className = 'Sabel_DB_Schema_' . $dbName;

    $this->schemaClass = new $className($connectName, $schema);
    $this->connectName = $connectName;
  }

  public function getTables()
  {
    return $this->schemaClass->getTables();
  }

  public function getTable($tblName)
  {
    return $this->schemaClass->getTable($tblName);
  }

  public function getTableNames()
  {
    $sClass = 'Schema_' . ucfirst($this->connectName) . 'TableList';

    if (class_exists($sClass, false)) {
      $sc = new $sClass();
      return $sc->get();
    } else {
      return $this->schemaClass->getTableNames();
    }
  }

  public function getColumnNames($tblName)
  {
    $sClass = get_schema_by_tablename($tblName);

    if ($sClass) {
      $cols = $sClass->get();
    } else {
      $executer = new Sabel_DB_Executer($this->connectName);
      $executer->setConstraint('limit', 1);
      $executer->getStatement()->setBasicSQL("SELECT * FROM $tblName");
      $cols = $executer->execute()->fetch();
    }
    return array_keys($cols);
  }

  /**
   *  for mysql.
   *
   */
  public function getTableEngine($tblName, $driver = null)
  {
    if (is_null($driver)) $driver = Sabel_DB_Connection::getDriver($this->connectName);
    $driver->execute("SHOW TABLE STATUS WHERE Name='{$tblName}'");
    $row = $driver->getResultSet()->fetch();
    return $row['Engine'];
  }
}
