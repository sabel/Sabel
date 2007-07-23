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
  protected static $overwrite = array();

  public static function load($command)
  {
    $className = "Sabel_DB_Command_" . ucfirst($command);

    if (isset(self::$instances[$className])) {
      return self::$instances[$className];
    } else {
      $instance  = new $className();
      $commandId = $instance->getCommandId();

      if (isset(self::$overwrite[$commandId])) {
        return self::$overwrite[$commandId];
      } else {
        return self::$instances[$className] = $instance;
      }
    }
  }

  public static function setInstance($commandId, $instance)
  {
    if ($instance instanceof Sabel_DB_Command_Base) {
      self::$overwrite[$commandId] = $instance;
    } else {
      // @todo
      throw new Exception("invalid object type.");
    }
  }

  public static function clear()
  {
    self::$instances = array();
  }
}
