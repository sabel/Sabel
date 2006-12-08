<?php

Sabel::using('Sabel_DB_Schema_Interface');

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
class Sabel_DB_Schema_Accessor implements Sabel_DB_Schema_Interface
{
  private $connectName = '';
  private $schemaClass = null;

  public function __construct($connectName, $schema = null)
  {
    $dbName    = ucfirst(Sabel_DB_Connection::getDB($connectName));
    $className = 'Sabel_DB_' . $dbName . '_Schema';

    Sabel::using($className);
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
    $sClsName = 'Schema_' . convert_to_modelname($tblName);
    Sabel::using($sClsName);

    if (class_exists($sClsName, false)) {
      $sClass = new $sClsName();
      $cols   = $sClass->get();
    } else {
      $arg = array('table' => $tblName, 'connectName' => $this->connectName);
      $executer = new Sabel_DB_Executer($arg);
      $executer->setConstraint('limit', 1);
      $executer->getStatement()->setBasicSQL("SELECT * FROM $tblName");
      $cols = $executer->exec()->fetch();
    }
    return array_keys($cols);
  }

  public function getTableEngine($tblName)
  {
    return $this->schemaClass->getTableEngine($tblName);
  }
}
