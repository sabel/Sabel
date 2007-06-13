<?php

/**
 * Sabel_DB_Driver_Common
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver_Common extends Sabel_DB_Driver_Base
{
  public function execute($conn = null)
  {
    $sql = $this->sql;

    // @todo
    //if (defined("QUERY_LOG") && ENVIRONMENT === DEVELOPMENT) {
    if (defined("QUERY_LOG")) {
      var_dump($sql);
    }

    if ($conn === null) {
      $conn = $this->getConnection();
    }

    $func = $this->execFunction;

    switch ($this->driverId) {
      case "mysql":
      case "mysqli":
      case "mssql":
        if (is_array($sql)) {
          foreach ($sql as $s) $func($s, $conn);
          return true;
        } else {
          return $func($sql, $conn);
        }

      case "pgsql":
      case "ibase":
        if (is_array($sql)) {
          foreach ($sql as $s) $func($conn, $s);
          return true;
        } else {
          return $func($conn, $sql);
        }
    }
  }

  public function getSqlClass($model)
  {
    return Sabel_DB_Sql_Loader::getClass($model, Sabel_DB_Sql_Loader::COMMON);
  }

  public function getConditionBuilder()
  {
    return Sabel_DB_Condition_Builder_Loader::getClass($this, Sabel_DB_Condition_Builder_Loader::COMMON);
  }

  public function getConstraintSqlClass()
  {
    return Sabel_DB_Sql_Constraint_Loader::getClass(Sabel_DB_Sql_Constraint_Loader::COMMON);
  }

  public function begin($connectionName)
  {
    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $connection = Sabel_DB_Connection::get($connectionName);
      $this->setSql($this->beginCommand)->execute($connection);
      $trans->start($connection, $this);
    }
  }

  public function commit($connection)
  {
    $this->setSql($this->commitCommand)->execute($connection);
  }

  public function rollback($connection)
  {
    $this->setSql($this->rollbackCommand)->execute($connection);
  }

  public function close($connection)
  {
    $method = $this->closeFunction;
    $method($connection);

    unset($this->connection);
  }

  protected function getSequence()
  {
    return Sabel_DB_Driver_Sequence::getId($this->driverId, $this);
  }

  protected function error($error)
  {
    $message = array();
    $name    = $this->connectionName;
    $params  = Sabel_DB_Config::get($name);

    $message["ERROR_MESSAGE"]   = $error;
    $message["EXECUTE_QUERY"]   = $this->sql;
    $message["CONNECTION_NAME"] = $name;
    $message["PARAMETERS"]      = $params;

    throw new Sabel_DB_Exception(print_r($message, true));
  }
}
