<?php

if (!defined("MQ_SYBASE")) {
  define("MQ_SYBASE", ini_get("magic_quotes_sybase"));
}

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

  public function loadSqlClass($model)
  {
    return Sabel_DB_Sql_Loader::load($model, "Sabel_DB_Sql_General");
  }

  public function loadConditionBuilder()
  {
    return Sabel_DB_Condition_Builder_Loader::load($this, "Sabel_DB_Condition_Builder_General");
  }

  public function loadConstraintSqlClass()
  {
    return Sabel_DB_Sql_Constraint_Loader::load("Sabel_DB_Sql_Constraint_Ibase");
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

  public function getBeforeMethods()
  {
    return array(Sabel_DB_Command::INSERT => "setIncrementId");
  }

  public function loadTransaction()
  {
    return Sabel_DB_Transaction_Ibase::getInstance();
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
    return escapeString("ibase", $values, "ibase_escape_string");
  }

  public function execute()
  {
    $result = parent::execute();

    if (!$result) {
      $error = ibase_errmsg();
      $this->error("ibase driver execute failed: $error");
    }

    $rows = array();
    if (is_resource($result)) {
      while ($row = ibase_fetch_assoc($result, IBASE_TEXT)) {
        $rows[] = array_change_key_case($row);
      }
      ibase_free_result($result);
    }

    if ($this->autoCommit) ibase_commit($this->connection);
    return $this->result = $rows;
  }

  public function setIncrementId($command)
  {
    $model = $command->getModel();
    if (($column = $model->getIncrementColumn()) === null) {
      return $command->setIncrementId(null);
    }

    $values = $model->getSaveValues();

    // @todo erase
    if (isset($values[$column])) {
      $command->setIncrementId(null);
    } else {
      $genName = $model->getTableName() . "_{$column}_gen";
      $values[$column] = ibase_gen_id($genName, 1, $this->getConnection());
      $model->setSaveValues($values);
      $command->setIncrementId($values[$column]);
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
