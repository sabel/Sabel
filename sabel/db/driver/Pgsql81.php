<?php

/**
 * Sabel_DB_Driver_Pgsql81
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver_Pgsql81 extends Sabel_DB_Driver_Base
{
  protected $driverId        = "pgsql81";
  protected $execFunction    = "pg_query";
  protected $closeFunction   = "pg_close";
  protected $beginCommand    = "START TRANSACTION";
  protected $commitCommand   = "COMMIT";
  protected $rollbackCommand = "ROLLBACK";

  public function getBeforeMethods()
  {
    return array("insert" => array("setIncrementId"));
  }

  public function getAfterMethods()
  {
    return array("execute" => array("getResultSet"),
                 "insert"  => array("getIncrementId"));
  }

  public function escape($values)
  {
    return escapeString("pgsql", $values, "pg_escape_string");
  }

  public function execute($connection = null)
  {
    $result = parent::execute($connection);

    if (!$result) {
      $error = pg_result_error($result);
      $sql   = substr($this->sql, 0, 128) . "...";
      throw new Exception("pg_query execute failed: $sql ERROR: $error");
    }

    $rows = array();
    if (is_resource($result)) $rows = pg_fetch_all($result);

    return $this->result = $rows;
  }

  public function setIncrementId($command)
  {
    $this->incrementId = Sabel_DB_Driver_Sequence::getId("pgsql", $command);
  }

  public function getIncrementId($command)
  {
    if ($command === null) {
      return $this->incrementId;
    } else {
      $command->setIncrementId($this->incrementId);
    }
  }
}
