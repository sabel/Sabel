<?php

/**
 * Sabel_DB_Mssql_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mssql_Driver extends Sabel_DB_Abstract_Driver
{
  protected $driverId        = "mssql";
  protected $execFunction    = "mssql_query";
  protected $closeFunction   = "mssql_close";
  protected $beginCommand    = "BEGIN TRANSACTION";
  protected $commitCommand   = "COMMIT TRANSACTION";
  protected $rollbackCommand = "ROLLBACK TRANSACTION";

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
    return Sabel_DB_Sql_Constraint_Loader::load("Sabel_DB_Mssql_SqlConstraint");
  }

  public function loadTransaction()
  {
    return Sabel_DB_Transaction_General::getInstance();
  }

  public function getAfterMethods()
  {
    return array(Sabel_DB_Command::INSERT => "getIncrementId");
  }

  public function escape($values)
  {
    return escapeString($this->driverId, $values, "mssql_escape_string");
  }

  public function execute($connection = null)
  {
    $result = parent::execute($connection);

    if (!$result) {
      $error = mssql_get_last_message();
      $this->error("mssql driver execute failed: $error");
    }

    $rows = array();
    if (is_resource($result)) {
      while ($row = mssql_fetch_assoc($result)) $rows[] = $row;
      mssql_free_result($result);
    }

    return $this->result = $rows;
  }

  public function getIncrementId($command)
  {
    if ($command->getModel()->getIncrementColumn()) {
      $command->setIncrementId($this->getSequenceId("SELECT SCOPE_IDENTITY() AS id"));
    }
  }
}

function mssql_escape_string($val)
{
  return str_replace("'", "''", $val);
}
