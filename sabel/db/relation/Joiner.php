<?php

/**
 * Sabel_DB_Relation_joiner
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Relation_Joiner
{
  const CANNOT_JOIN = 0x00;

  protected $objects = array();

  protected $sourceModel = null;
  protected $tblName     = "";

  protected $possibleTables = array();
  protected $resultBuilder  = null;

  public function __construct($model)
  {
    $this->sourceModel   = $model;
    $this->tblName       = $model->getTableName();
    $this->resultBuilder = Sabel_DB_Relation_Join_Result::getInstance();
  }

  public function add($arg, $joinKeys = null, $columns = null, $alias = null)
  {
    $object = new Sabel_DB_Relation_Join_Object($arg, $joinKeys, $columns, $alias);
    $this->objects[] = $object;

    $builder = $this->resultBuilder;
    $builder->setObject($object);
    $builder->addStructure($this->tblName, $object->getName());

    Sabel_DB_Relation_Join_Alias::regist($this->tblName, $object);

    if ($alias !== null) {
      $name = $object->getModel()->getTableName();
      Sabel_DB_Relation_Join_Alias::change($name, $alias, $object);
    }

    return $this;
  }

  public function buildParents()
  {
    $parents = $this->sourceModel->getParents();
    $result  = $this->addParentModel($parents);

    if ($result === self::CANNOT_JOIN) {
      return self::CANNOT_JOIN;
    } else {
      return $this->objects;
    }
  }

  protected function addParentModel($parents, $join = null)
  {
    if (empty($this->possibleTables)) {
      $possibleTables = $this->getPossibleTables();
    } else {
      $possibleTables = $this->possibleTables;
    }

    foreach ($parents as $parent) {
      $model = MODEL($parent);
      $tblName = $model->getTableName();
      $parents = $model->getParents();
      if (in_array($tblName, $possibleTables)) {
        if ($join === null && empty($parents)) {
          $this->add($model);
        } elseif ($join !== null) {
          $join->add($model);
        }
      } else {
        return self::CANNOT_JOIN;
      }

      if ($parents = $model->getParents()) {
        $more = new Sabel_DB_Relation_Join($model);
        $this->addParentModel($parents, $more);
        $this->add($more);
      }
    }
  }

  protected function getPossibleTables()
  {
    $connectionName = $this->sourceModel->getConnectionName();
    $schemaName = Sabel_DB_Config::getSchemaName($connectionName);
    $accessor   = new Sabel_DB_Schema_Accessor($connectionName, $schemaName);

    return $this->possibleTables = $accessor->getTableLists();
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

    $projection = implode(", ", $cols) . ", "
                . implode(", ", $projection);

    $query = array();
    $query[] = "SELECT $projection FROM $tblName";

    foreach ($objects as $object) {
      $query[] = $object->getJoinQuery($joinType);
    }

    $rows = $this->execute($model, implode("", $query));

    if (!$rows) {
      $this->clear();
      return false;
    } else {
      $results = $this->resultBuilder->build($model, $rows);
      $this->clear();
      return $results;
    }
  }

  public function execute($model, $joinQuery)
  {
    $command = $model->getCommand();
    $driver  = $command->getDriver();
    $conditionManager = $model->loadConditionManager();

    if (!$conditionManager->isEmpty()) {
      $joinQuery .= " " . $conditionManager->build($driver);
    }

    if ($constraints = $model->getConstraints()) {
      $joinQuery = $driver->getConstraintSqlClass()->build($joinQuery, $constraints);
    }

    return $driver->setSql($joinQuery)->execute();
  }

  protected function clear()
  {
    $this->objects = array();
    Sabel_DB_Relation_Join_Result::clear();
    Sabel_DB_Relation_Join_Alias::clear();
  }
}
