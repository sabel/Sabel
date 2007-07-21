<?php

/**
 * Sabel_DB_Join_Object
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Join_Object
{
  protected
    $model       = null,
    $isModel     = false,
    $objects     = array(),
    $columns     = array(),
    $joinKeys    = array(),
    $tblName     = "",
    $aliasName   = null,
    $sourceAlias = "";

  public function __construct($object, $fkeys, $joinKeys, $columns = null, $alias = null)
  {
    if ($object instanceof Sabel_DB_Join_Relation) {
      $model = $this->model = $object->getSourceModel();
      $this->objects = $object->getObjects();
    } elseif ($object instanceof Sabel_DB_Model) {
      $this->isModel = true;
      $model = $this->model = $object;
    } else {
      throw new Exception("invalid object type.");
    }

    if ($alias !== null) $this->aliasName = $alias;
    $tblName = $this->tblName = $model->getTableName();

    if ($columns === null) {
      $this->columns = $model->getColumnNames();
    } else {
      $this->columns = $columns;
    }

    if (!is_array($fkeys) && $joinKeys === null) {
      $this->joinKeys = array("id" => "id", "fkey" => $tblName . "_id");
    } elseif ($joinKeys !== null) {
      $this->joinKeys = $joinKeys;
    } elseif (is_array($fkeys)) {
      foreach ($fkeys as $colName => $fkey) {
        if ($fkey["referenced_table"] === $tblName) {
          $this->joinKeys = array("id" => $fkey["referenced_column"], "fkey" => $colName);
          break;
        }
      }
    }
  }

  public function isModel()
  {
    return $this->isModel;
  }

  public function getModel()
  {
    return $this->model;
  }

  public function getName($alias = true)
  {
    if ($alias && $this->hasAlias()) {
      return $this->getAlias();
    } else {
      return $this->model->getTableName();
    }
  }

  public function hasAlias()
  {
    return ($this->aliasName !== null);
  }

  public function getAlias()
  {
    return $this->aliasName;
  }

  public function getColumns()
  {
    return $this->columns;
  }

  public function getJoinKeys()
  {
    return $this->joinKeys;
  }

  public function setSourceName($alias)
  {
    $this->sourceAlias = $alias;
  }

  public function getProjection()
  {
    if ($this->isModel()) {
      return $this->createProjection();
    } else {
      $projection = $this->createProjection();
      foreach ($this->objects as $object) {
        $projection .= ", " . $object->getProjection();
      }

      return $projection;
    }
  }

  protected function createProjection()
  {
    $projection = array();

    $columns = $this->getColumns();
    $name = ($this->hasAlias()) ? strtolower($this->getAlias())
                                : $this->getName(false);

    foreach ($columns as $column) {
      $projection[] = "{$name}.{$column} AS pre_{$name}_{$column}";
    }

    return implode(", ", $projection);
  }

  public function getJoinQuery($joinType)
  {
    if ($this->isModel()) {
      $query = array();
      $sourceName = $this->sourceAlias;
      $this->createJoinQuery($query, $joinType, $this, $sourceName);
      return implode("", $query);
    }

    $objects = $this->objects;

    $query = array();
    $this->createJoinQuery($query, $joinType, $this, $this->sourceAlias);

    $tmp = array();
    foreach ($objects as $object) {
      if (!$object->isModel()) {
        $tmp[] = $object->getJoinQuery($joinType);
      }
    }

    $tmporary = implode(" ", $tmp);
    $sourceName = ($this->hasAlias()) ? strtolower($this->getAlias())
                                      : $this->getName(false);

    foreach ($objects as $object) {
      if (!$object->isModel()) continue;
      $this->createJoinQuery($query, $joinType, $object, $sourceName);
    }

    return implode("", $query) . " " . $tmporary;
  }

  protected function createJoinQuery(&$query, $joinType, $object, $sourceName)
  {
    $name = $object->getName(false);
    $query[] = " $joinType JOIN $name ";

    if ($object->hasAlias()) {
      $name = strtolower($object->getAlias());
      $query[] = $name . " ";
    }

    $keys = $object->getJoinKeys();
    $query[] = "ON {$sourceName}.{$keys["fkey"]} = {$name}.{$keys["id"]} ";
  }

  public function createModel($row)
  {
    $columns = $this->getColumns();
    $name    = $this->getName(false);

    static $models = array();

    if (isset($models[$name])) {
      $model = clone $models[$name];
    } else {
      $model = MODEL(convert_to_modelname($name));
      $models[$name] = clone $model;
    }

    if ($this->hasAlias()) {
      $name = strtolower($this->getAlias());
    }

    $props = array();
    foreach ($columns as $column) {
      $key = "pre_{$name}_{$column}";
      $props[$column] = $row[$key];
      unset($row[$key]);
    }

    $model->setProperties($props);
    return $model;
  }
}
