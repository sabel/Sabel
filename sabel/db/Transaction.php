<?php

/**
 * Sabel_DB_Transaction
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Transaction
{
  const READ_UNCOMMITTED = 1;
  const READ_COMMITTED   = 2;
  const REPEATABLE_READ  = 3;
  const SERIALIZABLE     = 4;
  
  /**
   * @var boolean
   */
  private static $active = false;
  
  /**
   * @var resource[]
   */
  private static $transactions = array();
  
  /**
   * @var int
   */
  private static $isolationLevel = null;
  
  /**
   * @param int $isolationLevel
   *
   * @throws Sabel_Exception_InvalidArgument
   * @return void
   */
  public static function activate($isolationLevel = null)
  {
    self::$active = true;
    if ($isolationLevel === null) return;
    
    if (is_numeric($isolationLevel) || $isolationLevel >= 1 && $isolationLevel <= 4) {
      self::$isolationLevel = $isolationLevel;
    } else {
      $message = __METHOD__ . "() invalid isolation level.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  /**
   * @param string $connectionName
   *
   * @return boolean
   */
  public static function isActive($connectionName = null)
  {
    if ($connectionName === null) {
      return self::$active;
    } else {
      return isset(self::$transactions[$connectionName]);
    }
  }
  
  /**
   * @param string $connectionName
   *
   * @return resource
   */
  public static function getConnection($connectionName)
  {
    if (isset(self::$transactions[$connectionName]["conn"])) {
      return self::$transactions[$connectionName]["conn"];
    } else {
      return null;
    }
  }
  
  /**
   * @param Sabel_DB_Driver $driver
   *
   * @throws Sabel_DB_Exception_Transaction
   * @return void
   */
  public static function begin(Sabel_DB_Driver $driver)
  {
    switch (self::$isolationLevel) {
      case self::READ_UNCOMMITTED:
        $iLevel = Sabel_DB_Driver::TRANS_READ_UNCOMMITTED;
        break;
      case self::READ_COMMITTED:
        $iLevel = Sabel_DB_Driver::TRANS_READ_COMMITTED;
        break;
      case self::REPEATABLE_READ:
        $iLevel = Sabel_DB_Driver::TRANS_REPEATABLE_READ;
        break;
      case self::SERIALIZABLE:
        $iLevel = Sabel_DB_Driver::TRANS_SERIALIZABLE;
        break;
      default:
        $iLevel = null;
    }
    
    $connectionName = $driver->getConnectionName();
    
    try {
      self::$transactions[$connectionName]["conn"]   = $driver->begin($iLevel);
      self::$transactions[$connectionName]["driver"] = $driver;
      self::$active = true;
    } catch (Exception $e) {
      throw new Sabel_DB_Exception_Transaction($e->getMessage());
    }
  }
  
  /**
   * @throws Sabel_DB_Exception_Transaction
   * @return void
   */
  public static function commit()
  {
    try {
      self::release("commit");
    } catch (Exception $e) {
      throw new Sabel_DB_Exception_Transaction($e->getMessage());
    }
  }
  
  /**
   * @throws Sabel_DB_Exception_Transaction
   * @return void
   */
  public static function rollback()
  {
    try {
      self::release("rollback");
    } catch (Exception $e) {
      throw new Sabel_DB_Exception_Transaction($e->getMessage());
    }
  }
  
  /**
   * @return void
   */
  private static function release($method)
  {
    if (self::$active) {
      foreach (self::$transactions as $trans) {
        $trans["driver"]->$method();
      }
      
      self::clear();
    }
  }
  
  /**
   * @return void
   */
  public static function clear()
  {
    self::$active = false;
    self::$transactions   = array();
    self::$isolationLevel = null;
  }
}
