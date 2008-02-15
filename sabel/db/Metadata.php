<?php

/**
 * Sabel_DB_Metadata
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Metadata
{
  private static $metadata = array();
  
  public static function getTableInfo($tblName, $connectionName = "default")
  {
    if (isset(self::$metadata[$tblName])) {
      return self::$metadata[$tblName];
    }
    
    $className = "Schema_" . convert_to_modelname($tblName);
    Sabel::using($className);
    
    if (class_exists($className, false)) {
      $cols = array();
      $schemaClass = new $className();
      foreach ($schemaClass->get() as $colName => $info) {
        $co = new Sabel_DB_Metadata_Column();
        $co->name = $colName;
        foreach ($info as $key => $val) $co->$key = $val;
        $cols[$colName] = $co;
      }
      
      $tblSchema  = new Sabel_DB_Metadata_Table($tblName, $cols);
      $properties = $schemaClass->getProperty();
      $tblSchema->setTableEngine($properties["tableEngine"]);
      $tblSchema->setUniques($properties["uniques"]);
      $tblSchema->setForeignKeys($properties["fkeys"]);
    } else {
      $schemaObj = Sabel_DB::createMetadata($connectionName);
      $tblSchema = $schemaObj->getTable($tblName);
    }
    
    return self::$metadata[$tblName] = $tblSchema;
  }
  
  public static function getTableList($connectionName = "default")
  {
    $clsName = "Schema_" . ucfirst($connectionName) . "TableList";
    Sabel::using($clsName);
    
    if (class_exists($clsName, false)) {
      $sc = new $clsName();
      return $sc->get();
    } else {
      return Sabel_DB::createMetadata($connectionName)->getTableList();
    }
  }
  
  public static function clear()
  {
    $metadata = self::$metadata;
    self::$metadata = array();
    
    return $metadata;
  }
}
