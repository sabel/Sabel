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

  public function loadTransaction()
  {
    return Sabel_DB_Transaction_General::getInstance();
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

  public function execute(Sabel_DB_Abstract_Statement $stmt)
  {
    if (($bindParams = $stmt->getBindParams()) !== null) {
      $bindParams = $this->escape($bindParams);
    }

    $conn   = $this->getConnection();
    $sql    = $this->bind($stmt->getSql(), $bindParams);
    $result = mysql_query($sql, $conn);

    if (!$result) $this->executeError($sql);

    $rows = array();
    if (is_resource($result)) {
      while ($row = mysql_fetch_assoc($result)) $rows[] = $row;
      mysql_free_result($result);
    }

    return (empty($rows)) ? null : $rows;
  }

  public function getLastInsertId()
  {
    $stmt = Sabel_DB_Statement::create(Sabel_DB_Statement::SELECT, $this);
    $rows = $stmt->setSql("SELECT LAST_INSERT_ID() AS id")->execute();
    return $rows[0]["id"];
  }

  private function executeError($sql)
  {
    $error   = mysql_error($this->connection);
    $message = "mysql driver execute failed: $error, SQL: $sql";
    throw new Sabel_DB_Exception($message);
  }
}
