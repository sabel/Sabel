<?php

/**
 * Sabel_DB_Schema_Loader
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Loader
{
  protected static $schemas = array();

  public static function getSchema($model)
  {
    $tblName = $model->getTableName();

    if (isset(self::$schemas[$tblName])) {
      return self::$schemas[$tblName];
    }

    $className = "Schema_" . convert_to_modelname($tblName);

    if (class_exists($className, true)) {
      $cols = array();
      $schemaClass = new $className();
      foreach ($schemaClass->get() as $colName => $info) {
        $info["name"] = $colName;
        $cols[$colName] = (object)$info;
      }

      $tblSchema  = new Sabel_DB_Schema_Table($tblName, $cols);
      $properties = $schemaClass->getProperty();
      $primary    = $properties["primaryKey"];
      $increment  = $properties["incrementKey"];
      $engine     = $properties["tableEngine"];
    } else {
      $conName   = $model->getConnectionName();
      $scmName   = Sabel_DB_Config::getSchemaName($conName);
      $database  = Sabel_DB_Config::getDB($conName);
      $accessor  = new Sabel_DB_Schema_Accessor($conName, $scmName);
      $engine    = ($database === "mysql") ? $accessor->getTableEngine($tblName) : null;
      $tblSchema = $accessor->get($tblName);

      $columns   = $tblSchema->getColumns();
      $primary   = self::getPrimaryKey($columns);
      $increment = self::getIncrementColumn($columns);
    }

    $tblSchema->setPrimaryKey($primary);
    $tblSchema->setIncrementColumn($increment);
    $tblSchema->setTableEngine($engine);

    return self::$schemas[$tblName] = $tblSchema;
  }

  public static function clear()
  {
    $schemas = self::$schemas;
    self::$schemas = array();

    return $schemas;
  }

  protected static function getPrimaryKey($columns)
  {
    $pKey = array();
    foreach ($columns as $column) {
      if ($column->primary) $pKey[] = $column->name;
    }

    if (empty($pKey)) return null;
    return (sizeof($pKey) === 1) ? $pKey[0] : $pKey;
  }

  protected static function getIncrementColumn($columns)
  {
    foreach ($columns as $column) {
      if ($column->increment) return $column->name;
    }

    return null;
  }
}
