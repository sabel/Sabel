<?php

/**
 * Sabel_DB_Mysql_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mysql_Driver extends Sabel_DB_Abstract_Driver
{
  public function getDriverId()
  {
    return "mysql";
  }

  public function getSqlBuilder($stmt)
  {
    return new Sabel_DB_Mysql_Sql($stmt);
  }

  public function begin()
  {
    $connection = $this->getConnection();
    if (mysql_query("START TRANSACTION", $connection)) {
      return $connection;
    } else {
      throw new Sabel_DB_Exception("mysql driver begin failed.");
    }
  }

  public function commit()
  {
    if (!mysql_query("COMMIT", $this->getConnection())) {
      throw new Sabel_DB_Exception("mysql driver commit failed.");
    }
  }

  public function rollback()
  {
    if (!mysql_query("ROLLBACK", $this->getConnection())) {
      throw new Sabel_DB_Exception("mysql driver rollback failed.");
    }
  }

  public function close($connection)
  {
    mysql_close($connection);
    unset($this->connection);
  }

  public function escape(array $values)
  {
    $conn = $this->getConnection();

    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      } elseif (is_string($val)) {
        $val = "'" . mysql_real_escape_string($val, $conn) . "'";
      }
    }

    return $values;
  }

  public function execute($sql, $bindParams = null)
  {
    if ($bindParams !== null) {
      $sql = $this->bind($sql, $this->escape($bindParams));
    }

    $result = mysql_query($sql, $this->getConnection());
    if (!$result) $this->executeError($sql);

    $rows = array();
    if (is_resource($result)) {
      while ($row = mysql_fetch_assoc($result)) $rows[] = $row;
      mysql_free_result($result);
    }

    return (empty($rows)) ? null : $rows;
  }

  public function getLastInsertId()
  {
    $rows = $this->execute("SELECT LAST_INSERT_ID() AS id");
    return $rows[0]["id"];
  }

  private function executeError($sql)
  {
    $error   = mysql_error($this->connection);
    $message = "mysql driver execute failed: $error, SQL: $sql";
    throw new Sabel_DB_Exception($message);
  }
}
