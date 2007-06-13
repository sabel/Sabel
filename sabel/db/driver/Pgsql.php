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
class Sabel_DB_Driver_Pgsql extends Sabel_DB_Driver_Common
{
  protected $driverId        = "pgsql";
  protected $execFunction    = "pg_query";
  protected $closeFunction   = "pg_close";
  protected $beginCommand    = "START TRANSACTION";
  protected $commitCommand   = "COMMIT";
  protected $rollbackCommand = "ROLLBACK";

  public function getBeforeMethods()
  {
    return array("insert" => array("insert"));
  }

  public function escape($values)
  {
    return escapeString("pgsql", $values, "pg_escape_string");
  }

  public function execute($connection = null)
  {
    $result = parent::execute($connection);

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
      $command->setIncrementId($this->getSequence());
    } else {
      $command->setIncrementId(null);
    }

    return Sabel_DB_Command_Executer::SKIP;
  }
}
