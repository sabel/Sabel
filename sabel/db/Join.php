<?php

/**
 * Sabel_DB_Join
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Join extends Sabel_Object
{
  /**
   * @var string
   */
  protected $joinType = "INNER";
  
  /**
   * @var Sabel_DB_Model
   */
  protected $model = null;
  
  /**
   * @var object[]
   */
  protected $objects = array();
  
  /**
   * @var array
   */
  protected $projection = array();
  
  /**
   * @var string
   */
  protected $tblName = "";
  
  public function __construct($model)
  {
    if (is_string($model)) {
      $model = MODEL($model);
    } elseif (!is_model($model)) {
      $message = __METHOD__ . "() argument must be a string or an instance of model.";
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
  
  public function clear()
  {
    if (is_object($this->structure)) {
      $this->structure->clear();
    }
    
    Sabel_DB_Join_ColumnHash::clear();
  }
  
  public function setProjection(array $projections)
  {
    $this->projection = $projections;
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
    $this->structure->addJoinObject($this->tblName, $object);
    $this->objects[] = $object;
    
    if (empty($joinKey)) {
      $name = $object->getModel()->getTableName();
      $object->setJoinKey(create_join_key($this->model, $name));
    }
    
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
    
    $stmt = $this->model->prepareStatement(Sabel_DB_Statement::SELECT);
    
    $query = array();
    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($stmt, $joinType);
    }
    
    $rows = $this->execute($stmt, "COUNT(*) AS cnt", implode("", $query));
    if ($clearState) $this->clear();
    return (int)$rows[0]["cnt"];
  }
  
  public function select($joinType = null)
  {
    if ($joinType === null) {
      $joinType = $this->joinType;
    }
    
    $stmt = $this->model->prepareStatement(Sabel_DB_Statement::SELECT);
    $projection = $this->createProjection($stmt);
    
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
    $stmt->projection($projection)
         ->where($this->model->getCondition()->build($stmt))
         ->join($join);
    
    $constraints = $this->model->getConstraints();
    return $stmt->constraints($constraints)->execute();
  }
  
  protected function createProjection(Sabel_DB_Statement $stmt)
  {
    if (empty($this->projection)) {
      $projection = array();
      foreach ($this->objects as $object) {
        $projection = array_merge($projection, $object->getProjection($stmt));
      }
      
      $quotedTblName = $stmt->quoteIdentifier($this->tblName);
      foreach ($this->model->getColumnNames() as $column) {
        $projection[] = $quotedTblName . "." . $stmt->quoteIdentifier($column);
      }
    } else {
      $projection = array();
      foreach ($this->projection as $name => $proj) {
        $tblName = convert_to_tablename($name);
        
        if (is_string($proj)) {
          $proj = array_map("trim", explode(",", $proj));
        }
        
        if ($tblName === $this->tblName) {
          foreach ($proj as $column) {
            $projection[] = $stmt->quoteIdentifier($tblName) . "." . $stmt->quoteIdentifier($column);
          }
        } else {
          foreach ($proj as $column) {
            $as = "{$tblName}.{$column}";
            if (strlen($as) > 30) {
              $as = Sabel_DB_Join_ColumnHash::toHash($as);
            }
            
            $p = $stmt->quoteIdentifier($tblName) . "." . $stmt->quoteIdentifier($column);
            $projection[] = $p . " AS " . $stmt->quoteIdentifier($as);
          }
        }
      }
    }
    
    return implode(", ", $projection);
  }
}
