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

  public function begin()
  {
    if (!pg_query($this->connection, "START TRANSACTION")) {
      throw new Sabel_DB_Exception("pgsql driver begin failed.");
    } else {
      return $this->connection;
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

  public function escape(array $values)
  {
    $conn = $this->connection;

    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? "'t'" : "'f'";
      } elseif (is_string($val)) {
        $val = "'" . pg_escape_string($conn, $val) . "'";
      }
    }

    return $values;
  }

  public function execute($sql, $bindParams = null)
  {
    if ($bindParams !== null) {
      $sql = $this->bind($sql, $this->escape($bindParams));
    }

    $result = pg_query($this->connection, $sql);
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
    $rows = $this->execute("SELECT LASTVAL() AS id");
    return $rows[0]["id"];
  }

  private function executeError($result, $sql)
  {
    $error = pg_result_error($result);
    $message = "pgsql driver execute failed: $error, SQL: $sql";
    throw new Sabel_DB_Exception($message);
  }
}
