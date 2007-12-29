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
    $manip    = null,
    $model    = null,
    $objects  = array(),
    $joinType = "INNER",
    $tblName  = "";
    
  public function __construct(Sabel_DB_Manipulator $manip)
  {
    $this->manip     = $manip;
    $this->model     = $manip->getModel();
    $this->tblName   = $this->model->getTableName();
    $this->structure = Sabel_DB_Join_Structure::getInstance();
  }
  
  public function getManipulator()
  {
    return $this->manip;
  }
  
  public function setJoinType($joinType)
  {
    $this->joinType = $joinType;
  }
  
  public function add($object, $alias = "", $joinKey = array())
  {
    if (is_string($object)) {
      $object = new Sabel_DB_Join_Object(MODEL($object), $alias, $joinKey);
    } elseif (is_model($object)) {
      $object = new Sabel_DB_Join_Object($object, $alias, $joinKey);
    }
    
    $object->setSourceName($this->tblName);
    $this->objects[] = $object;
    $this->structure->addJoinObject($object);
    $this->structure->add($this->tblName, $object->getName());
    
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
    
    $stmt = $this->manip->getStatement(Sabel_DB_Statement::SELECT);
    
    $query = array();
    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($stmt, $joinType);
    }
    
    $rows = $this->execute($stmt,
                           array("COUNT(*) AS cnt"),
                           implode("", $query),
                           array("limit" => 1));
                           
    if ($clearState) $this->clear();
    return (int)$rows[0]["cnt"];
  }
  
  public function join($joinType = null)
  {
    if ($joinType === null) {
      $joinType = $this->joinType;
    }
    
    $stmt = $this->manip->getStatement(Sabel_DB_Statement::SELECT);
    $projection = $this->manip->getProjection();
    
    if (empty($projection)) {
      $projection = array();
      foreach ($this->objects as $object) {
        $projection = array_merge($projection, $object->getProjection($stmt));
      }
      
      $model   = $this->model;
      $tblName = $model->getTableName();
      
      foreach ($model->getColumnNames() as $column) {
        $projection[] = $stmt->quoteIdentifier($tblName) . "."
                      . $stmt->quoteIdentifier($column);
      }
    }
    
    $query = array();
    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($stmt, $joinType);
    }
    
    if ($rows = $this->execute($stmt, $projection, implode("", $query))) {
      $results = Sabel_DB_Join_Result::build($model, $this->structure, $rows);
    } else {
      $results = false;
    }
    
    $this->clear();
    return $results;
  }
  
  protected function execute($stmt, $projection, $join, $constraints = null)
  {
    $manip = $this->manip;
    
    $stmt->join($join)
         ->projection($projection)
         ->where($manip->loadConditionManager()->build($stmt));
        
    if ($constraints === null) $constraints = $manip->getConstraints();
    return $manip->executeStatement($stmt->constraints($constraints));
  }
  
  public function clear()
  {
    $this->structure->clear();
    Sabel_DB_Join_ColumnHash::clear();
  }
}
