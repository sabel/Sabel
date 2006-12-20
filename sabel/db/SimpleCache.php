<?php

/**
 * Sabel_DB_SimpleCache
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_SimpleCache
{
  private static $cache = array();

  public static function add($key, $val)
  {
    self::$cache[$key] = $val;
  }

  public static function get($key)
  {
    return (isset(self::$cache[$key])) ? self::$cache[$key] : null;
  }

  public static function remove($key)
  {
    unset(self::$cache[$key]);
  }

  public static function clear()
  {
    self::$cache = array();
  }
}
