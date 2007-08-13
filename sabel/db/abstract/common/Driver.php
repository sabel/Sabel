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
  public function begin($connectionName = null)
  {
    if ($connectionName === null) {
      $connectionName = $this->connectionName;
    }

    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $this->connection = Sabel_DB_Connection::get($connectionName);
      $this->execute($this->beginCommand);
      $trans->start($this->connection, $this);
    }
  }

  public function commit($connection)
  {
    $this->connection = $connection;
    $this->execute($this->commitCommand);
  }

  public function rollback($connection)
  {
    $this->connection = $connection;
    $this->execute($this->rollbackCommand);
  }
}
