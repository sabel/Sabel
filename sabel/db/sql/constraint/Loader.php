<?php

/**
 * Sabel_DB_Sql_Constraint_Loader
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Constraint_Loader
{
  private static $instances = array();

  public static function load($className)
  {
    if (isset(self::$instances[$className])) {
      $instance = self::$instances[$className];
    } else {
      $instance = self::$instances[$className] = new $className();
      if (!$instance instanceof Sabel_DB_Sql_Constraint_Interface) {
        $name = get_class($instance);
        $message = "'{$name}' should implement Sabel_DB_Sql_Constraint_Interface.";
        throw new Sabel_DB_Exception($message);
      }
    }

    return $instance;
  }

  public static function clear()
  {
    self::$instances = array();
  }
}
