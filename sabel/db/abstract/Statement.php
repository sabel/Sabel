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
    $query      = "",
    $driver     = null,
    $sql        = null,
    $bindValues = array();

  protected
    $tblName     = "",
    $projection  = "*",
    $join        = "",
    $where       = "",
    $values      = array(),
    $constraints = array(),
    $seqColumn   = null;

  protected
    $phPrefix = ":",
    $phSuffix = "";

  abstract public function getStatementType();
  abstract public function build();

  public function __construct(Sabel_DB_Abstract_Driver $driver)
  {
    $this->driver     = $driver;
    $this->sql        = $driver->getSqlBuilder($this);
    $this->phPrefix   = $this->sql->getPrefixOfPlaceHelder();
    $this->phSuffix   = $this->sql->getSuffixOfPlaceHelder();
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

    foreach ($values as $key => $value) {
      $this->values[$key] = $value;
      $key = $this->phPrefix . $key . $this->phSuffix;
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
    $key = $this->phPrefix . $key . $this->phSuffix;
    $this->bindValues[$key] = $val;

    return $key;
  }

  public function getBindValues()
  {
    return $this->bindValues;
  }

  public function isSelect()
  {
    return ($this->getStatementType() === Sabel_DB_Statement::SELECT);
  }

  public function isInsert()
  {
    return ($this->getStatementType() === Sabel_DB_Statement::INSERT);
  }

  public function isUpdate()
  {
    return ($this->getStatementType() === Sabel_DB_Statement::UPDATE);
  }

  public function isDelete()
  {
    return ($this->getStatementType() === Sabel_DB_Statement::DELETE);
  }
}
