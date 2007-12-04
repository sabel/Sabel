<?php

/**
 * Sabel_DB_Schema
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema
{
  private static $schemas = array();
  
  public static function getTableSchema($tblName, $connectionName = "default")
  {
    if (isset(self::$schemas[$tblName])) {
      return self::$schemas[$tblName];
    }
    
    $className = "Schema_" . convert_to_modelname($tblName);
    Sabel::using($className);
    
    if (class_exists($className, false)) {
      $cols = array();
      $schemaClass = new $className();
      foreach ($schemaClass->get() as $colName => $info) {
        $co = new Sabel_DB_Schema_Column();
        $co->name = $colName;
        foreach ($info as $key => $val) $co->$key = $val;
        $cols[$colName] = $co;
      }
      
      $tblSchema  = new Sabel_DB_Schema_Table($tblName, $cols);
      $properties = $schemaClass->getProperty();
      $tblSchema->setTableEngine($properties["tableEngine"]);
      $tblSchema->setUniques($properties["uniques"]);
      $tblSchema->setForeignKeys($properties["fkeys"]);
    } else {
      $schemaObj = Sabel_DB_Driver::createSchema($connectionName);
      $tblSchema = $schemaObj->getTable($tblName);
    }
    
    return self::$schemas[$tblName] = $tblSchema;
  }
  
  public static function getTableList($connectionName = "default")
  {
    $clsName = "Schema_" . ucfirst($connectionName) . "TableList";
    Sabel::using($clsName);
    
    if (class_exists($clsName, false)) {
      $sc = new $clsName();
      return $sc->get();
    } else {
      return Sabel_DB_Driver::createSchema()->getTableList();
    }
  }
  
  public static function clear()
  {
    $schemas = self::$schemas;
    self::$schemas = array();
    
    return $schemas;
  }
}
