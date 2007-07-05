<?php

/**
 * Sabel_DB_Join
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Join extends Sabel_DB_Join_Base
{
  const CANNOT_JOIN = "CANNOT_JOIN";

  protected $tableLists = array();

  public function buildParents()
  {
    $parents = $this->sourceModel->getParents();
    $result  = $this->addParentModel($parents);

    if ($result === self::CANNOT_JOIN) $this->clear();
    return $result;
  }

  protected function addParentModel($parents, $join = null)
  {
    if (empty($this->tableLists)) {
      $tableLists = $this->getTableLists();
    } else {
      $tableLists = $this->tableLists;
    }

    foreach ($parents as $parent) {
      $model   = MODEL($parent);
      $tblName = $model->getTableName();
      $parents = $model->getParents();

      if (in_array($tblName, $tableLists)) {
        if ($join === null && empty($parents)) {
          $this->add($model);
        } elseif ($join !== null) {
          $join->add($model);
        }
      } else {
        return self::CANNOT_JOIN;
      }

      if ($parents = $model->getParents()) {
        $more = new Sabel_DB_Join_Relation($model);
        $res  = $this->addParentModel($parents, $more);
        if ($res === self::CANNOT_JOIN) return self::CANNOT_JOIN;
        $this->add($more);
      }
    }

    return true;
  }

  protected function getTableLists()
  {
    $connectionName = $this->sourceModel->getConnectionName();
    $accessor = new Sabel_DB_Schema_Accessor($connectionName);

    return $this->tableLists = $accessor->getTableLists();
  }

  public function getCount($joinType = "INNER")
  {
    $query = array();
    $model = $this->sourceModel;

    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($joinType);
    }

    $query = implode("", $query);
    return $model->getCommand()->count($query)->getResult();
  }

  public function join($joinType = "INNER")
  {
    $objects = $this->objects;
    $model   = $this->sourceModel;

    $projection = array();
    foreach ($objects as $object) {
      $projection[] = $object->getProjection();
    }

    $cols = array();
    $tblName = $model->getTableName();
    $columns = $model->getColumnNames();

    foreach ($columns as $column) {
      $cols[] = $tblName . "." . $column;
    }

    $projection = implode(", ", $cols) . ", " . implode(", ", $projection);

    $query   = array();
    $query[] = "SELECT $projection FROM $tblName";

    foreach ($objects as $object) {
      $query[] = $object->getJoinQuery($joinType);
    }

    $query = implode("", $query);
    $rows  = $model->getCommand()->join($query)->getResult();

    if (!$rows) {
      $results = false;
    } else {
      $results = $this->resultBuilder->build($model, $rows);
    }

    $this->clear();
    return $results;
  }

  protected function clear()
  {
    $this->objects = array();

    Sabel_DB_Join_Result::clear();
    Sabel_DB_Join_Alias::clear();
  }
}
