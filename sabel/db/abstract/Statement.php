<?php

/**
 * Sabel_DB_Abstract_Statement
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Statement extends Sabel_Object
{
  protected
    $type       = Sabel_DB_Statement::QUERY,
    $query      = "",
    $driver     = null,
    $schema     = null,
    $bindValues = array();
    
  protected
    $table       = "",
    $projection  = array(),
    $join        = "",
    $where       = "",
    $values      = array(),
    $constraints = array(),
    $seqColumn   = null;
    
  public function getDriver()
  {
    return $this->driver;
  }
  
  public function type($type = null)
  {
    if ($type === null) {
      return $this->type;
    } else {
      $this->type = $type;
      return $this;
    }
  }
  
  public function setQuery($query)
  {
    if (is_string($query)) {
      $this->query = $query;
    } else {
      throw new Sabel_Exception_InvalidArgument("argument must be a string.");
    }
    
    return $this;
  }
  
  public function getQuery()
  {
    return ($this->hasQuery()) ? $this->query : $this->build();
  }
  
  public function hasQuery()
  {
    return (is_string($this->query) && $this->query !== "");
  }
  
  public function table($table)
  {
    if (is_string($table)) {
      $this->table    = $table;
      $connectionName = $this->driver->getConnectionName();
      $this->schema   = Sabel_DB_Schema::getTableSchema($table, $connectionName);
    } else {
      throw new Sabel_Exception_InvalidArgument("argument must be a string.");
    }
    
    return $this;
  }
  
  public function projection(array $projection)
  {
    $this->projection = $projection;
    
    return $this;
  }
  
  public function join($join)
  {
    if (is_string($join)) {
      $this->join = $join;
    } else {
      throw new Sabel_Exception_InvalidArgument("argument must be a string.");
    }
    
    return $this;
  }
  
  public function where($where)
  {
    if (is_string($where)) {
      $this->where = $where;
    } else {
      throw new Sabel_Exception_InvalidArgument("argument must be a string.");
    }
    
    return $this;
  }
  
  public function constraints(array $constraints)
  {
    $this->constraints = $constraints;
    
    return $this;
  }
  
  public function values(array $values)
  {
    $this->values = $this->bindValues = $values;
    
    return $this;
  }
  
  public function sequenceColumn($seqColumn)
  {
    if ($seqColumn === null) {
      $this->seqColumn = null;
    } elseif (is_string($seqColumn)) {
      $this->seqColumn = $seqColumn;
    } else {
      throw new Sabel_Exception_InvalidArgument("argument must be a string.");
    }
    
    return $this;
  }
  
  public function execute()
  {
    $query = $this->getQuery();
    
    if (empty($this->bindValues)) {
      $result = $this->driver->execute($query);
    } else {
      $bindValues = $this->escape($this->bindValues);
      foreach ($bindValues as $k => $v) {
        $bindValues["@{$k}@"] = $v;
        unset($bindValues[$k]);
      }
      
      $result = $this->driver->execute($query, $bindValues);
    }
    
    if ($this->isInsert() && $this->seqColumn !== null) {
      return $this->driver->getLastInsertId();
    }
    
    return $result;
  }
  
  public function setBindValue($key, $val)
  {
    $this->bindValues[$key] = $val;
    
    return $this;
  }
  
  public function setBindValues(array $values)
  {
    $this->bindValues = $values;
    
    return $this;
  }
  
  public function getBindValues()
  {
    return $this->bindValues;
  }
  
  public function isSelect()
  {
    return ($this->type === Sabel_DB_Statement::SELECT);
  }
  
  public function isInsert()
  {
    return ($this->type === Sabel_DB_Statement::INSERT);
  }
  
  public function isUpdate()
  {
    return ($this->type === Sabel_DB_Statement::UPDATE);
  }
  
  public function isDelete()
  {
    return ($this->type === Sabel_DB_Statement::DELETE);
  }
  
  public function quoteIdentifier($arg)
  {
    if (is_array($arg)) {
      foreach ($arg as &$v) {
        $v = '"' . $v . '"';
      }
      return $arg;
    } elseif (is_string($arg)) {
      return '"' . $arg . '"';
    } else {
      $message = "argument must be a string or an array.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function build()
  {
    if ($this->schema === null) {
      $message = "can't build query. please call table() method.";
      throw new Sabel_DB_Statement_Exception($message);
    }
    
    if ($this->isSelect()) {
      return $this->createSelectSql();
    } elseif ($this->isInsert()) {
      return $this->createInsertSql();
    } elseif ($this->isUpdate()) {
      return $this->createUpdateSql();
    } elseif ($this->isDelete()) {
      return $this->createDeleteSql();
    } else {
      return $this->query;
    }
  }
  
  protected function createSelectSql()
  {
    $tblName = $this->quoteIdentifier($this->table);
    $projection = $this->getProjection();
    $sql = "SELECT $projection FROM $tblName" . $this->join . $this->where;
    return $sql . $this->createConstraintSql();
  }
  
  protected function createInsertSql()
  {
    $sql  = "INSERT INTO {$this->quoteIdentifier($this->table)} (";
    $cols = array_keys($this->values);
    $hlds = array();
    
    foreach ($cols as $c) $hlds[] = "@{$c}@";
    
    $cols = $this->quoteIdentifier($cols);
    $sql .= implode(", ", $cols) . ") VALUES(" . implode(", ", $hlds) . ")";
    return $sql;
  }
  
  protected function createUpdateSql()
  {
    $updates = array();
    foreach ($this->values as $column => $value) {
      $updates[] = $this->quoteIdentifier($column) . " = @{$column}@";
    }
    
    $tblName = $this->quoteIdentifier($this->table);
    return "UPDATE $tblName SET " . implode(", ", $updates) . $this->where;
  }
  
  protected function createDeleteSql()
  {
    $tblName = $this->quoteIdentifier($this->table);
    return "DELETE FROM $tblName" . $this->where;
  }
  
  protected function createConstraintSql()
  {
    $sql = "";
    $c = $this->constraints;
    
    if (isset($c["group"]))  $sql .= " GROUP BY " . $c["group"];
    if (isset($c["having"])) $sql .= " HAVING "   . $c["having"];
    if (isset($c["order"]))  $sql .= " ORDER BY " . $c["order"];
    
    if (isset($c["offset"]) && !isset($c["limit"])) {
      $sql .= " LIMIT 100 OFFSET " . $c["offset"];
    } else {
      if (isset($c["limit"]))  $sql .= " LIMIT "  . $c["limit"];
      if (isset($c["offset"])) $sql .= " OFFSET " . $c["offset"];
    }
    
    return $sql;
  }
  
  protected function getProjection()
  {
    if (empty($this->projection)) {
      $colNames = $this->quoteIdentifier($this->schema->getColumnNames());
      return implode(", ", $colNames);
    } else {
      $ps = array();
      foreach ($this->projection as $p) {
        if (is_object($p)) {
          $ps[] = $this->toSqlValue($p);
        } else {
          $ps[] = $p;
        }
      }
      
      return implode(", ", $ps);
    }
  }
  
  protected function toSqlValue($object)
  {
    return $object->getSqlValue($this);
  }
}
