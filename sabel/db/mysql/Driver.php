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
        $val = ($val) ? 1 : 0;
      } elseif (is_string($val)) {
        $val = "'" . mysql_real_escape_string($val, $conn) . "'";
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
    $result = mysql_query($sql, $conn);

    if (!$result) $this->executeError($sql);

    $rows = array();
    if (is_resource($result)) {
      while ($row = mysql_fetch_assoc($result)) $rows[] = $row;
      mysql_free_result($result);
    }

    return $this->result = $rows;
  }

  public function getIncrementId($executer)
  {
    if ($executer->getModel()->getIncrementColumn()) {
      $executer->setIncrementId($this->getSequenceId("SELECT last_insert_id() AS id"));
    }
  }

  private function executeError($sql)
  {
    $error   = mysql_error($this->connection);
    $message = "mysql driver execute failed: $error, SQL: $sql";
    throw new Sabel_DB_Exception($message);
  }
}
