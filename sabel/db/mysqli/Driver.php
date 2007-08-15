<?php

/**
 * Sabel_DB_Mysqli_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mysqli_Driver extends Sabel_DB_Abstract_Driver
{
  protected $closeFunction = "mysqli_close";

  public function getDriverId()
  {
    return "mysqli";
  }

  public function loadTransaction()
  {
    return Sabel_DB_Mysqli_Transaction::getInstance();
  }

  public function begin($connectionName = null)
  {
    if ($connectionName === null) {
      $connectionName = $this->connectionName;
    }

    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $connection = Sabel_DB_Connection::get($connectionName);
      mysqli_autocommit($connection, false);
      $trans->start($connection, $connectionName);
    }
  }

  public function escape($values)
  {
    $conn = $this->getConnection();

    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      } elseif (is_string($val)) {
        $val = "'" . mysqli_real_escape_string($conn, $val) . "'";
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
    $result = mysqli_query($conn, $sql);

    if (!$result) $this->executeError($sql);

    $rows = array();
    if (is_object($result)) {
      while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
      mysqli_free_result($result);
    }

    return (empty($rows)) ? null : $rows;
  }

  public function getLastInsertId()
  {
    return mysqli_insert_id($this->connection);
  }

  private function executeError($sql)
  {
    $error   = mysqli_error($this->connection);
    $message = "mysqli driver execute failed: $error, SQL: $sql";
    throw new Sabel_DB_Exception($message);
  }
}
