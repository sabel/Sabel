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
class Sabel_DB_Join extends Sabel_Object
{
  private
    $model    = null,
    $objects  = array(),
    $joinType = "INNER",
    $tblName  = "";
    
  public function __construct($model)
  {
    if (is_string($model)) {
      $model = MODEL($model);
    } elseif (!is_model($model)) {
      $message = "argument must be a string or instance of model.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    $this->model     = $model;
    $this->tblName   = $model->getTableName();
    $this->structure = Sabel_DB_Join_Structure::getInstance();
  }
  
  public function getModel()
  {
    return $this->model;
  }
  
  public function setJoinType($joinType)
  {
    $this->joinType = $joinType;
  }
  
  public function setCondition($arg1, $arg2 = null)
  {
    $this->model->setCondition($arg1, $arg2);
  }
  
  public function setOrderBy($orderBy)
  {
    $this->model->setOrderBy($orderBy);
  }
  
  public function add($object, $alias = "", $joinKey = array())
  {
    if (is_string($object)) {
      $object = new Sabel_DB_Join_Object(MODEL($object), $alias, $joinKey);
    } elseif (is_model($object)) {
      $object = new Sabel_DB_Join_Object($object, $alias, $joinKey);
    }
    
    $object->setChildName($this->tblName);
    $this->objects[] = $object;
    $this->structure->addJoinObject($object);
    $this->structure->add($this->tblName, $object->getName());
    
    if (!empty($joinKey)) return $this;
    
    $name = $object->getModel()->getTableName();
    $object->setJoinKey(create_join_key($this->model, $name));
    
    return $this;
  }
  
  public function setParents(array $parents)
  {
    foreach ($parents as $parent) {
      $this->add($parent);
    }
    
    return $this;
  }
  
  public function getCount($joinType = null, $clearState = true)
  {
    if ($joinType === null) {
      $joinType = $this->joinType;
    }
    
    $stmt = $this->model->getStatement(Sabel_DB_Statement::SELECT);
    
    $query = array();
    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($stmt, $joinType);
    }
    
    $rows = $this->execute($stmt, "COUNT(*) AS cnt", implode("", $query));
    if ($clearState) $this->clear();
    return (int)$rows[0]["cnt"];
  }
  
  public function join($joinType = null)
  {
    if ($joinType === null) {
      $joinType = $this->joinType;
    }
    
    $stmt = $this->model->getStatement(Sabel_DB_Statement::SELECT);
    $projection = $this->model->getProjection();
    
    if (empty($projection)) {
      $projection = array();
      foreach ($this->objects as $object) {
        $projection = array_merge($projection, $object->getProjection($stmt));
      }
      
      $quotedTblName = $stmt->quoteIdentifier($this->tblName);
      foreach ($this->model->getColumnNames() as $column) {
        $projection[] = $quotedTblName . "." . $stmt->quoteIdentifier($column);
      }
      
      $projection = implode(", ", $projection);
    }
    
    $query = array();
    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($stmt, $joinType);
    }
    
    if ($rows = $this->execute($stmt, $projection, implode("", $query))) {
      $results = Sabel_DB_Join_Result::build($this->model, $this->structure, $rows);
    } else {
      $results = false;
    }
    
    $this->clear();
    return $results;
  }
  
  protected function execute($stmt, $projection, $join)
  {
    $stmt->join($join)
         ->projection($projection)
         ->where($this->model->getCondition()->build($stmt));
         
    $constraints = $this->model->getConstraints();
    return $stmt->constraints($constraints)->execute();
  }
  
  public function clear()
  {
    $this->structure->clear();
    Sabel_DB_Join_ColumnHash::clear();
  }
}
