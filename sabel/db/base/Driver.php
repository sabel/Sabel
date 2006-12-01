<?php

/**
 * Sabel_DB_Base_Driver
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @subpackage base
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Base_Driver
{
  protected
    $conn = null,
    $stmt = null,
    $db   = '';

  protected
    $escMethod    = '',
    $lastInsertId = null;

  public abstract function begin($conn);
  public abstract function commit($conn);
  public abstract function rollback($conn);
  public abstract function close($conn);
  public abstract function getResultSet();

  protected abstract function driverExecute($sql = null);

  public function extension($property){}

  public function loadStatement()
  {
    $this->stmt = new Sabel_DB_Statement_NonBind($this->db, $this->escMethod);
    return $this->stmt;
  }

  public function update()
  {
    $this->driverExecute();
  }

  public function insert()
  {
    $this->driverExecute();
  }

  public function setIdNumber($table, $data, $defColumn)
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
    $this->stmt->makeConditionQuery($conditions);
    if ($constraints) $this->stmt->makeConstraintQuery($constraints);
  }

  public function getLastInsertId()
  {
    return (isset($this->lastInsertId)) ? $this->lastInsertId : null;
  }

  public function execute($sql = null, $param = null)
  {
    if ($param) {
      foreach ($param as $key => $val) $param[$key] = $this->stmt->escape($val);
      $sql = vsprintf($sql, $param);
    }

    $this->driverExecute($sql);
    if (isset($this->stmt)) $this->stmt->unsetProperties();
  }
}
