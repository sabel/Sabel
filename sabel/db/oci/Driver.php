<?php

/**
 * Sabel_DB_Oci_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Oci_Driver_Oci extends Sabel_DB_Abstract_Driver
{
  protected
    $driverId      = "oci",
    $closeFunction = "oci_close";

  private
    $execMode = OCI_COMMIT_ON_SUCCESS;

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
    return Sabel_DB_Sql_Constraint_Loader::load("Sabel_DB_Oci_SqlConstraint");
  }

  public function loadTransaction()
  {
    return Sabel_DB_Oci_Transaction::getInstance();
  }

  public function getConnection()
  {
    $connection = $this->loadTransaction()->get($this->connectionName);

    if ($connection === null) {
      $connection     = parent::getConnection();
      $this->execMode = OCI_COMMIT_ON_SUCCESS;
    } else {
      $this->execMode = OCI_DEFAULT;
    }

    return $this->connection = $connection;
  }

  public function getBeforeMethods()
  {
    return array("all" => "setConstraints", Sabel_DB_Command::INSERT => "setIncrementId");
  }

  public function setConstraints($command)
  {
    $c = $command->getModel()->getConstraints();

    if (isset($c["limit"]))  $this->limit  = $c["limit"];
    if (isset($c["offset"])) $this->offset = $c["offset"];
  }

  // @todo
  public function setLimit($limit)
  {
    $this->limit = $limit;
  }

  // @todo
  public function setOffset($offset)
  {
    $this->offset = $offset;
  }

  public function begin($connectionName = null)
  {
    if ($connectionName === null) {
      $connectionName = $this->connectionName;
    }

    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $connection = Sabel_DB_Connection::get($connectionName);
      $trans->start($connection, $connectionName);
    }
  }

  public function escape($values)
  {
    return escapeString($this->driverId, $values, "oci_escape_string");
  }

  public function execute()
  {
    $stmt   = oci_parse($this->getConnection(), $this->sql);
    $result = oci_execute($stmt, $this->execMode);

    if (!$result) {
      $error = oci_error($stmt);
      $this->error("oci driver execute failed: {$error["message"]}");
    }

    if (oci_statement_type($stmt) === "SELECT") {
      oci_fetch_all($stmt, $rows, $this->offset, $this->limit, OCI_ASSOC|OCI_FETCHSTATEMENT_BY_ROW);
      $rows = array_map("array_change_key_case", $rows);
    } else {
      $rows = array();
    }

    oci_free_statement($stmt);
    $this->limit = $this->offset = null;

    return $this->result = $rows;
  }

  public function setIncrementId($command)
  {
    $model = $command->getModel();
    if (($column = $model->getIncrementColumn()) === null) {
      return $command->setIncrementId(null);
    }

    $values = $model->getSaveValues();

    $seqName = strtoupper($model->getTableName() . "_{$column}_seq");
    $rows = $this->setSql("SELECT {$seqName}.nextval AS id FROM dual")->execute();
    $values[$column] = (int)$rows[0]["id"];
    $model->setSaveValues($values);
    $command->setIncrementId($values[$column]);
  }
}

function oci_escape_string($val)
{
  return str_replace("'", "''", $val);
}
