<?php

/**
 * Sabel_DB_Join_Template
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Join_Template
{
  protected
    $model      = null,
    $columns    = array(),
    $joinKey    = array(),
    $tblName    = "",
    $aliasName  = "",
    $sourceName = "";

  public function __construct($model, $columns = array(), $alias = "", $joinKey = array())
  {
    $this->model   = $model;
    $this->tblName = $model->getTableName();

    if (empty($columns)) {
      $this->columns = $model->getColumnNames();
    } else {
      $this->columns = $columns;
    }

    $this->aliasName = $alias;
    $this->joinKey   = $joinKey;
  }

  public function getModel()
  {
    return $this->model;
  }

  public function getName($alias = true)
  {
    if ($alias && $this->hasAlias()) {
      return $this->aliasName;
    } else {
      return $this->tblName;
    }
  }

  public function hasAlias()
  {
    return ($this->aliasName !== "");
  }

  public function setJoinKey($joinKey)
  {
    if (empty($this->joinKey)) {
      $this->joinKey = $joinKey;
    }
  }

  public function setSourceName($name)
  {
    $this->sourceName = $name;
  }

  public function createModel($row)
  {
    $name = $this->tblName;

    static $models = array();

    if (isset($models[$name])) {
      $model = clone $models[$name];
    } else {
      $model = MODEL(convert_to_modelname($name));
      $models[$name] = clone $model;
    }

    if ($this->hasAlias()) {
      $name = strtolower($this->aliasName);
    }

    $props = array();
    foreach ($this->columns as $column) {
      $hash = Sabel_DB_Join_ColumnHash::getHash("pre_{$name}_{$column}");
      $props[$column] = $row[$hash];
    }

    $model->setProperties($props);
    return $model;
  }
}
