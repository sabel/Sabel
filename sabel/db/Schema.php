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
  
  public static function getTableInfo($tblName, $connectionName = "default")
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
    
    self::setMaxmin($tblSchema);
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
      return Sabel_DB_Driver::createSchema($connectionName)->getTableList();
    }
  }
  
  public static function clear()
  {
    $schemas = self::$schemas;
    self::$schemas = array();
    
    return $schemas;
  }
  
  private static function setMaxmin($tblSchema)
  {
    if (class_exists("ColumnMaxmin", false)) {
      $tblName = $tblSchema->getTableName();
      $maxmin  = ColumnMaxmin::create();
      if (!$maxmin->hasMethod($tblName)) return;
      
      if (($columns = $maxmin->$tblName()) !== null) {
        foreach ($columns as $name => $param) {
          if (isset($param["min"]) && $tblSchema->hasColumn($name)) {
            $tblSchema->$name->min = $param["min"];
          }
          if (isset($param["max"]) && $tblSchema->hasColumn($name)) {
            $tblSchema->$name->max = $param["max"];
          }
        }
      }
    }
  }
}
