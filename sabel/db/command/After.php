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

  public static function regist($class, $commands, $methods, $options = null)
  {
    if (is_array($class)) {
      $key   = $class[0];
      $cache = ($class[1] === true || $class[1] === 1);
    } else {
      $key   = $class;
      $cache = false;
    }

    self::$after[$key] = array("commands" => $commands,
                               "methods"  => $methods,
                               "options"  => $options,
                               "cache"    => $cache);
  }

  public static function execute($commandId, $commandClass)
  {
    if (empty(self::$after)) return;

    $tableName  = $commandClass->getModel()->getTableName();
    $modelName  = convert_to_modelname($tableName);
    $driverName = get_class($commandClass->getDriver());

    foreach (self::$after as $className => $params) {
      $commands = $params["commands"];
      if (!is_array($commands)) $commands = (array)$commands;
      if (!in_array($commandId, $commands)) continue;

      if (!self::isPass($params["options"], $driverName, $modelName)) continue;

      if ($params["cache"]) {
        if (isset(self::$instances[$className])) {
          $ins = self::$instances[$className];
        } else {
          $ins = self::$instances[$className] = new $className();
        }
      } else {
        $ins = new $className();
      }

      foreach ($commands as $command) {
        if ($command === $commandId) {
          foreach ($params["methods"] as $method) {
            $ins->$method($commandClass);
          }
        }
      }
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
