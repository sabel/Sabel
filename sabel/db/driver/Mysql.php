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
class Sabel_DB_Driver_Mysql extends Sabel_DB_Driver_Common
{
  protected $driverId        = "mysql";
  protected $execFunction    = "mysql_query";
  protected $closeFunction   = "mysql_close";
  protected $beginCommand    = "START TRANSACTION";
  protected $commitCommand   = "COMMIT";
  protected $rollbackCommand = "ROLLBACK";

  public function getAfterMethods()
  {
    return array(Sabel_DB_Command::INSERT => "getIncrementId");
  }

  public function escape($values)
  {
    if ($this->connection === null) {
      $this->connection = Sabel_DB_Connection::get($this->connectionName);
    }

    return escapeString($this->driverId, $values, "mysql_real_escape_string");
  }

  public function execute()
  {
    $result = parent::execute();

    if (!$result) {
      $error = mysql_error($this->connection);
      $this->error("mysql driver execute failed: $error");
    }

    $rows = array();
    if (is_resource($result)) {
      while ($row = mysql_fetch_assoc($result)) $rows[] = $row;
      mysql_free_result($result);
    }

    return $this->result = $rows;
  }

  public function getIncrementId($command)
  {
    if ($command->getModel()->getIncrementColumn()) {
      $command->setIncrementId($this->getSequenceId("SELECT last_insert_id() AS id"));
    }
  }
}
