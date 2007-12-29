<?php

/**
 * Sabel_DB_Ibase_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Ibase_Driver extends Sabel_DB_Abstract_Driver
{
  private $lastInsertId = null;

  public function getDriverId()
  {
    return "ibase";
  }

  public function connect(array $params)
  {
    $host = $params["host"]. ":" . $params["database"];
    $enc  = (isset($params["charset"])) ? $params["charset"] : null;
    $conn = ibase_connect($host, $params["user"], $params["password"], $enc);

    if ($conn) {
      return $conn;
    } else {
      return ibase_errmsg();
    }
  }

  public function begin()
  {
    $this->autoCommit(false);
    $this->connection = ibase_trans(IBASE_COMMITTED|IBASE_REC_NO_VERSION, $this->connection);

    return $this->connection;
  }

  public function commit()
  {
    if (ibase_commit($this->connection)) {
      $this->autoCommit(true);
    } else {
      throw new Sabel_DB_Driver_Exception("ibase driver commit failed.");
    }
  }

  public function rollback()
  {
    if (ibase_rollback($this->connection)) {
      $this->autoCommit(true);
    } else {
      throw new Sabel_DB_Driver_Exception("ibase driver rollback failed.");
    }
  }

  public function close($connection)
  {
    ibase_close($connection);
    unset($this->connection);
  }

  public function setLastInsertId($id)
  {
    $this->lastInsertId = $id;
  }

  public function execute($sql, $bindParams = null)
  {
    $sql = $this->bind($sql, $bindParams);
    $connection = $this->connection;
    $result = ibase_query($connection, $sql);
    if (!$result) $this->executeError($sql);

    $rows = array();
    if (is_resource($result)) {
      while ($row = ibase_fetch_assoc($result, IBASE_TEXT)) {
        $rows[] = array_change_key_case($row);
      }
      ibase_free_result($result);
    }

    if ($this->autoCommit) ibase_commit($connection);
    return (empty($rows)) ? null : $rows;
  }

  public function getLastInsertId()
  {
    return $this->lastInsertId;
  }

  private function executeError($sql)
  {
    $error   = ibase_errmsg();
    $message = "ibase driver execute failed: $error, SQL: $sql";
    throw new Sabel_DB_Driver_Exception($message);
  }
}
