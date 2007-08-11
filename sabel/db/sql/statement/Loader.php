<?php

/**
 * Sabel_DB_Sql_Statement_Loader
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Statement_Loader
{
  protected static $instances = array();

  public static function load($stmtType)
  {
    $className = "Sabel_DB_Sql_Statement_" . ucfirst($stmtType);

    if (isset(self::$instances[$className])) {
      return self::$instances[$className];
    } else {
      return self::$instances[$className] = new $className();
    }
  }

  public static function clear()
  {
    self::$instances = array();
  }
}
