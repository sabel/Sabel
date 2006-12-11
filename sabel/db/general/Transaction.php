<?php

/**
 * Sabel_DB_General_Transaction
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage general
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_General_Transaction
{
  private static $ins = null;

  private
    $active       = false,
    $transactions = array();

  private function __construct() { }

  public static function getInstance()
  {
    if (self::$ins === null) {
      self::$ins = new self();
    }
    return self::$ins;
  }

  public function begin($driver, $conName = 'default')
  {
    if (!isset($this->transactions[$conName])) {
      $conn = Sabel_DB_Connection::getConnection($conName);
      $this->transactions[$conName]['conn']   = $conn;
      $this->transactions[$conName]['driver'] = $driver;

      $this->active = true;
    }
  }

  public function isActive($conName = null)
  {
    if (isset($conName)) {
      return (isset($this->transactions[$conName]));
    } else {
      return $this->active;
    }
  }

  public function commit()
  {
    $this->executeMethod('doCommit');
  }

  public function rollback()
  {
    $this->executeMethod('doRollback');
  }

  private function executeMethod($method)
  {
    if ($this->isActive()) {
      foreach ($this->transactions as $trans) {
        $trans['driver']->$method($trans['conn']);
      }

      $this->active = false;
      $this->transactions = array();
    }
  }
}
