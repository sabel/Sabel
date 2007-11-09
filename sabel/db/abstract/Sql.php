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
    $bindValues = array();

  protected
    $table       = "",
    $projection  = "*",
    $join        = "",
    $where       = "",
    $values      = array(),
    $constraints = array(),
    $seqColumn   = null;

  protected
    $placeHolderPrefix = "@",
    $placeHolderSuffix = "@";

  public function __construct($connectionName = "default", $type = Sabel_DB_Sql::QUERY)
  {
    $this->type   = $type;
    $this->driver = Sabel_DB_Driver::create($connectionName);
    $this->connectionName = $connectionName;
  }

  public function setQuery($query)
  {
    if (is_string($query)) {
      $this->query = $query;
    } else {
      throw new Sabel_DB_Exception("setQuery() argument should be a string.");
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
      $this->table = $table;
    } else {
      throw new Sabel_DB_Exception("table() argument should be a string.");
    }

    return $this;
  }

  public function getTable()
  {
    return $this->table;
  }

  public function projection($projection)
  {
    if (is_string($projection)) {
      $this->projection = $projection;
    } else {
      throw new Sabel_DB_Exception("projection() argument should be a string.");
    }

    return $this;
  }

  public function getProjection()
  {
    return $this->projection;
  }

  public function join($join)
  {
    if (is_string($join)) {
      $this->join = $join;
    } else {
      throw new Sabel_DB_Exception("join() argument should be a string.");
    }

    return $this;
  }

  public function getJoin()
  {
    return $this->join;
  }

  public function where($where)
  {
    if (is_string($where)) {
      $this->where = $where;
    } else {
      throw new Sabel_DB_Exception("where() argument should be a string.");
    }

    return $this;
  }

  public function getWhere()
  {
    return $this->where;
  }

  public function constraints(array $constraints)
  {
    $this->constraints = $constraints;

    return $this;
  }

  public function getConstraints()
  {
    return $this->constraints;
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

  public function getValues()
  {
    return $this->values;
  }

  public function sequenceColumn($seqColumn)
  {
    if ($seqColumn === null) {
      $this->seqColumn = null;
    } elseif (is_string($seqColumn)) {
      $this->seqColumn = $seqColumn;
    } else {
      throw new Sabel_DB_Exception("sequenceColumn() argument should be a string.");
    }

    return $this;
  }

  public function getSequenceColumn()
  {
    return $this->seqColumn;
  }

  public function getPrefixOfPlaceHelder()
  {
    return $this->placeHolderPrefix;
  }

  public function getSuffixOfPlaceHelder()
  {
    return $this->placeHolderSuffix;
  }

  public function execute()
  {
    $result = $this->driver->execute($this->getQuery(), $this->bindValues);

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
    $sql = "SELECT {$this->projection} FROM {$this->table}" . $this->join . $this->where;
    return $sql . $this->createConstraintSql($this->constraints);
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

  protected function createConstraintSql($constraints)
  {
    $sql = "";

    if (isset($constraints["group"]))  $sql .= " GROUP BY " . $constraints["group"];
    if (isset($constraints["having"])) $sql .= " HAVING "   . $constraints["having"];
    if (isset($constraints["order"]))  $sql .= " ORDER BY " . $constraints["order"];

    if (isset($constraints["offset"]) && !isset($constraints["limit"])) {
      $sql .= " LIMIT 100 OFFSET " . $constraints["offset"];
    } else {
      if (isset($constraints["limit"]))  $sql .= " LIMIT "  . $constraints["limit"];
      if (isset($constraints["offset"])) $sql .= " OFFSET " . $constraints["offset"];
    }

    return $sql;
  }
}
