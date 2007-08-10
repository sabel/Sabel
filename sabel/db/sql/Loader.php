<?php

/**
 * Sabel_DB_Sql_Loader
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Loader
{
  protected static $instances = array();

  public static function load($executer, $className)
  {
    if (isset(self::$instances[$className])) {
      $instance = self::$instances[$className];
    } else {
      $instance = self::$instances[$className] = new $className();
      if (!$instance instanceof Sabel_DB_Sql_Interface) {
        $name = get_class($instance);
        throw new Exception("'{$name}' should implement Sabel_DB_Sql_Interface.");
      }
    }

    $instance->setExecuter($executer);

    return $instance;
  }

  public static function clear()
  {
    self::$instances = array();
  }
}
