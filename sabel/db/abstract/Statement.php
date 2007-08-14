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
    $manager     = null,
    $constraints = array(),
    $saveValues  = array(),
    $seqColumn   = "";

  abstract public function getStatementType();

  public function __construct(Sabel_DB_Abstract_Driver $driver)
  {
    $this->driver = $driver;
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

  public function setSqlObject(Sabel_DB_Sql_Object $object)
  {
    $this->sqlObject = $object;

    return $this;
  }

  public function setSql($sql)
  {
    $this->sql = $sql;

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

  public function execute()
  {
    $result = $this->driver->execute($this);
    $object = $this->sqlObject;

    if ($this->isInsert() && is_object($object) && $object->sequenceColumn !== null) {
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
      return $this->driver->createSelectSql($this->sqlObject);
    } elseif ($this->isInsert()) {
      return $this->driver->createInsertSql($this->sqlObject);
    } elseif ($this->isUpdate()) {
      return $this->driver->createUpdateSql($this->sqlObject);
    } elseif ($this->isDelete()) {
      return $this->driver->createDeleteSql($this->sqlObject);
    } else {
      throw new Sabel_DB_Exception("invalid statement type.");
    }
  }
}
