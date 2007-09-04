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
  public function getDriverId()
  {
    return "mysqli";
  }

  public function begin($connectionName = null)
  {
    if ($connectionName === null) {
      $connectionName = $this->connectionName;
    } else {
      $this->setConnectionName($connectionName);
    }

    $connection = $this->getConnection();
    mysqli_autocommit($connection, false);

    return $connection;
  }

  public function commit($connection)
  {
    if (!mysqli_commit($connection)) {
      throw new Sabel_DB_Exception("mysqli driver commit failed.");
    }

    mysqli_autocommit($connection, true);
  }

  public function rollback($connection)
  {
    if (!mysqli_rollback($connection)) {
      throw new Sabel_DB_Exception("mysqli driver rollback failed.");
    }

    mysqli_autocommit($connection, true);
  }

  public function close($connection)
  {
    mysqli_close($connection);
    unset($this->connection);
  }

  public function escape(array $values)
  {
    $conn = $this->getConnection();

    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      } elseif (is_string($val)) {
        $val = "'" . mysqli_real_escape_string($conn, $val) . "'";
      }
    }

    return $values;
  }

  public function execute($sql, $bindParams = null)
  {
    if ($bindParams !== null) {
      $sql = $this->bind($sql, $this->escape($bindParams));
    }

    $conn   = $this->getConnection();
    $result = mysqli_query($conn, $sql);

    if (!$result) $this->executeError($sql);

    $rows = array();
    if (is_object($result)) {
      while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
      mysqli_free_result($result);
    }

    return (empty($rows)) ? null : $rows;
  }

  public function getLastInsertId()
  {
    return mysqli_insert_id($this->connection);
  }

  private function executeError($sql)
  {
    $error   = mysqli_error($this->connection);
    $message = "mysqli driver execute failed: $error, SQL: $sql";
    throw new Sabel_DB_Exception($message);
  }
}
