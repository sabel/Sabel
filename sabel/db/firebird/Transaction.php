<?php

/**
 * Sabel_DB_Firebird_Transaction
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage firebird
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Firebird_Transaction
{
  private static $ins = null;

  private
    $active       = false,
    $transactions = array();

  private function __construct() { }

  public static function getInstance()
  {
    if (self::$ins === null) self::$ins = new self();
    return self::$ins;
  }

  public function begin($transaction, $conName = 'default')
  {
    if (!isset($this->transactions[$conName])) {
      $this->transactions[$conName] = $transaction;
      $this->active = true;
    }
  }

  public function get($conName)
  {
    $ts = $this->transactions;
    return (isset($ts[$conName])) ? $ts[$conName] : null;
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
    $this->executeMethod('ibase_commit');
  }

  public function rollback()
  {
    $this->executeMethod('ibase_rollback');
  }

  private function executeMethod($method)
  {
    if ($this->isActive()) {
      foreach ($this->transactions as $transaction) $method($transaction);

      $this->active = false;
      $this->transactions = array();
    }
  }
}
