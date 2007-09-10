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
abstract class Sabel_DB_Abstract_Statement
{
  protected
    $sql        = "",
    $model      = null,
    $driver     = null,
    $bindValues = array();

  protected
    $tblName     = "",
    $projection  = "*",
    $join        = "",
    $where       = "",
    $constraints = array(),
    $values      = array(),
    $seqColumn   = null;

  abstract public function getStatementType();
  abstract public function build();

  public function __construct(Sabel_DB_Abstract_Model $model)
  {
    $this->model  = $model;
    $this->driver = Sabel_DB_Driver::create($model->getConnectionName());
  }

  public function getModel()
  {
    return $this->model;
  }

  public function getDriver()
  {
    return $this->driver;
  }

  public function setSql($sql)
  {
    if (is_string($sql)) {
      $this->sql = $sql;
    } else {
      throw new Sabel_DB_Exception("setSql() argument should be a string.");
    }

    return $this;
  }

  public function getSql()
  {
    return ($this->hasSql()) ? $this->sql : $this->build();
  }

  public function hasSql()
  {
    return (is_string($this->sql) && $this->sql !== "");
  }

  public function table($table)
  {
    if (is_string($table)) {
      $this->table = $table;
    } else {
      throw new Sabel_DB_Exception("table() argument should be a string.");
    }
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
  }

  public function getWhere()
  {
    return $this->where;
  }

  public function constraints(array $constraints)
  {
    $this->constraints = $constraints;
  }

  public function getConstraints()
  {
    return $this->constraints;
  }

  public function values(array $values)
  {
    $this->values = $values;
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
  }

  public function getSequenceColumn()
  {
    return $this->seqColumn;
  }

  public function execute()
  {
    if ($this->hasSql()) {
      $result = $this->driver->execute($this->sql);
    } else {
      $sql = $this->build();
      $bindParams = $this->getBindParams();
      $result = $this->driver->execute($sql, $bindParams);
    }

    if ($this->isInsert() && $this->seqColumn !== null) {
      return $this->driver->getLastInsertId();
    }

    return $result;
  }

  public function setBindValues(array $bindValues)
  {
    $this->bindValues = $bindValues;
  }

  public function setBindValue($key, $val)
  {
    $this->bindValues[$key] = $val;
  }

  public function getBindParams()
  {
    if (empty($this->bindValues)) {
      return null;
    }

    $bindParams = array();
    foreach ($this->bindValues as $key => $value) {
      $bindParams[":{$key}"] = $value;
    }

    return $bindParams;
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
