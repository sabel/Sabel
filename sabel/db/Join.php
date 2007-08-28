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
class Sabel_DB_Join
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
    if ($object instanceof Sabel_DB_Abstract_Model) {
      $object = new Sabel_DB_Join_Object($object);
    }

    $object->setSourceName($this->tblName);
    $this->objects[] = $object;
    $this->structure->addJoinObject($object);
    $this->structure->add($this->tblName, $object->getName());

    $name  = $object->getModel()->getTableName();
    $fkeys = $this->model->getSchema()->getForeignKeys();

    if (is_array($fkeys)) {
      foreach ($fkeys as $colName => $fkey) {
        if ($fkey["referenced_table"] === $name) {
          $joinKey = array("id" => $fkey["referenced_column"], "fkey" => $colName);
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
      $this->add(MODEL($parent));
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

    $rows = $this->execute("COUNT(*) AS cnt", implode("", $query), array("limit" => 1));
    if ($clearState) $this->clear();

    return $rows[0]["cnt"];
  }

  public function join($joinType = null)
  {
    if ($joinType === null) {
      $joinType = $this->joinType;
    }

    $projection = array();
    foreach ($this->objects as $object) {
      $projection[] = $object->getProjection();
    }

    $cols    = array();
    $model   = $this->model;
    $tblName = $model->getTableName();

    foreach ($model->getColumnNames() as $column) {
      $cols[] = $tblName . "." . $column;
    }

    $projection = implode(", ", $cols) . ", " . implode(", ", $projection);

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
    $stmt  = Sabel_DB_Statement::create($this->model, Sabel_DB_Statement::SELECT);
    $stmt->table($this->model->getTableName());
    $stmt->join($join);
    $stmt->projection($projection);
    $stmt->where($manip->loadConditionManager()->build($stmt));

    if ($constraints === null) {
      $stmt->constraints($manip->getConstraints());
    } else {
      $stmt->constraints($constraints);
    }

    return $manip->executeStatement($stmt);
  }

  public function clear()
  {
    $this->structure->clear();
    Sabel_DB_Join_ColumnHash::clear();
  }
}
