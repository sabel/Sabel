<?php

/**
 * Sabel_DB_Schema_Accessor
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Accessor extends Sabel_Object
{
  private $connectionName = "";
  private $schemaClass = null;

  public function __construct($connectionName = "default")
  {
    $dbName     = Sabel_DB_Config::getDB($connectionName);
    $className  = "Sabel_DB_" . ucfirst($dbName) . "_Schema";
    $schemaName = Sabel_DB_Config::getSchemaName($connectionName);

    $this->schemaClass = new $className($connectionName, $schemaName);
    $this->connectionName = $connectionName;
  }

  public function get($tblName)
  {
    return $this->schemaClass->getTable($tblName);
  }

  public function getAll()
  {
    return $this->schemaClass->getAll();
  }

  public function getTableList()
  {
    $sClass = "Schema_{$this->connectionName}TableList";

    if (class_exists($sClass, true)) {
      $sc = new $sClass();
      return $sc->get();
    } else {
      return $this->schemaClass->getTableList();
    }
  }

  public function getColumnNames($tblName)
  {
    $sClsName = "Schema_" . convert_to_modelname($tblName);

    if (class_exists($sClsName, true)) {
      $sClass = new $sClsName();
      $cols   = $sClass->get();
    } else {
      $cols = $this->get($tblName)->getColumns();
    }

    return array_keys($cols);
  }

  public function getTableEngine($tblName)
  {
    return $this->schemaClass->getTableEngine($tblName);
  }
}
