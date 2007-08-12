<?php

/**
 * Sabel_DB_Mysqli_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mysqli_Driver extends Sabel_DB_Abstract_Driver
{
  protected $driverId      = "mysqli";
  protected $closeFunction = "mysqli_close";

  public function loadSqlClass($model)
  {
    return Sabel_DB_Sql_Loader::load($model, "Sabel_DB_Sql_General");
  }

  public function loadConditionBuilder()
  {
    return Sabel_DB_Condition_Builder_Loader::load($this, "Sabel_DB_Condition_Builder_General");
  }

  public function loadConstraintSqlClass()
  {
    return Sabel_DB_Sql_Constraint_Loader::load("Sabel_DB_Sql_Constraint_General");
  }

  public function loadTransaction()
  {
    return Sabel_DB_Mysqli_Transaction::getInstance();
  }

  public function begin($connectionName = null)
  {
    if ($connectionName === null) {
      $connectionName = $this->connectionName;
    }

    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $connection = Sabel_DB_Connection::get($connectionName);
      mysqli_autocommit($connection, false);
      $trans->start($connection, $connectionName);
    }
  }

  public function getAfterMethods()
  {
    return array(Sabel_DB_Statement::INSERT => "getIncrementId");
  }

  public function escape($values)
  {
    if ($values === null) return "''";

    if ($this->connection === null) {
      $this->connection = Sabel_DB_Connection::get($this->connectionName);
    }

    $conn   = $this->connection;
    $values = escapeString($this->driverId, $values);
    if (!is_array($values)) $values = (array)$values;

    foreach ($values as &$value) {
      if (!is_string($value)) continue;
      $value = "'" . mysqli_real_escape_string($conn, $value) . "'";
    }

    if (isset($values[0]) && count($values) === 1) {
      return $values[0];
    } else {
      return $values;
    }
  }

  public function execute()
  {
    $conn = $this->getConnection();

    if (is_array($this->sql)) {
      foreach ($this->sql as $sql) {
        $result = mysqli_query($conn, $sql);
        if (!$result) break;
      }
    } else {
      $result = mysqli_query($conn, $this->sql);
    }

    if (!$result) {
      $error = mysqli_error($conn);
      $this->error("mysql driver execute failed: $error");
    }

    $rows = array();
    if (is_object($result)) {
      while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
      mysqli_free_result($result);
    }

    return $this->result = $rows;
  }

  public function getIncrementId($executer)
  {
    if ($executer->getModel()->getIncrementColumn()) {
      $executer->setIncrementId(mysqli_insert_id($this->connection));
    }
  }
}
