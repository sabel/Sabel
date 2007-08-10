<?php

/**
 * Sabel_DB_Condition_Builder_Loader
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_Builder_Loader
{
  private static $instances = array();

  public static function load($driver, $className)
  {
    if (isset(self::$instances[$className])) {
      $instance = self::$instances[$className];
    } else {
      $instance = self::$instances[$className] = new $className();

      if (!$instance instanceof Sabel_DB_Abstract_ConditionBuilder) {
        $name = get_class($instance);
        throw new Exception("'{$name}' should be instance of Sabel_DB_Abstract_ConditionBuilder.");
      }
    }

    $instance->initialize($driver);
    return $instance;
  }

  public static function clear()
  {
    self::$instances = array();
  }
}
