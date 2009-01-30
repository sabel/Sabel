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
  protected $model     = null;
  protected $columns   = array();
  protected $on        = array();
  protected $joinType  = "INNER";
  protected $tblName   = "";
  protected $aliasName = "";
  protected $childName = "";
  
  public function __construct($model)
  {
    $this->model   = (is_string($model)) ? MODEL($model) : $model;
    $this->tblName = $this->model->getTableName();
    $this->columns = $this->model->getColumnNames();
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
  
  public function setAlias($alias)
  {
    $this->aliasName = $alias;
    
    return $this;
  }
  
  public function hasAlias()
  {
    return !empty($this->aliasName);
  }
  
  public function on($on)
  {
    if (isset($on[0])) $on["id"]   = $on[0];
    if (isset($on[1])) $on["fkey"] = $on[1];
    
    unset($on[0], $on[1]);
    
    $this->on = $on;
    
    return $this;
  }
  
  public function getOn()
  {
    return $this->on;
  }
  
  public function setJoinType($joinType)
  {
    $this->joinType = strtoupper($joinType);
    
    return $this;
  }
  
  public function setChildName($name)
  {
    $this->childName = $name;
    
    return $this;
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
      if (array_key_exists($hash, $row)) {
        $props[$column] = $row[$hash];
        unset($row[$hash]);
      }
    }
    
    $model->setProperties($props);
    return $model;
  }
}
