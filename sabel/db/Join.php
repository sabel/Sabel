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
class Sabel_DB_Join extends Sabel_Object
{
  private
    $manip    = null,
    $model    = null,
    $objects  = array(),
    $joinType = "INNER",
    $tblName  = "";

  public function __construct(Sabel_DB_Manipulator $manip)
  {
    $this->manip     = $manip;
    $this->model     = $manip->getModel();
    $this->tblName   = $this->model->getTableName();
    $this->structure = Sabel_DB_Join_Structure::getInstance();
  }

  public function getManipulator()
  {
    return $this->manip;
  }

  public function setJoinType($joinType)
  {
    $this->joinType = $joinType;
  }

  public function add($object)
  {
    if (is_string($object)) {
      $object = new Sabel_DB_Join_Object(MODEL($object));
    } elseif ($object instanceof Sabel_DB_Abstract_Model) {
      $object = new Sabel_DB_Join_Object($object);
    }

    $object->setSourceName($this->tblName);
    $this->objects[] = $object;
    $this->structure->addJoinObject($object);
    $this->structure->add($this->tblName, $object->getName());

    $name = $object->getModel()->getTableName();
    if ($fkey = $this->model->getSchema()->getForeignKey()) {
      foreach ($fkey->toArray() as $colName => $fkey) {
        if ($fkey->table === $name) {
          $joinKey = array("id" => $fkey->column, "fkey" => $colName);
          break;
        }
      }
    } else {
      $joinKey = array("id" => "id", "fkey" => $name . "_id");
    }

    $object->setJoinKey($joinKey);

    return $this;
  }

  public function setParents(array $parents)
  {
    foreach ($parents as $parent) {
      $this->add($parent);
    }

    return $this;
  }

  public function getCount($joinType = null, $clearState = true)
  {
    if ($joinType === null) {
      $joinType = $this->joinType;
    }

    $query = array();
    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($joinType);
    }

    $rows = $this->execute(array("COUNT(*) AS cnt"),
                           implode("", $query),
                           array("limit" => 1));

    if ($clearState) $this->clear();
    return (int)$rows[0]["cnt"];
  }

  public function join($joinType = null)
  {
    if ($joinType === null) {
      $joinType = $this->joinType;
    }

    $projection = array();
    foreach ($this->objects as $object) {
      $projection = array_merge($projection, $object->getProjection());
    }

    $model   = $this->model;
    $tblName = $model->getTableName();

    foreach ($model->getColumnNames() as $column) {
      $projection[] = $tblName . "." . $column;
    }

    $query = array();
    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($joinType);
    }

    if ($rows = $this->execute($projection, implode("", $query))) {
      $results = Sabel_DB_Join_Result::build($model, $this->structure, $rows);
    } else {
      $results = false;
    }

    $this->clear();
    return $results;
  }

  protected function execute($projection, $join, $constraints = null)
  {
    $manip = $this->manip;
    $sql   = $manip->getStatement(Sabel_DB_Statement::SELECT);

    $sql->join($join)
        ->projection($projection)
        ->where($manip->loadConditionManager()->build($sql));

    if ($constraints === null) $constraints = $manip->getConstraints();
    return $manip->executeSql($sql->constraints($constraints));
  }

  public function clear()
  {
    $this->structure->clear();
    Sabel_DB_Join_ColumnHash::clear();
  }
}
