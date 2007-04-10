<?php

/**
 * Sabel_DB_Driver_Ibase
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver_Ibase extends Sabel_DB_Base_Driver
{
  protected $driverId      = "ibase";
  protected $execFunction  = "ibase_query";
  protected $closeFunction = "ibase_close";

  public function getBeforeMethods()
  {
    return array("execute" => array("prepareExecute"),
                 "insert"  => array("setIncrementId"));
  }

  public function getAfterMethods()
  {
    return array("execute" => array("getResultSet"),
                 "insert"  => array("getIncrementId"));
  }

  public function loadTransaction()
  {
    return Sabel_DB_Transaction_Ibase::getInstance();
  }

  public function begin($connectionName)
  {
    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $connection = Sabel_DB_Connection::get($connectionName);
      $resource = ibase_trans(IBASE_COMMITTED|IBASE_REC_NO_VERSION, $connection);
      $trans->begin($resource, $connectionName);
    }
  }

  public function prepareExecute($command)
  {
    // @todo
    /*
    $conn = $this->loadTransaction()->get($this->connectName);

    if ($conn === null) {
      $conn = $this->getConnection();
      $autoCommit = true;
    } else {
      $autoCommit = false;
    }
    */
  }

  public function execute($connection = null)
  {
    // @todo
    /*
    $result = parent::execute($connection);

    if (!$result) {
      $error = ibase_errmsg();
      $sql   = substr($this->sql, 0, 128) . " ...";
      throw new Exception("ibase_query execute failed: $sql ERROR: $error");
    }

    if ($autoCommit) ibase_commit($connection);

    $rows = array();
    if (is_resource($result)) {
      while ($row = ibase_fetch_assoc($result)) $rows[] = array_change_key_case($row);
    }

    return $this->result = $rows;
    */
  }

  public function setIncrementId($command)
  {
    $this->incrementId = Sabel_DB_Driver_Sequence::getId("ibase", $command);
  }

  public function getIncrementId($command = null)
  {
    if ($command === null) {
      return $this->incrementId;
    } else {
      $command->setIncrementId($this->incrementId);
    }
  }
}
