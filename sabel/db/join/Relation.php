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
    } elseif (is_model($object)) {
      $object = new Sabel_DB_Join_Object($object, $alias, $joinKey);
    }
    
    $structure = Sabel_DB_Join_Structure::getInstance();
    $structure->addJoinObject($object);
    $myName = $this->getName();
    $object->setChildName($myName);
    $this->objects[] = $object;
    
    $structure->add($myName, $object->getName());
    if (!empty($joinKey)) return $this;
    
    $name = $object->getModel()->getTableName();
    $object->setJoinKey(create_join_key($this->model, $name));
    
    return $this;
  }
  
  public function getProjection(Sabel_DB_Abstract_Statement $stmt)
  {
    $projection = array();
    $name = ($this->hasAlias()) ? strtolower($this->aliasName) : $this->getName(false);
    
    foreach ($this->columns as $column) {
      $as = "pre_{$name}_{$column}";
      
      if (strlen($as) > 30) {
        $as = Sabel_DB_Join_ColumnHash::toHash("pre_{$name}_{$column}");
      }
      
      $p = $stmt->quoteIdentifier($name) . "." . $stmt->quoteIdentifier($column);
      $projection[] = $p . " AS " . $as;
    }
    
    foreach ($this->objects as $object) {
      $projection = array_merge($projection, $object->getProjection($stmt));
    }
    
    return $projection;
  }
  
  public function getJoinQuery(Sabel_DB_Abstract_Statement $stmt, $joinType)
  {
    $name  = $stmt->quoteIdentifier($this->tblName);
    $keys  = $this->joinKey;
    $query = array(" $joinType JOIN $name ");
    
    if ($this->hasAlias()) {
      $name = $stmt->quoteIdentifier(strtolower($this->aliasName));
      $query[] = $name . " ";
    }
    
    $query[] = "ON " . $stmt->quoteIdentifier(strtolower($this->childName)) . "."
             . $stmt->quoteIdentifier($keys["fkey"]) . " = {$name}."
             . $stmt->quoteIdentifier($keys["id"]);
             
    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($stmt, $joinType);
    }
    
    return implode("", $query);
  }
}
