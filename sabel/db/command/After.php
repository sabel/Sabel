<?php

/**
 * Sabel_DB_Command_After
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Command_After
{
  protected static $after     = array();
  protected static $instances = array();

  public static function regist($class, $commands, $options = null)
  {
    $method = (isset($options["method"])) ? $options["method"] : "execute";

    self::$after[$class] = array("commands" => $commands,
                                 "method"   => $method,
                                 "options"  => $options);
  }

  public static function execute($commandId, $executer, $result)
  {
    if (empty(self::$after)) return;

    $modelName  = $executer->getModel()->getModelName();
    $driverName = get_class($executer->getDriver());

    foreach (self::$after as $className => $params) {
      if (($commandId & $params["commands"]) === 0) continue;

      if (!self::isPass($params["options"], $driverName, $modelName)) continue;

      if (isset(self::$instances[$className])) {
        $ins = self::$instances[$className];
      } else {
        $ins = self::$instances[$className] = new $className();
      }

      $method = $params["method"];
      $ins->$method($executer, $result);
    }
  }

  protected static function isPass($options, $driverName, $modelName)
  {
    if (empty($options)) return true;

    if (isset($options["driver"])) {
      if (!self::optCheck($options["driver"], $driverName)) return false;
    } elseif (isset($options["model"])) {
      if (!self::optCheck($options["model"], $modelName)) return false;
    }

    return true;
  }

  protected static function optCheck($option, $className)
  {
    if (isset($option["exclude"])) {
      if (in_array($className, $option["exclude"])) return false;
    } elseif (isset($option["include"])) {
      if (!in_array($className, $option["include"])) return false;
    }

    return true;
  }

  public static function clear()
  {
    self::$after     = array();
    self::$instances = array();
  }
}
