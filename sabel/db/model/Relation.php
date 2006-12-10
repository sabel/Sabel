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
class Sabel_DB_Model_Relation
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

  private
    $objects = array();

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
        $pm = $parent;
        if (strpos($pm, ':') !== false) list($gbg, $pm) = explode(':', $pm);
        if (strpos($pm, '.') !== false) list($pm) = explode('.', $pm);
        if (in_array($pm, $this->acquiredParents)) continue;

        $condition = $this->createRelationPair($mdlName, $parent);
        $this->acquiredParents[] = $pm;
        if (!$this->isEnableJoin($pm)) return false;
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

    list($cTable) = explode('.', $child);
    list($pTable) = explode('.', $parent);

    $this->joinTablePairs[] = array($cTable, $pTable);
    $this->refStructure[$cTable][] = $pTable;

    if (!isset($this->joinConditions[$pTable])) {
      $this->joinConditions[$pTable] = $child . ' = ' . $parent;
    }
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

  public function getUniqueTables($tablePairs = null)
  {
    if ($tablePairs === null) $tablePairs = $this->joinTablePairs;

    $joinTables = array();
    foreach ($tablePairs as $pair) $joinTables = array_merge($joinTables, array_values($pair));
    return array_unique($joinTables);
  }

  public function join($model, $modelPairs, $joinType, $columns)
  {
    if (!$model instanceof Sabel_DB_Model)
      throw new Exception('Error:join() first argument must be an instance of Sabel_DB_Model.');

    foreach ($modelPairs as $pair) {
      $this->createRelationPair(get_class($model), $pair);
    }

    $colList = array();
    $tblName = $model->getTableName();
    $colList[$tblName] = $model->getColumnNames();

    $joinTables = array_diff($this->getUniqueTables(), array($tblName));

    foreach ($joinTables as $tblName) {
      $mdlName = convert_to_modelname($tblName);
      if (isset($columns[$mdlName])) {
        $colList[$tblName] = $columns[$mdlName];
      } else {
        $colList[$tblName] = $model->getColumnNames($tblName);
      }
    }

    $this->isJoin      = true;
    $this->joinColList = $colList;

    return $this->execJoin($model, $joinType, $joinTables);
  }

  public function execJoin($model, $joinType = 'INNER', $joinTables = null)
  {
    if (!$model instanceof Sabel_DB_Model)
      throw new Exception('Error:execJoin() first argument must be an instance of Sabel_DB_Model.');

    if (!$this->isJoin)
      throw new Exception('Error: join flag is not active. confirm it by initJoin() ?');

    $sql        = array('SELECT ');
    $tablePairs = $this->joinTablePairs;
    $colList    = $this->joinColList;
    $myTable    = $model->getTableName();

    foreach ($colList[$myTable] as $column) $sql[] = "{$myTable}.{$column}, ";

    if (!$joinTables) {
      $joinTables = array_diff($this->getUniqueTables(), (array)$myTable);
    }

    foreach ($joinTables as $tblName) {
      foreach ($colList[$tblName] as $column) {
        $this->joinColCache[$tblName][] = $column;
        $sql[] = "{$tblName}.{$column} AS pre_{$tblName}_{$column}, ";
      }
    }

    $sql   = array(substr(join('', $sql), 0, -2));
    $sql[] = " FROM {$myTable}";

    foreach ($this->joinConditions as $parent => $condition) {
      $sql[] = " $joinType JOIN $parent ON $condition";
    }

    $model->getStatement()->setBasicSQL(join('', $sql));
    $resultSet = $model->find();
    if ($resultSet->isEmpty()) return false;

    $results = array();
    $obj     = MODEL(convert_to_modelname($myTable));
    $rows    = $resultSet->fetchAll();

    foreach ($rows as $row) {
      $models = $this->makeEachModels($row, $joinTables);

      $ref = $this->refStructure;
      foreach ($joinTables as $tblName) {
        if (!isset($ref[$tblName])) continue;
        foreach ($ref[$tblName] as $parent) {
          $mdlName = convert_to_modelname($parent);
          $models[$tblName]->dataSet($mdlName, $models[$parent]);
        }
      }

      $self = clone $obj;
      $self->setData($row);

      foreach ($ref[$myTable] as $parent) {
        $mdlName = convert_to_modelname($parent);
        $self->dataSet($mdlName, $models[$parent]);
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
    $objects  = $this->createObjects($joinTables);

    foreach ($joinTables as $tblName) {
      $model  = clone $objects[$tblName];
      $preCol = "pre_{$tblName}_" . $model->getPrimaryKey();

      foreach ($colCache[$tblName] as $column) {
        $preCol = "pre_{$tblName}_{$column}";
        $acquire[$tblName][$column] = $row[$preCol];
        unset($row[$preCol]);
      }
      $model->setData($acquire[$tblName]);
      $models[$tblName] = $model;
    }
    return $models;
  }

  protected function createObjects($tblNames)
  {
    $objects =& $this->objects;
    if ($objects) return $objects;

    foreach ($tblNames as $tblName) {
      $objects[$tblName] = MODEL(convert_to_modelname($tblName));
    }
    return $objects;
  }
}
