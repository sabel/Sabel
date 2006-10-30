<?php

/**
 * Sabel_DB_Driver_General
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @subpackage driver
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Driver_General
{
  protected
    $dbType   = '',
    $conn     = null,
    $query    = null,
    $insertId = null;

  public abstract function begin($conn);
  public abstract function commit($conn);
  public abstract function rollback($conn);
  public abstract function close($conn);
  public abstract function getResultSet();

  protected abstract function driverExecute($sql = null);

  public function extension($property){}

  public function getStatement()
  {
    return $this->query;
  }

  public function setBasicSQL($sql)
  {
    $this->query->setBasicSQL($sql);
  }

  public function setUpdateSQL($table, $data)
  {
    $this->query->makeUpdateSQL($table, $data);
  }

  public function executeInsert($table, $data, $defColumn)
  {
    if ($defColumn && ($this->dbType === 'pgsql' || $this->dbType === 'firebird'))
      $data = $this->setIdNumber($table, $data, $defColumn);

    $sql  = $this->query->makeInsertSQL($table, $data);
    $this->query->setBasicSQL($sql);

    return $this->driverExecute();
  }

  protected function setIdNumber($table, $data, $defColumn)
  {
    if (!isset($data[$defColumn])) {
      $this->driverExecute("SELECT nextval('{$table}_{$defColumn}_seq')");
      $resultSet = $this->getResultSet();
      $row = $resultSet->fetch(Sabel_DB_Driver_ResultSet::NUM);
      if (($this->lastInsertId = (int)$row[0]) === 0) {
        throw new Exception("{$table}_{$defColumn}_seq is not found.");
      } else {
        $data[$defColumn] = $this->lastInsertId;
      }
    }
    return $data;
  }

  public function makeQuery($conditions, $constraints = null)
  {
    $this->query->makeConditionQuery($conditions);
    if ($constraints) $this->query->makeConstraintQuery($constraints);
  }

  public function getLastInsertId()
  {
    return (isset($this->lastInsertId)) ? $this->lastInsertId : null;
  }

  public function execute($sql = null, $param = null)
  {
    if ($param) {
      foreach ($param as $key => $val) $param[$key] = $this->query->escape($val);
      $sql = vsprintf($sql, $param);
    }
    $this->driverExecute($sql);
    $this->query->unsetProperties();
  }
}
