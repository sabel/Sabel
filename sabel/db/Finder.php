<?php

/**
 * Sabel_Db_Finder
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Finder
{
  protected $model = null;
  protected $join = null;
  
  public function __construct($mdlName, $projection = null)
  {
    $this->model = (is_model($mdlName)) ? $mdlName : MODEL($mdlName);
    
    if ($projection !== null) {
      $this->p($projection);
    }
  }
  
  public function p($projection)
  {
    $this->model->setProjection($projection);
    
    return $this;
  }
  
  public function eq($column, $value)
  {
    $this->model->setCondition(eq($column, $value));
    
    return $this;
  }
  
  public function in($column, array $values)
  {
    $this->model->setCondition(in($column, $values));
    
    return $this;
  }
  
  public function lt($column, $value)
  {
    $this->model->setCondition(lt($column, $value));
    
    return $this;
  }
  
  public function le($column, $value)
  {
    $this->model->setCondition(le($column, $value));
    
    return $this;
  }
  
  public function gt($column, $value)
  {
    $this->model->setCondition(gt($column, $value));
    
    return $this;
  }
  
  public function ge($column, $value)
  {
    $this->model->setCondition(ge($column, $value));
    
    return $this;
  }
  
  public function between($column, $from, $to = null)
  {
    $this->model->setCondition(between($column, $from, $to));
    
    return $this;
  }
  
  public function starts($column, $value)
  {
    $this->model->setCondition(starts($column, $value));
    
    return $this;
  }
  
  public function ends($column, $value)
  {
    $this->model->setCondition(ends($column, $value));
    
    return $this;
  }
  
  public function contains($column, $value)
  {
    $this->model->setCondition(contains($column, $value));
    
    return $this;
  }
  
  public function isNull($column)
  {
    $this->model->setCondition(isNull($column));
    
    return $this;
  }
  
  public function nl($column)
  {
    return $this->isNull($column);
  }
  
  public function isNotNull($column)
  {
    $this->model->setCondition(isNotNull($column));
    
    return $this;
  }
  
  public function nnl($column)
  {
    return $this->isNotNull($column);
  }
  
  public function c(/* args */)
  {
    foreach (func_get_args() as $condition) {
      $this->model->setCondition($condition);
    }
    
    return $this;
  }
  
  public function _or(/* args */)
  {
    $or = new Sabel_Db_Condition_Or();
    foreach (func_get_args() as $condition) {
      $or->add($condition);
    }
    
    $this->model->setCondition($or);
    
    return $this;
  }
  
  public function _and(/* args */)
  {
    $and = new Sabel_Db_Condition_And();
    foreach (func_get_args() as $condition) {
      $and->add($condition);
    }
    
    $this->model->setCondition($and);
    
    return $this;
  }
  
  public function innerJoin($mdlName, $keys = null, $alias = "")
  {
    $this->_join($mdlName, $keys, $alias, "inner");
    
    return $this;
  }
  
  public function ij($mdlName, $keys = null, $alias = "")
  {
    return $this->innerJoin($mdlName, $keys, $alias);
  }
  
  public function leftJoin($mdlName, $keys = null, $alias = "")
  {
    $this->_join($mdlName, $keys, $alias, "left");
    
    return $this;
  }
  
  public function lj($mdlName, $keys = null, $alias = "")
  {
    return $this->leftJoin($mdlName, $keys, $alias);
  }
  
  public function rightJoin($mdlName, $keys = null, $alias = "")
  {
    $this->_join($mdlName, $keys, $alias, "right");
    
    return $this;
  }
  
  public function rj($mdlName, $keys = null, $alias = "")
  {
    return $this->rightJoin($mdlName, $keys, $alias);
  }
  
  public function join($joinObject, $keys = array(), $alias = "", $type = "inner")
  {
    $this->_join($joinObject, $keys, $alias, $type);
    
    return $this;
  }
  
  protected function _join($mdlName, $keys, $alias, $type)
  {
    if ($this->join === null) {
      $this->join = new Sabel_Db_Join($this->model);
    }
    
    $this->join->add($mdlName, $keys, $alias, $type);
  }
  
  public function sort($column, $smode = "asc")
  {
    $this->model->setOrderBy($column, $smode);
    
    return $this;
  }
  
  public function fetchAll()
  {
    if ($this->join === null) {
      return $this->model->select();
    } else {
      return $this->join->select();
    }
  }
  
  public function fetch()
  {
    $this->model->setLimit(1);
    
    if ($this->join === null) {
      return $this->model->selectOne();
    } else {
      return $this->join->selectOne();
    }
  }
  
  public function all()
  {
    return $this->fetchAll();
  }
  
  public function one()
  {
    return $this->fetch();
  }
  
  public function count()
  {
    return $this->model->getCount();
  }
}

function eq($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::EQUAL, $column, $value
  );
}

function in($column, array $values)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::IN, $column, $values
  );
}

function lt($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::LESS_THAN, $column, $value
  );
}

function le($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::LESS_EQUAL, $column, $value
  );
}

function gt($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::GREATER_THAN, $column, $value
  );
}

function ge($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::GREATER_EQUAL, $column, $value
  );
}

function between($column, $from, $to = null)
{
  if ($to === null) {
    if (isset($from["from"])) $from[0] = $from["from"];
    if (isset($from["to"]))   $from[1] = $from["to"];
    
    unset($from["from"], $from["to"]);
    
    return Sabel_Db_Condition::create(
      Sabel_Db_Condition::BETWEEN, $column, $from
    );
  } else {
    return Sabel_Db_Condition::create(
      Sabel_Db_Condition::BETWEEN, $column, array($from, $to)
    );
  }
}

function starts($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::LIKE, $column, $value
  )->type(Sabel_Db_Condition_Like::STARTS_WITH);
}

function ends($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::LIKE, $column, $value
  )->type(Sabel_Db_Condition_Like::ENDS_WITH);
}

function contains($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::LIKE, $column, $value
  )->type(Sabel_Db_Condition_Like::CONTAINS);
}

function isNull($column)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::ISNULL, $column
  );
}

function isNotNull($column)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::ISNOTNULL, $column
  );
}

function _or(/* args */)
{
  $or = new Sabel_Db_Condition_Or();
  foreach (func_get_args() as $condition) {
    $or->add($condition);
  }
  
  return $or;
}

function _and(/* args */)
{
  $and = new Sabel_Db_Condition_And();
  foreach (func_get_args() as $condition) {
    $and->add($condition);
  }
  
  return $and;
}

function with($mdlName)
{
  return new Sabel_Db_Join_Relation($mdlName);
}
