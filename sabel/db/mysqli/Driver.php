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

  public function begin()
  {
    mysqli_autocommit($this->connection, false);
    return $this->connection;
  }

  public function commit()
  {
    if (mysqli_commit($this->connection)) {
      mysqli_autocommit($this->connection, true);
    } else {
      throw new Sabel_DB_Exception("mysqli driver commit failed.");
    }
  }

  public function rollback()
  {
    if (mysqli_rollback($this->connection)) {
      mysqli_autocommit($this->connection, true);
    } else {
      throw new Sabel_DB_Exception("mysqli driver rollback failed.");
    }
  }

  public function close($connection)
  {
    mysqli_close($connection);
    unset($this->connection);
  }

  public function escape(array $values)
  {
    $conn = $this->connection;

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

    $result = mysqli_query($this->connection, $sql);
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
