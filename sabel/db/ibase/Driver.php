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
class Sabel_DB_Ibase_Driver extends Sabel_DB_Abstract_Common_Driver
{
  protected $driverId      = "ibase";
  protected $execFunction  = "ibase_query";
  protected $closeFunction = "ibase_close";

  public function loadConstraintSqlClass()
  {
    return Sabel_DB_Sql_Constraint_Loader::load("Sabel_DB_Ibase_SqlConstraint");
  }

  public function loadTransaction()
  {
    return Sabel_DB_Ibase_Transaction::getInstance();
  }

  public function getConnection()
  {
    $connection = $this->loadTransaction()->get($this->getConnectionName());

    if ($connection === null) {
      $connection = parent::getConnection();
      $this->autoCommit = true;
    } else {
      $this->autoCommit = false;
    }

    return $this->connection = $connection;
  }

  public function getSequenceId(Sabel_DB_Model $model)
  {
    $column  = $model->getIncrementColumn();
    $genName = strtoupper($model->getTableName() . "_{$column}_gen");
    return ibase_gen_id($genName, 1, $this->getConnection());
  }

  public function begin($connectionName = null)
  {
    if ($connectionName === null) {
      $connectionName = $this->connectionName;
    }

    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $connection = Sabel_DB_Connection::get($connectionName);
      $resource = ibase_trans(IBASE_COMMITTED|IBASE_REC_NO_VERSION, $connection);
      $trans->start($resource, $connectionName);
    }
  }

  public function escape($values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      } elseif (is_string($val)) {
        $val = "'" . ibase_escape_string($val) . "'";
      }
    }

    return $values;
  }

  public function execute($sql, $bindParam = null)
  {
    if ($bindParam !== null) {
      $bindParam = $this->escape($bindParam);
    }

    $conn   = $this->getConnection();
    $sql    = $this->bind($sql, $bindParam);
    $result = ibase_query($conn, $sql);

    if (!$result) $this->executeError($sql);

    $rows = array();
    if (is_resource($result)) {
      while ($row = ibase_fetch_assoc($result, IBASE_TEXT)) {
        $rows[] = array_change_key_case($row);
      }
      ibase_free_result($result);
    }

    if ($this->autoCommit) ibase_commit($this->connection);
    return $rows;
  }

  private function executeError($sql)
  {
    $error   = ibase_errmsg();
    $message = "ibase driver execute failed: $error, SQL: $sql";
    throw new Sabel_DB_Exception($message);
  }
}

if (!defined("MQ_SYBASE")) {
  define("MQ_SYBASE", ini_get("magic_quotes_sybase"));
}

function ibase_escape_string($val)
{
  if (MQ_SYBASE) {
    return $val;
  } else {
    return str_replace("'", "''", $val);
  }
}
