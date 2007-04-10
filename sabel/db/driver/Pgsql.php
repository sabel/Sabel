<?php

/**
 * Sabel_DB_Driver_Pgsql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver_Pgsql extends Sabel_DB_Driver_Pgsql81
{
  protected $driverId = "pgsql";

  public function getBeforeMethods()
  {
    return array("insert" => array("setIncrementId"));
  }

  public function getAfterMethods()
  {
    return array("insert" => array("getIncrementId"));
  }

  public function escape($values)
  {
    // @todo
    // pg_convert()
    // return escapeString("pgsql", $values, "pg_escape_string");
  }

  public function execute($connection = null)
  {
    $result = parent::execute($connection);

    if (!$result) {
      $error = pg_result_error($result);
      $sql   = substr($this->sql, 0, 128) . "...";
      throw new Exception("pg_query execute failed: $sql ERROR: $error");
    }

    $this->createResult($result);
  }

  protected function createResult($resource)
  {
    $rows = array();
    if (is_resource($resource)) $rows = pg_fetch_all($resource);

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
