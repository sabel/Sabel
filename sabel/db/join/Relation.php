<?php

/**
 * Sabel_DB_Join_Relation
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Join_Relation extends Sabel_DB_Join_TemplateMethod
{
  protected $objects = array();

  public function add($object, $alias = "", $joinKey = array())
  {
    if (is_string($object)) {
      $object = new Sabel_DB_Join_Object(MODEL($object), $alias, $joinKey);
    } elseif ($object instanceof Sabel_DB_Abstract_Model) {
      $object = new Sabel_DB_Join_Object($object, $alias, $joinKey);
    }

    $structure = Sabel_DB_Join_Structure::getInstance();
    $structure->addJoinObject($object);
    $myName = $this->getName();
    $object->setSourceName($myName);
    $this->objects[] = $object;

    $structure->add($myName, $object->getName());
    if (!empty($joinKey)) return $this;

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

  public function getProjection()
  {
    $projection = array();
    $name = ($this->hasAlias()) ? strtolower($this->aliasName) : $this->getName(false);

    foreach ($this->columns as $column) {
      $hash = Sabel_DB_Join_ColumnHash::toHash("pre_{$name}_{$column}");
      $projection[] = $name . '.' . $column . ' AS "' . $hash . '"';
    }

    foreach ($this->objects as $object) {
      $projection = array_merge($projection, $object->getProjection());
    }

    return $projection;
  }

  public function getJoinQuery($joinType)
  {
    $name  = $this->tblName;
    $keys  = $this->joinKey;
    $query = array(" $joinType JOIN $name ");

    if ($this->hasAlias()) {
      $name = strtolower($this->aliasName);
      $query[] = $name . " ";
    }

    $lower   = strtolower($this->sourceName);
    $query[] = "ON {$lower}.{$keys["fkey"]} = {$name}.{$keys["id"]} ";

    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($joinType);
    }

    return implode("", $query);
  }
}
