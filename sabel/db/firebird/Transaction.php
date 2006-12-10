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
  private static $cons   = array();
  private static $active = false;

  public static function add($conName, $trans)
  {
    self::$cons[$conName] = $trans;
    self::$active = true;
  }

  public static function get($conName)
  {
    return (isset(self::$cons[$conName])) ? self::$cons[$conName] : null;
  }

  public static function isActive()
  {
    return self::$active;
  }

  public static function commit()
  {
    self::executeMethod('ibase_commit');
  }

  public static function rollback()
  {
    self::executeMethod('ibase_rollback');
  }

  private static function executeMethod($method)
  {
    if (sizeof(self::$cons) > 0) {
      foreach (self::$cons as $trans) {
        $method($trans);
      }
      self::$cons   = array();
      self::$active = false;

      // @todo
      Sabel_DB_Transaction::unsetList();
    }
  }
}
