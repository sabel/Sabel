<?php

/**
 * Sabel_DB_Join_ColumnHash
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Join_ColumnHash
{
  private static $columns = array();
  
  public static function toHash($as)
  {
    $hash = "a" . substr(md5($as), 0, 24);
    return self::$columns[$as] = $hash;
  }
  
  public static function getHash($as)
  {
    return (isset(self::$columns[$as])) ? self::$columns[$as] : $as;
  }
  
  public static function clear()
  {
    self::$columns = array();
  }
}
