<?php

/**
 * Sabel_DB_Abstract_Statement
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Statement extends Sabel_Object
{
  /**
   * @var const Sabel_DB_Statement
   */
  protected $type = Sabel_DB_Statement::QUERY;
  
  /**
   * @var string
   */
  protected $query = "";
  
  /**
   * @var Sabel_DB_Abstract_Driver
   */
  protected $driver = null;
  
  /**
   * @var Sabel_DB_Metadata_Table
   */
  protected $metadata = null;
  
  /**
   * @var array
   */
  protected $bindValues = array();
  
  /**
   * @var string
   */
  protected $table = "";
  
  /**
   * @var mixed
   */
  protected $projection = array();
  
  /**
   * @var string
   */
  protected $join = "";
  
  /**
   * @var string
   */
  protected $where = "";
  
  /**
   * @var array
   */
  protected $values = array();
  
  /**
   * @var array
   */
  protected $constraints = array();
  
  /**
   * @var string
   */
  protected $seqColumn = null;
  
  /**
   * @var boolean
   */
  protected $forUpdate = false;
  
  /**
   * @param Sabel_DB_Abstract_Driver $driver
   *
   * @return void
   */
  abstract public function setDriver($driver);
  
  /**
   * @return Sabel_DB_Abstract_Driver
   */
  public function getDriver()
  {
    return $this->driver;
  }
  
  /**
   * @param Sabel_DB_Metadata_Table $metadata
   *
   * @return self
   */
  public function setMetadata(Sabel_DB_Metadata_Table $metadata)
  {
    $this->table    = $metadata->getTableName();
    $this->metadata = $metadata;
    
    return $this;
  }
  
  /**
   * @return self
   */
  public function clear()
  {
    $this->query       = "";
    $this->bindValues  = array();
    $this->projection  = array();
    $this->join        = "";
    $this->where       = "";
    $this->values      = array();
    $this->constraints = array();
    $this->seqColumn   = null;
    $this->forUpdate   = false;
    
    return $this;
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
      $message = __METHOD__ . "() argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
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
  
  public function projection($projection)
  {
    if (is_array($projection) || is_string($projection)) {
      $this->projection = $projection;
    } else {
      $message = "argument must be a string or an array.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
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
      $this->where = ($where === "") ? "" : " " . $where;
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
      $message = __METHOD__ . "() argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    return $this;
  }
  
  public function forUpdate($bool)
  {
    if (is_bool($bool)) {
      $this->forUpdate = $bool;
    } else {
      $message = __METHOD__ . "() argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
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
    } elseif ($this->isUpdate() || $this->isDelete()) {
      return $this->driver->getAffectedRows();
    } else {
      return $result;
    }
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
    if ($this->metadata === null) {
      $message = "can't build sql query. must set the metadata with setMetadata().";
      throw new Sabel_Exception_Runtime($message);
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
  
  public function __toString()
  {
    return $this->build();
  }
  
  protected function createSelectSql()
  {
    $tblName = $this->quoteIdentifier($this->table);
    $projection = $this->getProjection();
    
    $sql = "SELECT $projection FROM "
         . $this->quoteIdentifier($this->table)
         . $this->join . $this->where
         . $this->createConstraintSql();
    
    if ($this->forUpdate) $sql .= " FOR UPDATE";
    
    return $sql;
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
    
    if (isset($c["order"])) {
      $sql .= " ORDER BY " . $this->quoteIdentifierForOrderString($c["order"]);
    }
    
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
      $colNames = $this->quoteIdentifier($this->metadata->getColumnNames());
      return implode(", ", $colNames);
    } elseif (is_string($this->projection)) {
      return $this->projection;
    } else {
      $ps = array();
      foreach ($this->projection as $p) {
        $ps[] = $this->quoteIdentifier($p);
      }
      
      return implode(", ", $ps);
    }
  }
  
  protected function quoteIdentifierForOrderString($orderBy)
  {
    $results = array();
    $orders  = array_map("trim", explode(", ", $orderBy));
    
    foreach ($orders as $order) {
      @list ($col, $sort) = explode(" ", $order);
      if ($sort === null) $sort = "ASC";
      if (($pos = strpos($col, ".")) !== false) {
        $tbl = convert_to_tablename(substr($col, 0, $pos));
        $results[] = $this->quoteIdentifier($tbl) . "."
                   . $this->quoteIdentifier(substr($col, $pos + 1))
                   . " " . $sort;
      } else {
        $results[] = $this->quoteIdentifier($col) . " " . $sort;
      }
    }
    
    return implode(", ", $results);
  }
}
