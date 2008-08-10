<?php

/**
 * Sabel_Db_Join_TemplateMethod
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Join_TemplateMethod extends Sabel_Object
{
  protected
    $model     = null,
    $columns   = array(),
    $joinKey   = array(),
    $tblName   = "",
    $aliasName = "",
    $childName = "";
    
  public function __construct($model, $alias = "", $joinKey = array())
  {
    $this->model     = (is_string($model)) ? MODEL($model) : $model;
    $this->tblName   = $this->model->getTableName();
    $this->columns   = $this->model->getColumnNames();
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
    $this->joinKey = $joinKey;
  }
  
  public function setChildName($name)
  {
    $this->childName = $name;
  }
  
  public function createModel(&$row)
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
      $hash = Sabel_Db_Join_ColumnHash::getHash("{$name}.{$column}");
      if (isset($row[$hash])) {
        $props[$column] = $row[$hash];
        unset($row[$hash]);
      }
    }
    
    $model->setProperties($props);
    return $model;
  }
}
