<?php

/**
 * Sabel_DB_Transaction_Common
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Transaction_Common extends Sabel_DB_Transaction_Base
{
  public static function getInstance()
  {
    if (self::$ins === null) self::$ins = new self();
    return self::$ins;
  }

  public function start($connection, $driver)
  {
    $connectionName = $driver->getConnectionName();
    $this->transactions[$connectionName]["conn"]   = $connection;
    $this->transactions[$connectionName]["driver"] = $driver;
    $this->active = true;
  }

  public function commit()
  {
    $this->executeMethod('commit');
  }

  public function rollback()
  {
    $this->executeMethod('rollback');
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
}
