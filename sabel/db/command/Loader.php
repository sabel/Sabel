<?php

/**
 * Sabel_DB_Command_Loader
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Command_Loader
{
  protected static $instances = array();

  public static function getClass($command)
  {
    $className = "Sabel_DB_Command_" . ucfirst($command);

    if (isset(self::$instances[$className])) {
      return self::$instances[$className];
    } else {
      $instance = new $className();
      return self::$instances[$className] = $instance;
    }
  }

  public static function clear()
  {
    self::$instances = array();
  }
}
