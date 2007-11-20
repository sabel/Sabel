<?php

/**
 * Sabel_DB_Abstract_Sql
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Sql extends Sabel_Object
{
  protected
    $connectionName = "";

  protected
    $type       = Sabel_DB_Sql::QUERY,
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

  protected
    $placeHolderPrefix = "@",
    $placeHolderSuffix = "@";

  public function __construct($connectionName)
  {
    $this->driver = Sabel_DB_Driver::create($connectionName);
    $this->connectionName = $this->driver->getConnectionName();
  }

  public function setType($type)
  {
    $this->type = $type;

    return $this;
  }

  public function setQuery($query)
  {
    if (is_string($query)) {
      $this->query = $query;
    } else {
      throw new Sabel_DB_Sql_Exception("setQuery() argument should be a string.");
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
      $this->table  = $table;
      $this->schema = Sabel_DB_Schema::create($table, $this->connectionName);
    } else {
      throw new Sabel_DB_Sql_Exception("table() argument should be a string.");
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
      throw new Sabel_DB_Sql_Exception("join() argument should be a string.");
    }

    return $this;
  }

  public function where($where)
  {
    if (is_string($where)) {
      $this->where = $where;
    } else {
      throw new Sabel_DB_Sql_Exception("where() argument should be a string.");
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
    $this->values = array();
    $this->bindValues = array();
    $prefix = $this->placeHolderPrefix;
    $suffix = $this->placeHolderSuffix;

    foreach ($values as $key => $value) {
      $this->values[$key] = $value;
      $key = $prefix . $key . $suffix;
      $this->bindValues[$key] = $value;
    }

    return $this;
  }

  public function sequenceColumn($seqColumn)
  {
    if ($seqColumn === null) {
      $this->seqColumn = null;
    } elseif (is_string($seqColumn)) {
      $this->seqColumn = $seqColumn;
    } else {
      throw new Sabel_DB_Sql_Exception("sequenceColumn() argument should be a string.");
    }

    return $this;
  }

  public function getPlaceHolderPrefix()
  {
    return $this->placeHolderPrefix;
  }

  public function getPlaceHolderSuffix()
  {
    return $this->placeHolderSuffix;
  }

  public function execute()
  {
    $query = $this->getQuery();

    if (empty($this->bindValues)) {
      $result = $this->driver->execute($query);
    } else {
      $bindValues = $this->escape($this->bindValues);
      $result = $this->driver->execute($query, $bindValues);
    }

    if ($this->isInsert() && $this->seqColumn !== null) {
      return $this->driver->getLastInsertId();
    }

    return $result;
  }

  public function setBindValue($key, $val)
  {
    $key = $this->placeHolderPrefix . $key . $this->placeHolderSuffix;
    $this->bindValues[$key] = $val;

    return $key;
  }

  public function setBindValues(array $values)
  {
    $prefix = $this->placeHolderPrefix;
    $suffix = $this->placeHolderSuffix;
    $binds =& $this->bindValues;

    foreach ($values as $key => $val) {
      $binds[$prefix . $key . $suffix] = $val;
    }
  }

  public function getBindValues()
  {
    return $this->bindValues;
  }

  public function isSelect()
  {
    return ($this->type === Sabel_DB_Sql::SELECT);
  }

  public function isInsert()
  {
    return ($this->type === Sabel_DB_Sql::INSERT);
  }

  public function isUpdate()
  {
    return ($this->type === Sabel_DB_Sql::UPDATE);
  }

  public function isDelete()
  {
    return ($this->type === Sabel_DB_Sql::DELETE);
  }

  public function build()
  {
    if ($this->schema === null) {
      $message = "can't build query. please call table() method.";
      throw new Sabel_DB_Sql_Exception($message);
    }

    switch ($this->type) {
      case Sabel_DB_Sql::SELECT:
        return $this->createSelectSql();

      case Sabel_DB_Sql::INSERT:
        return $this->createInsertSql();

      case Sabel_DB_Sql::UPDATE:
        return $this->createUpdateSql();

      case Sabel_DB_Sql::DELETE:
        return $this->createDeleteSql();

      case Sabel_DB_Sql::QUERY:
        return $this->query;
    }
  }

  protected function createSelectSql()
  {
    if (empty($this->projection)) {
      $projection = implode(", ", $this->schema->getColumnNames());
    } else {
      $projection = implode(", ", $this->projection);
    }

    $sql = "SELECT $projection FROM {$this->table}" . $this->join . $this->where;
    return $sql . $this->createConstraintSql();
  }

  protected function createInsertSql()
  {
    $binds  = array();
    $keys   = array_keys($this->values);
    $prefix = $this->placeHolderPrefix;
    $suffix = $this->placeHolderSuffix;

    foreach ($keys as $key) {
      $binds[] = $prefix . $key . $suffix;
    }

    $sql = array("INSERT INTO {$this->table} (");
    $sql[] = join(", ", $keys);
    $sql[] = ") VALUES(";
    $sql[] = join(", ", $binds);
    $sql[] = ")";

    return implode("", $sql);
  }

  protected function createUpdateSql()
  {
    $prefix = $this->placeHolderPrefix;
    $suffix = $this->placeHolderSuffix;

    $updates = array();
    foreach ($this->values as $column => $value) {
      $updates[] = "$column = {$prefix}{$column}{$suffix}";
    }

    return "UPDATE {$this->table} SET " . implode(", ", $updates) . $this->where;
  }

  protected function createDeleteSql()
  {
    return "DELETE FROM " . $this->table . $this->where;
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

  protected function escapeObject($instance)
  {
    if ($instance instanceof Sabel_DB_Sql_Part_Interface) {
      return $instance->getValue($this);
    } else {
      throw new Sabel_DB_Sql_Exception("cannot convert object to sql string.");
    }
  }
}
