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
class Sabel_DB_Mysql_Driver extends Sabel_DB_Driver_Base
{
  protected $driverId     = "mysql";
  protected $execFunction = "mysql_query";

  public function getAfterMethods()
  {
    return array("execute" => array("getResultSet"),
                 "insert"  => array("getIncrementId"));
  }

  public function begin($connectionName)
  {
    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $connection = Sabel_DB_Connection::get($connectionName);
      $this->execute("START TRANSACTION", $connection);
      $trans->start($connection, $this);
    }
  }

  public function commit($connection)
  {
    $this->execute("COMMIT", $connection);
  }

  public function rollback($connection)
  {
    $this->execute("ROLLBACK", $connection);
  }

  public function close($connection)
  {
    mysql_close($connection);
  }

  public function escape($values)
  {
    if ($this->connection === null) {
      $this->connection = Sabel_DB_Connection::get($this->connectionName);
    }
    return array_map("mysql_real_escape_string", $values);
  }

  public function query($sql)
  {
    $result = $this->execute($sql);

    if (!$result) {
      $error = mysql_error($this->connection);
      $sql   = substr($sql, 0, 128) . "...";
      throw new Exception("mysql_query execute failed: $sql ERROR: $error");
    }

    $rows = array();
    //if (is_resource($result)) {
      while ($row = mysql_fetch_assoc($result)) $rows[] = $row;
    //}

    return $this->result = $rows;
  }

  public function getIncrementId($command = null)
  {
    $id = getMysqlIncrementId($this);
    if ($command === null) return $id;

    $command->setIncrementId($id);
  }
}
