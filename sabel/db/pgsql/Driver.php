<?php

/**
 * Sabel_DB_Pgsql_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pgsql_Driver extends Sabel_DB_Abstract_Common_Driver
{
  protected $driverId        = "pgsql";
  protected $execFunction    = "pg_query";
  protected $closeFunction   = "pg_close";
  protected $beginCommand    = "START TRANSACTION";
  protected $commitCommand   = "COMMIT";
  protected $rollbackCommand = "ROLLBACK";

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
    return Sabel_DB_Sql_Constraint_Loader::load("Sabel_DB_Sql_Constraint_General");
  }

  public function loadTransaction()
  {
    return Sabel_DB_Transaction_General::getInstance();
  }

  public function getBeforeMethods()
  {
    return array(Sabel_DB_Command::INSERT => "insert");
  }

  public function escape($values)
  {
    return escapeString($this->driverId, $values, "pg_escape_string");
  }

  public function execute()
  {
    $result = parent::execute();

    if (!$result) {
      $error = pg_result_error($result);
      $this->error("pgsql driver execute failed: $error");
    }

    $rows = array();
    if (is_resource($result)) {
      $rows = pg_fetch_all($result);
      pg_free_result($result);
    }

    return $this->result = $rows;
  }

  public function insert($command)
  {
    $model   = $command->getModel();
    $tblName = $model->getTableName();
    $values  = $model->getSaveValues();
    $conn    = $this->getConnection();

    if (!$result = pg_insert($conn, $tblName, $values)) {
      $values = var_export($values, true);
      throw new Exception("pg_insert execute failed: '$tblName' VALUES: $values");
    }

    if ($model->getIncrementColumn()) {
      $command->setIncrementId($this->getSequenceId("SELECT LASTVAL() AS id"));
    } else {
      $command->setIncrementId(null);
    }

    return Sabel_DB_Command_Executer::SKIP;
  }
}
