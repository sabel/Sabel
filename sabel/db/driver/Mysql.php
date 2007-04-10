<?php

include_once("ArrayInsert.php");

/**
 * Sabel_DB_Driver_Mysql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver_Mysql extends Sabel_DB_Driver_Base
{
  protected $driverId        = "mysql";
  protected $execFunction    = "mysql_query";
  protected $closeFunction   = "mysql_close";
  protected $beginCommand    = "START TRANSACTION";
  protected $commitCommand   = "COMMIT";
  protected $rollbackCommand = "ROLLBACK";

  public function getAfterMethods()
  {
    return array("execute" => array("getResultSet"),
                 "insert"  => array("getIncrementId"));
  }

  public function escape($values)
  {
    if ($this->connection === null) {
      $this->connection = Sabel_DB_Connection::get($this->connectionName);
    }

    return escapeString("mysql", $values, "mysql_real_escape_string");
  }

  public function execute($connection = null)
  {
    $result = parent::execute($connection);

    if (!$result) {
      $error = mysql_error($this->connection);
      $sql   = substr($this->sql, 0, 128) . "...";
      throw new Exception("mysql_query execute failed: $sql ERROR: $error");
    }

    $rows = array();
    if (is_resource($result)) {
      while ($row = mysql_fetch_assoc($result)) $rows[] = $row;
    }

    return $this->result = $rows;
  }

  public function getIncrementId($command = null)
  {
    $id = Sabel_DB_Driver_Sequence::getId("mysql", $command);

    if ($command === null) {
      return $id;
    } else {
      $command->setIncrementId($id);
    }
  }
}
