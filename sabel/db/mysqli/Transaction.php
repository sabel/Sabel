<?php

/**
 * Sabel_DB_Mysqli_Transaction
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mysqli_Transaction extends Sabel_DB_Abstract_Transaction
{
  private static $ins = null;

  public static function getInstance()
  {
    if (self::$ins === null) {
      parent::registInstance(self::$ins = new self());
    }

    return self::$ins;
  }

  public function start($connection, $connectionName)
  {
    $this->transactions[$connectionName] = $connection;
    $this->active = true;
  }

  public function get($connectionName)
  {
    $ts = $this->transactions;
    return (isset($ts[$connectionName])) ? $ts[$connectionName] : null;
  }

  public function commit()
  {
    $this->executeMethod("mysqli_commit");
  }

  public function rollback()
  {
    $this->executeMethod("mysqli_rollback");
  }

  private function executeMethod($method)
  {
    if ($this->isActive()) {
      foreach ($this->transactions as $trans) {
        $method($trans);
        mysqli_autocommit($trans, true);
      }
      $this->clear();
    }
  }
}