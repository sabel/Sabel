<?php

/**
 * Sabel_DB_Transaction_General
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Transaction_General
{
  private static $ins = null;
  protected $active = false;
  protected $transactions = array();

  private function __construct() {}

  public static function getInstance()
  {
    if (self::$ins === null) {
      self::$ins = new self();
    }

    return self::$ins;
  }

  public function isActive($connectionName = null)
  {
    if ($connectionName === null) {
      return $this->active;
    } else {
      return (isset($this->transactions[$connectionName]));
    }
  }

  public function start($connection, $driver)
  {
    $connectionName = $driver->getConnectionName();
    $this->transactions[$connectionName]["conn"]   = $connection;
    $this->transactions[$connectionName]["driver"] = $driver;
    $this->active = true;
  }

  public function getConnection($connectionName)
  {
    $ts = $this->transactions;
    return (isset($ts[$connectionName]["conn"])) ? $ts[$connectionName]["conn"] : null;
  }

  public function commit()
  {
    $this->executeMethod("commit");
  }

  public function rollback()
  {
    $this->executeMethod("rollback");
  }

  private function executeMethod($method)
  {
    if ($this->isActive()) {
      foreach ($this->transactions as $trans) {
        $trans["driver"]->$method($trans["conn"]);
      }
      $this->clear();
    }
  }

  public function clear()
  {
    $this->active = false;
    $this->transactions = array();
  }
}
