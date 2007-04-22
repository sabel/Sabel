<?php

if (!defined("MQ_SYBASE")) {
  define("MQ_SYBASE", ini_get("magic_quotes_sybase"));
}

/**
 * Sabel_DB_Driver_Ibase
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver_Ibase extends Sabel_DB_Driver_Base
{
  protected $driverId      = "ibase";
  protected $execFunction  = "ibase_query";
  protected $closeFunction = "ibase_close";

  public function getBeforeMethods()
  {
    return array("insert" => array("setIncrementId"));
  }

  public function getAfterMethods()
  {
    return array("insert" => array("getIncrementId"));
  }

  public function loadTransaction()
  {
    return Sabel_DB_Transaction_Ibase::getInstance();
  }

  public function getConstraintSqlClass($classType = null)
  {
    return parent::getConstraintSqlClass(Sabel_DB_Sql_Constraint_Loader::IBASE);
  }

  public function begin($connectionName)
  {
    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $connection = Sabel_DB_Connection::get($connectionName);
      $resource = ibase_trans(IBASE_COMMITTED|IBASE_REC_NO_VERSION, $connection);
      $trans->start($resource, $connectionName);
    }
  }

  public function escape($values)
  {
    return escapeString("ibase", $values, "ibase_escape_string");
  }

  public function execute($connection = null)
  {
    $autoCommit = true;

    if ($connection === null) {
      $connection = $this->loadTransaction()->get($this->getConnectionName());
      if ($connection === null) {
        $connection = $this->getConnection();
      } else {
        $autoCommit = false;
      }
    }

    $result = parent::execute($connection);

    if (!$result) {
      $error = ibase_errmsg();
      $sql   = substr($this->sql, 0, 128) . " ...";
      throw new Exception("ibase_query execute failed: $sql ERROR: $error");
    }

    $rows = array();
    if (is_resource($result)) {
      while ($row = ibase_fetch_assoc($result, IBASE_TEXT)) {
        $rows[] = array_change_key_case($row);
      }
      ibase_free_result($result);
    }

    if ($autoCommit) ibase_commit($connection);
    return $this->result = $rows;
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

function ibase_escape_string($val)
{
  if (MQ_SYBASE) {
    return $val;
  } else {
    return str_replace("'", "''", $val);
  }
}
