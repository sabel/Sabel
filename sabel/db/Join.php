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
  const CANNOT_JOIN = "CANNOT_JOIN";

  private
    $executer = null,
    $model    = null,
    $objects  = array(),
    $tblName  = "";

  public function __construct(Sabel_DB_Model_Manipulator $executer)
  {
    $this->executer  = $executer;
    $this->model     = $executer->getModel();
    $this->tblName   = $this->model->getTableName();
    $this->structure = Sabel_DB_Join_Structure::getInstance();
  }

  public function add($object)
  {
    if ($object instanceof Sabel_DB_Model) {
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

  public function buildParents()
  {
    $parents = $this->executer->getParents();
    $connectionName = $this->model->getConnectionName();
    $accessor = new Sabel_DB_Schema_Accessor($connectionName);
    $tableLists = $accessor->getTableLists();

    $result = true;

    foreach ($parents as $parent) {
      $model = MODEL($parent);
      if (in_array($model->getTableName(), $tableLists)) {
        $this->add(new Sabel_DB_Join_Object($model));
      } else {
        $result = self::CANNOT_JOIN;
        break;
      }
    }

    if ($result === self::CANNOT_JOIN) $this->clear();

    return $result;
  }

  public function getCount($joinType = "INNER")
  {
    $query = array();
    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($joinType);
    }

    $rows = $this->execute("COUNT(*) AS cnt", implode("", $query));
    $this->clear();

    return $rows[0]["cnt"];
  }

  public function join($joinType = "INNER")
  {
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

  protected function execute($projection, $join)
  {
    $executer = $this->executer;
    $stmt = Sabel_DB_Statement::create($this->model, Sabel_DB_Statement::SELECT);
    $stmt->table($this->model->getTableName());
    $stmt->join($join);
    $stmt->projection($projection);
    $stmt->where($executer->loadConditionManager()->build($stmt));
    $stmt->constraints($executer->getConstraints());

    return $executer->executeStatement($stmt);
  }

  public function clear()
  {
    $this->objects = array();
    $this->structure->clear();

    Sabel_DB_Join_ColumnHash::clear();
  }
}
