<?php

/**
 * Sabel_DB_Mysql_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mysql_Driver extends Sabel_DB_Abstract_Common_Driver
{
  protected $driverId        = "mysql";
  protected $execFunction    = "mysql_query";
  protected $closeFunction   = "mysql_close";
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
