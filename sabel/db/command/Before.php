<?php

/**
 * Sabel_DB_Command_Before
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Command_Before
{
  const INTERRUPT_IMMEDIATE = -1;
  const INTERRUPT           = 0;
  const CONTINUOUS          = 1;

  protected static $before    = array();
  protected static $instances = array();

  public static function regist($class, $commands, $methods,
                                $options = null, $continuation = self::CONTINUOUS)
  {
    if (is_array($class)) {
      $key   = $class[0];
      $cache = ($class[1] === true || $class[1] === 1);
    } else {
      $key   = $class;
      $cache = false;
    }

    self::$before[$key] = array("commands" => $commands,
                                "methods"  => $methods,
                                "options"  => $options,
                                "cache"    => $cache,
                                "continue" => $continuation);
  }

  public static function execute($commandId, $commandClass)
  {
    $result = self::CONTINUOUS;
    if (empty(self::$before)) return $result;

    $tableName  = $commandClass->getModel()->getTableName();
    $modelName  = convert_to_modelname($tableName);
    $driverName = get_class($commandClass->getDriver());

    foreach (self::$before as $className => $params) {
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

          if ($params["continue"] === self::INTERRUPT_IMMEDIATE) {
            return self::INTERRUPT_IMMEDIATE;
          } elseif ($params["continue"] === self::INTERRUPT) {
            $result = self::INTERRUPT;
          }
        }
      }
    }

    return $result;
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
    self::$before    = array();
    self::$instances = array();
  }
}
