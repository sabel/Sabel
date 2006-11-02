<?php

/**
 * Sabel_DB_Driver_Pdo_PdoStatement
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage driver
 * @subpackage pdo
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver_Pdo_PdoStatement
{
  private static $stmt  = null;
  private static $sql   = '';
  private static $keys  = array();
  private static $const = array();

  public static function exists($sql, $cond, $const = null)
  {
    $result = true;
    $keys   = array();

    if ($cond) $keys = array_keys($cond);

    if (self::$sql !== $sql || self::$keys !== $keys || self::$const !== $const) {
      self::$sql   = $sql;
      self::$keys  = $keys;
      self::$const = $const;

      $result = false;
    }
    return $result;
  }

  public static function add($stmt)
  {
    self::$stmt = $stmt;
  }

  public static function get()
  {
    return self::$stmt;
  }
}
