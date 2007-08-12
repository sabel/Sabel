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
    return array(Sabel_DB_Statement::INSERT => "getIncrementId");
  }

  public function escape($values)
  {
    $conn = $this->getConnection();

    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? "'t'" : "'f'";
      } elseif (is_string($val)) {
        $val = "'" . pg_escape_string($conn, $val) . "'";
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
    $result = pg_query($conn, $sql);

    if (!$result) $this->executeError($result);

    $rows = array();
    if (is_resource($result)) {
      $rows = pg_fetch_all($result);
      pg_free_result($result);
    }

    return $this->result = $rows;
  }

  public function getIncrementId($executer)
  {
    if (($column = $executer->getModel()->getIncrementColumn()) !== null) {
      $executer->setIncrementId($this->getSequenceId("SELECT LASTVAL() AS id"));
    } else {
      $executer->setIncrementId(null);
    }
  }

  private function executeError($result)
  {
    $error = pg_result_error($result);
    throw new Sabel_DB_Exception("pgsql driver execute failed: $error");
  }
}
