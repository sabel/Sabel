<?php

/**
 * Sabel_DB_Join_Result
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Join_Result
{
  protected static $ins = null;

  protected $objects   = array();
  protected $structure = array();

  private function __construct() {}

  public static function getInstance()
  {
    if (self::$ins === null) {
      self::$ins = new self();
    }

    return self::$ins;
  }

  public function addStructure($source, $table)
  {
    $this->structure[$source][] = $table;
  }

  public function changeKeyOfStructure($key, $newKey)
  {
    $structure =& $this->structure;

    if (isset($structure[$key])) {
      $structure[$newKey] = $structure[$key];
      unset($structure[$key]);
    }
  }

  public function setObject($object)
  {
    $this->objects[$object->getName()] = $object;
  }

  public static function clear()
  {
    self::$ins = null;
  }

  protected function getUniqueTables()
  {
    $tables = array();
    $structure = $this->structure;

    foreach ($structure as $joinTables) {
      $tables = array_merge($tables, $joinTables);
    }

    return array_unique($tables);
  }

  public function build($source, $rows)
  {
    $tables = $this->getUniqueTables();
    $temporary = $this->structure;

    $structure = array();
    foreach ($temporary as $sourceTable => $parents) {
      $structure[$sourceTable] = array_unique($parents);
    }

    $results = array();
    foreach ($rows as $row) {
      $models = $this->createModels($row, $tables);

      foreach ($tables as $tblName) {
        if (!isset($structure[$tblName])) continue;
        foreach ($structure[$tblName] as $parent) {
          $name = convert_to_modelname($parent);
          $models[$tblName]->$name = $models[$parent];
        }
      }

      $tblName = $source->getTableName();
      $self = MODEL(convert_to_modelname($tblName));
      $self->setProperties($row);

      foreach ($structure[$tblName] as $parent) {
        $name = convert_to_modelname($parent);
        $self->$name = $models[$parent];
      }

      $results[] = $self;
    }

    return $results;
  }

  protected function createModels($row, $tables)
  {
    $models = array();

    $objects = $this->objects;
    foreach ($tables as $tblName) {
      $object = $objects[$tblName];
      $model  = $object->createModel($row);
      $models[$tblName] = $model;
    }

    return $models;
  }
}
