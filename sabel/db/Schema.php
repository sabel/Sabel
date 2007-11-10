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

  public static function create($tblName, $connectionName)
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
      $accessor  = new Sabel_DB_Schema_Accessor($connectionName);
      $tblSchema = $accessor->get($tblName);
    }

    return self::$schemas[$tblName] = $tblSchema;
  }

  public static function clear()
  {
    $schemas = self::$schemas;
    self::$schemas = array();

    return $schemas;
  }
}
