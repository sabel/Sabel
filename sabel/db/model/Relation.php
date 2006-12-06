<?php

/**
 * Sabel_DB_Model_Relation
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage model
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Model_Relation extends Sabel_DB_Model
{
  private
    $isJoin = false;

  private
    $joinTablePairs  = array(),
    $joinColList     = array(),
    $joinConditions  = array(),
    $refStructure    = array(),
    $joinColCache    = array(),
    $joinConNames    = array(),
    $acquiredParents = array();

  public function __construct() {}

  public function initJoin($mdlName)
  {
    $result = $this->isEnableJoin($mdlName);
    return $this->isJoin = $result;
  }

  protected function isEnableJoin($mdlName)
  {
    $sClsName = 'Schema_' . $mdlName;
    Sabel::using($sClsName);

    if (class_exists($sClsName, false)) {
      $sClass = new $sClsName();
      $props  = $sClass->getProperty();
      if (!$this->isSameConnectName($props['connectName'])) return false;
    } else {
      return false;
    }

    $tblName = convert_to_tablename($mdlName);
    $this->joinColList[$tblName] = array_keys($sClass->get());
    if ($parents = $sClass->getParents()) {
      foreach ($parents as $parent) {
        $condition = $this->createRelationPair($mdlName, $parent);

        if (strpos($parent, ':') !== false) list($gbg, $parent) = explode(':', $parent);
        if (strpos($parent, '.') !== false) list($parent) = explode('.', $parent);

        $pTable = convert_to_tablename($parent);
        $this->joinConditions[$pTable] = $condition;
        if (in_array($parent, $this->acquiredParents)) continue;

        $this->acquiredParents[] = $parent;
        if (!$this->isEnableJoin($parent)) return false;
      }
    }
    return true;
  }

  protected function isSameConnectName($conName)
  {
    if (($size = sizeof($this->joinConNames)) > 0) {
      if ($this->joinConNames[$size - 1] !== $conName) return false;
    }
    $this->joinConNames[] = $conName;
    return true;
  }

  public function createRelationPair($mdlName, $pair)
  {
    if (strpos($pair, ':') === false) {
      $pair  = $mdlName . ':' . $pair;
    }

    list($child, $parent) = explode(':', $pair);

    $child  = $this->createChildKey($child, $parent);
    $parent = $this->createParentKey($parent);

    $cTable = $this->getTableNameFromKey($child);
    $pTable = $this->getTableNameFromKey($parent);

    $this->joinTablePairs[] = array($cTable, $pTable);
    $this->refStructure[$cTable][] = $pTable;

    return $child . ' = ' . $parent;
  }

  public function createChildKey($child, $parent)
  {
    if (strpos($child, '.') === false) {
      $child = $child . '.' . convert_to_tablename($parent) . '_id';
    }

    list($c, $key) = explode('.', $child);
    return convert_to_tablename($c) . '.' . $key;
  }

  public function createParentKey($parent)
  {
    if (strpos($parent, '.') === false) {
      $parent = $parent. '.id';
    }

    list($p, $key) = explode('.', $parent);
    return convert_to_tablename($p) . '.' . $key;
  }

  protected function getTableNameFromKey($key)
  {
    list($tblName) = explode('.', $key);
    return $tblName;
  }

  public function join($self, $joinType = 'INNER')
  {
    $sql        = array('SELECT ');
    $joinTables = array();
    $tablePairs = $this->joinTablePairs;
    $colList    = $this->joinColList;
    $myTable    = $self->tableProp->table;

    foreach ($colList[$myTable] as $column) $sql[] = "{$myTable}.{$column}, ";

    foreach ($tablePairs as $pair) $joinTables = array_merge($joinTables, array_values($pair));
    $joinTables = array_diff(array_unique($joinTables), (array)$myTable);

    foreach ($joinTables as $tblName) {
      foreach ($colList[$tblName] as $column) {
        $this->joinColCache[$tblName][] = $column;
        $sql[] = "{$tblName}.{$column} AS pre_{$tblName}_{$column}, ";
      }
    }

    $sql   = array(substr(join('', $sql), 0, -2));
    $sql[] = " FROM {$myTable}";

    $acquired = array();
    foreach ($tablePairs as $pair) {
      list($child, $parent) = array_values($pair);
      if (!in_array($parent, $acquired)) {
        $cond  = $this->joinConditions[$parent];
        $sql[] = " $joinType JOIN $parent ON $cond";
        $acquired[] = $parent;
      }
    }

    $self->getStatement()->setBasicSQL(join('', $sql));
    $resultSet = $self->exec();
    if ($resultSet->isEmpty()) return false;

    $results = array();
    foreach ($resultSet as $row) {
      $models = $this->makeEachModels($row, $joinTables);

      $model = $this->newClass($myTable);
      $self->setData($model, $row);
      $models[$myTable] = $model;

      $ref = $this->refStructure;
      foreach ($joinTables as $tblName) {
        if (!isset($ref[$tblName])) continue;
        foreach ($ref[$tblName] as $parent) {
          $mdlName = convert_to_modelname($parent);
          $models[$tblName]->dataSet($mdlName, $models[$parent]);
        }
      }

      foreach ($ref[$myTable] as $parent) {
        $mdlName = convert_to_modelname($parent);
        $self->dataSet($mdlName, $models[$parent]);
        $self->$mdlName = $models[$parent];
      }
      $results[] = $self;
    }
    return $results;
  }

  protected function makeEachModels($row, $joinTables)
  {
    $models   = array();
    $acquire  = array();
    $colCache = $this->joinColCache;

    foreach ($joinTables as $tblName) {
      $model  = $this->newClass($tblName);
      //$pKey   = $model->tableProp->primaryKey;
      $preCol = "pre_{$tblName}_{$model->tableProp->primaryKey}";
      //$preCol = "pre_{$tblName}_{$pKey}";
      //$cache  = Sabel_DB_SimpleCache::get($tblName . $row[$preCol]);

      //if (is_object($cache)) {
      //  $models[$tblName] = clone($cache);
      //} else {
        foreach ($colCache[$tblName] as $column) {
          $preCol = "pre_{$tblName}_{$column}";
          $acquire[$tblName][$column] = $row[$preCol];
          unset($row[$preCol]);
        }
        $this->setData($model, $acquire[$tblName]);
        $models[$tblName] = $model;
      //  Sabel_DB_SimpleCache::add($tblName . $model->$pKey, $model);
      //}
    }
    return $models;
  }
}
