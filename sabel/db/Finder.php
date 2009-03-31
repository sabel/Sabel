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
  
  public function neq($column, $value)
  {
    $this->model->setCondition(neq($column, $value));
    
    return $this;
  }
  
  public function in($column, array $values)
  {
    $this->model->setCondition(in($column, $values));
    
    return $this;
  }
  
  public function nin($column, array $values)
  {
    $this->model->setCondition(nin($column, $values));
    
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
  
  public function bw($column, $from, $to = null)
  {
    return $this->between($column, $from, $to);
  }
  
  public function notBetween($column, $from, $to = null)
  {
    $this->model->setCondition(notBetween($column, $from, $to));
    
    return $this;
  }
  
  public function nbw($column, $from, $to = null)
  {
    return $this->notBetween($column, $from, $to);
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
  
  public function isNotNull($column)
  {
    $this->model->setCondition(isNotNull($column));
    
    return $this;
  }
  
  public function where(/* args */)
  {
    foreach (func_get_args() as $condition) {
      $this->model->setCondition($condition);
    }
    
    return $this;
  }
  
  public function w(/* args */)
  {
    $args = func_get_args();
    return call_user_func_array(array($this, "where"), $args);
  }
  
  public function orWhere(/* args */)
  {
    $or = new Sabel_Db_Condition_Or();
    foreach (func_get_args() as $condition) {
      $or->add($condition);
    }
    
    $this->model->setCondition($or);
    
    return $this;
  }
  
  public function ow(/* args */)
  {
    $args = func_get_args();
    return call_user_func_array(array($this, "orWhere"), $args);
  }
  
  public function andWhere(/* args */)
  {
    $and = new Sabel_Db_Condition_And();
    foreach (func_get_args() as $condition) {
      $and->add($condition);
    }
    
    $this->model->setCondition($and);
    
    return $this;
  }
  
  public function aw(/* args */)
  {
    $args = func_get_args();
    return call_user_func_array(array($this, "andWhere"), $args);
  }
  
  public function innerJoin($mdlName, $on = array(), $alias = "")
  {
    $this->_join($mdlName, $on, $alias, "INNER");
    
    return $this;
  }
  
  public function leftJoin($mdlName, $on = array(), $alias = "")
  {
    $this->_join($mdlName, $on, $alias, "LEFT");
    
    return $this;
  }
  
  public function rightJoin($mdlName, $on = array(), $alias = "")
  {
    $this->_join($mdlName, $on, $alias, "RIGHT");
    
    return $this;
  }
  
  protected function _join($mdlName, $on, $alias, $type)
  {
    if ($this->join === null) {
      $this->join = new Sabel_Db_Join($this->model);
    }
    
    $this->join->add($mdlName, $on, $alias, $type);
  }
  
  public function limit($limit)
  {
    $this->model->setLimit($limit);
    
    return $this;
  }
  
  public function offset($offset)
  {
    $this->model->setOffset($offset);
    
    return $this;
  }
  
  public function sort($column, $smode = "ASC", $nulls = "LAST")
  {
    $this->model->setOrderBy($column, $smode, $nulls);
    
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

function neq($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::EQUAL, $column, $value, true
  );
}

function in($column, array $values)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::IN, $column, $values
  );
}

function nin($column, array $values)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::IN, $column, $values, true
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
    return __between($column, $from, false);
  } else {
    return __between($column, array($from, $to), false);
  }
}

function notBetween($column, $from, $to = null)
{
  if ($to === null) {
    return __between($column, $from, true);
  } else {
    return __between($column, array($from, $to), true);
  }
}

function __between($column, array $params, $not)
{
  if (isset($params["from"])) $params[0] = $params["from"];
  if (isset($params["to"]))   $params[1] = $params["to"];
  
  unset($params["from"], $params["to"]);
  
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::BETWEEN, $column, $params, $not
  );
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

function ow(/* args */)
{
  $or = new Sabel_Db_Condition_Or();
  foreach (func_get_args() as $condition) {
    $or->add($condition);
  }
  
  return $or;
}

function aw(/* args */)
{
  $and = new Sabel_Db_Condition_And();
  foreach (func_get_args() as $condition) {
    $and->add($condition);
  }
  
  return $and;
}

function rel($mdlName)
{
  return new Sabel_Db_Join_Relation($mdlName);
}

function innerJoin($mdlName, $on = array(), $alias = "")
{
  $join = new Sabel_Db_Join_Object($mdlName);
  return $join->setAlias($alias)->setJoinKey($on)->setJoinType("INNER");
}

function leftJoin($mdlName, $on = array(), $alias = "")
{
  $join = new Sabel_Db_Join_Object($mdlName);
  return $join->setAlias($alias)->setJoinKey($on)->setJoinType("LEFT");
}

function rightJoin($mdlName, $on = array(), $alias = "")
{
  $join = new Sabel_Db_Join_Object($mdlName);
  return $join->setAlias($alias)->setJoinKey($on)->setJoinType("RIGHT");
}
