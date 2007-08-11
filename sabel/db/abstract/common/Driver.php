<?php

/**
 * Sabel_DB_Abstract_Commoin_Driver
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Common_Driver extends Sabel_DB_Abstract_Driver
{
  public function execute()
  {
    $sql  = $this->sql;
    $conn = $this->getConnection();
    $func = $this->execFunction;

    switch ($this->driverId) {
      case "mysql":
      case "mssql":
        if (is_array($sql)) {
          foreach ($sql as $s) $func($s, $conn);
          return true;
        } else {
          return $func($sql, $conn);
        }

      case "mysqli":
      case "pgsql":
      case "ibase":
        if (is_array($sql)) {
          foreach ($sql as $s) $func($conn, $s);
          return true;
        } else {
          return $func($conn, $sql);
        }
    }
  }

  public function begin($connectionName = null)
  {
    if ($connectionName === null) {
      $connectionName = $this->connectionName;
    }

    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $connection = Sabel_DB_Connection::get($connectionName);
      $this->setSql($this->beginCommand)->execute($connection);
      $trans->start($connection, $this);
    }
  }

  public function commit($connection)
  {
    $this->connection = $connection;
    $this->setSql($this->commitCommand)->execute();
  }

  public function rollback($connection)
  {
    $this->connection = $connection;
    $this->setSql($this->rollbackCommand)->execute();
  }

  protected function getSequenceId($sql)
  {
    $rows = $this->setSql($sql)->execute();
    return (isset($rows[0]["id"])) ? (int)$rows[0]["id"] : null;
  }
}
