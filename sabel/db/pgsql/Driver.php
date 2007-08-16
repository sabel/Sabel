<?php

/**
 * Sabel_DB_Pgsql_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pgsql_Driver extends Sabel_DB_Abstract_Driver
{
  public function getDriverId()
  {
    return "pgsql";
  }

  public function loadTransaction()
  {
    return Sabel_DB_Transaction_General::getInstance();
  }

  public function begin($connectionName = null)
  {
    if ($connectionName === null) {
      $connectionName = $this->connectionName;
    }

    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $this->connection = Sabel_DB_Connection::get($connectionName);
      if (!pg_query($this->connection, "START TRANSACTION")) {
        throw new Sabel_DB_Exception("pgsql driver begin failed.");
      }
      $trans->start($this->connection, $this);
    }
  }

  public function commit($connection)
  {
    if (!pg_query($connection, "COMMIT")) {
      throw new Sabel_DB_Exception("pgsql driver commit failed.");
    }
  }

  public function rollback($connection)
  {
    if (!pg_query($connection, "ROLLBACK")) {
      throw new Sabel_DB_Exception("pgsql driver rollback failed.");
    }
  }

  public function close($connection)
  {
    pg_close($connection);
    unset($this->connection);
  }

  public function escape($values)
  {
    $conn = $this->getConnection();

    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? "'t'" : "'f'";
      } elseif (is_string($val)) {
        $val = "'" . pg_escape_string($conn, $val) . "'";
      }
    }

    return $values;
  }

  public function execute(Sabel_DB_Abstract_Statement $stmt)
  {
    if (($bindParams = $stmt->getBindParams()) !== null) {
      $bindParams = $this->escape($bindParams);
    }

    $conn   = $this->getConnection();
    $sql    = $this->bind($stmt->getSql(), $bindParams);
    $result = pg_query($conn, $sql);

    if (!$result) $this->executeError($result, $sql);

    $rows = array();
    if (is_resource($result)) {
      $rows = pg_fetch_all($result);
      pg_free_result($result);
    }

    return (empty($rows)) ? null : $rows;
  }

  public function getLastInsertId()
  {
    $stmt = Sabel_DB_Statement::create($this, Sabel_DB_Statement::SELECT);
    $rows = $stmt->setSql("SELECT LASTVAL() AS id")->execute();
    return $rows[0]["id"];
  }

  private function executeError($result, $sql)
  {
    $error = pg_result_error($result);
    $message = "pgsql driver execute failed: $error, SQL: $sql";
    throw new Sabel_DB_Exception($message);
  }
}
