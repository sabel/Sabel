<?php

/**
 * Sabel_DB_Driver_Mssql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver_Mssql extends Sabel_DB_Driver_Base
{
  protected $driverId        = "mssql";
  protected $execFunction    = "mssql_query";
  protected $closeFunction   = "mssql_close";
  protected $beginCommand    = "BEGIN TRANSACTION";
  protected $commitCommand   = "COMMIT TRANSACTION";
  protected $rollbackCommand = "ROLLBACK TRANSACTION";

  public function getAfterMethods()
  {
    return array("insert" => array("getIncrementId"));
  }

  public function escape($values)
  {
    return escapeString("mssql", $values, "mssql_escape_string");
  }

  public function getConstraintSqlClass($classType = null)
  {
    return parent::getConstraintSqlClass(Sabel_DB_Sql_Constraint_Loader::MSSQL);
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

  public function getIncrementId($command = null)
  {
    $id = Sabel_DB_Driver_Sequence::getId("mssql", $command);

    if ($command === null) {
      return $id;
    } else {
      $command->setIncrementId($id);
    }
  }
}

function mssql_escape_string($val)
{
  return str_replace("'", "''", $val);
}
