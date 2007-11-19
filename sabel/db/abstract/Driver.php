<?php

/**
 * Sabel_DB_Abstract_Driver
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Driver extends Sabel_Object
{
  protected
    $autoCommit = true,
    $connection = null,
    $connectionName = "";

  abstract public function getDriverId();
  abstract public function connect(array $params);
  abstract public function begin();
  abstract public function commit();
  abstract public function rollback();
  abstract public function execute($sql, $bindParams = null);
  abstract public function getLastInsertId();
  abstract public function close($connection);

  public function __construct($connectionName)
  {
    $this->connectionName = $connectionName;
  }

  public function setConnection($connection)
  {
    $this->connection = $connection;
  }

  public function getConnection()
  {
    if ($this->connection === null) {
      return $this->connection = Sabel_DB_Connection::get($this);
    } else {
      return $this->connection;
    }
  }

  public function autoCommit($bool)
  {
    $this->autoCommit = $bool;
  }

  public function getConnectionName()
  {
    return $this->connectionName;
  }

  protected function bind($sql, $bindParam)
  {
    if (empty($bindParam)) return $sql;

    if (in_array(null, $bindParam, true)) {
      foreach ($bindParam as $key => $val) {
        $val = ($val === null) ? "NULL" : $val;
        $sql = str_replace($key, $val, $sql);
      }
      return $sql;
    } else {
      return str_replace(array_keys($bindParam), $bindParam, $sql);
    }
  }
}
