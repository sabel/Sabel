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
    $driver     = null,
    $sqlObject  = null,
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

  public function __construct(Sabel_DB_Abstract_Driver $driver)
  {
    $this->driver = $driver;
  }

  public function setSql($sql)
  {
    $this->sql = $sql;

    return $this;
  }

  public function getSql()
  {
    if ($this->hasSql()) {
      return $this->sql;
    } else {
      return $this->sql = $this->build();
    }
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
      throw new Sabel_DB_Exception("argument should be a string.");
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
      throw new Sabel_DB_Exception("argument should be a string.");
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
      throw new Sabel_DB_Exception("argument should be a string.");
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
      throw new Sabel_DB_Exception("argument should be a string.");
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
      throw new Sabel_DB_Exception("argument should be a string.");
    }
  }

  public function getSequenceColumn()
  {
    return $this->seqColumn;
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

  public function execute()
  {
    if (!$this->hasSql()) $this->build();
    $result = $this->driver->execute($this);

    if ($this->isInsert() && $this->seqColumn !== null) {
      return $this->driver->getLastInsertId();
    }

    return $result;
  }

  public function setBind($bindValues, $add = true)
  {
    if ($add) {
      foreach ($bindValues as $key => $val) {
        $this->bindValues[$key] = $val;
      }
    } else {
      $this->bindValues = $bindValues;
    }
  }

  public function getBindParams()
  {
    if (empty($this->bindValues)) {
      return null;
    }

    $bindParam = array();
    foreach ($this->bindValues as $key => $value) {
      $bindParam[":{$key}"] = $value;
    }

    $this->bindValues = array();
    return $bindParam;
  }

  public function build()
  {
    if ($this->isSelect()) {
      $sql = $this->driver->createSelectSql($this);
    } elseif ($this->isInsert()) {
      $sql = $this->driver->createInsertSql($this);
    } elseif ($this->isUpdate()) {
      $sql = $this->driver->createUpdateSql($this);
    } elseif ($this->isDelete()) {
      $sql = $this->driver->createDeleteSql($this);
    } else {
      throw new Sabel_DB_Exception("invalid statement type.");
    }

    return $this->sql = $sql;
  }
}
