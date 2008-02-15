<?php

/**
 * Sabel_DB_Join_Object
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Join_Object extends Sabel_DB_Join_TemplateMethod
{
  public function getProjection(Sabel_DB_Abstract_Statement $stmt)
  {
    $projection = array();
    $name = ($this->hasAlias()) ? strtolower($this->aliasName) : $this->tblName;
    
    foreach ($this->columns as $column) {
      $as = "pre_{$name}_{$column}";
      
      if (strlen($as) > 30) {
        $as = Sabel_DB_Join_ColumnHash::toHash("pre_{$name}_{$column}");
      }
      
      $p = $stmt->quoteIdentifier($name) . "." . $stmt->quoteIdentifier($column);
      $projection[] = $p . " AS " . $as;
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
             
    return implode("", $query);
  }
}
