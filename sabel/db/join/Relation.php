<?php

/**
 * Sabel_Db_Join_Relation
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Join_Relation extends Sabel_Db_Join_TemplateMethod
{
  protected $objects = array();
  
  public function add($object, $alias = "", $joinKey = array())
  {
    if (is_string($object)) {
      $object = new Sabel_Db_Join_Object(MODEL($object), $alias, $joinKey);
    } elseif (is_model($object)) {
      $object = new Sabel_Db_Join_Object($object, $alias, $joinKey);
    }
    
    $structure = Sabel_Db_Join_Structure::getInstance();
    $structure->addJoinObject($this->getName(), $object);
    $object->setChildName($this->getName());
    $this->objects[] = $object;
    
    if (empty($joinKey)) {
      $name = $object->getModel()->getTableName();
      $object->setJoinKey(create_join_key($this->model, $name));
    }
    
    return $this;
  }
  
  public function getProjection(Sabel_Db_Statement $stmt)
  {
    $projection = array();
    $name = ($this->hasAlias()) ? strtolower($this->aliasName) : $this->getName(false);
    
    foreach ($this->columns as $column) {
      $as = "{$name}.{$column}";
      if (strlen($as) > 30) $as = Sabel_Db_Join_ColumnHash::toHash($as);
      $p = $stmt->quoteIdentifier($name) . "." . $stmt->quoteIdentifier($column);
      $projection[] = $p . " AS " . $stmt->quoteIdentifier($as);
    }
    
    foreach ($this->objects as $object) {
      $projection = array_merge($projection, $object->getProjection($stmt));
    }
    
    return $projection;
  }
  
  public function getJoinQuery(Sabel_Db_Statement $stmt, $joinType)
  {
    $name  = $stmt->quoteIdentifier($this->tblName);
    $keys  = $this->joinKey;
    $query = array(" $joinType JOIN $name ");
    
    if ($this->hasAlias()) {
      $name = $stmt->quoteIdentifier(strtolower($this->aliasName));
      $query[] = $name . " ";
    }
    
    $query[] = "ON {$name}." . $stmt->quoteIdentifier($keys["id"])
             . " = " . $stmt->quoteIdentifier(strtolower($this->childName))
             . "."   . $stmt->quoteIdentifier($keys["fkey"]);
    
    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($stmt, $joinType);
    }
    
    return implode("", $query);
  }
}
