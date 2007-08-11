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

  protected $executer = null;

  public function __construct($executer)
  {
    $model = $executer->getModel();

    $this->executer      = $executer;
    $this->sourceModel   = $executer->getModel();
    $this->tblName       = $this->sourceModel->getTableName();
    $this->resultBuilder = Sabel_DB_Join_Result::getInstance();
  }

  public function buildParents()
  {
    $parents = $this->executer->getParents();
    $connectionName = $this->sourceModel->getConnectionName();
    $accessor = new Sabel_DB_Schema_Accessor($connectionName);
    $tableLists = $accessor->getTableLists();

    $result = true;

    foreach ($parents as $parent) {
      $model   = MODEL($parent);
      $tblName = $model->getTableName();

      if (in_array($tblName, $tableLists)) {
        $this->add($model);
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
    $model = $this->sourceModel;
    $query = array("SELECT COUNT(*) AS cnt FROM " . $model->getTableName());

    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($joinType);
    }

    $rows = $this->execute(implode("", $query));
    $this->clear();

    return $rows[0]["cnt"];
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

    if (!$rows = $this->execute(implode("", $query))) {
      $results = false;
    } else {
      $results = $this->resultBuilder->build($model, $rows);
    }

    $this->clear();
    return $results;
  }

  protected function execute($query)
  {
    $executer = $this->executer;
    $stmt = Sabel_DB_Statement::createSelectStatement($executer, $query);
    return $executer->query($stmt->getSql(), true)->execute();
  }

  public function clear()
  {
    $this->objects = array();

    Sabel_DB_Join_Result::clear();
    Sabel_DB_Join_Alias::clear();
  }
}
