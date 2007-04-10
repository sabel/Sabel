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
  protected static $after = array();

  public static function regist($className, $commands, $methods)
  {
    self::$before[$className] = array("commands" => $commands, "methods" => $methods);
  }

  public static function execute($commandId, $commandClass)
  {
    if (empty(self::$before)) return true;

    foreach (self::$before as $className => $params) {
      $commands = $params["commands"];
      if (is_string($commands)) $commands = (array)$commands;
      if (!in_array($commandId, $commands)) continue;

      foreach ($commands as $command) {
        if ($command === $commandId) {
          $ins = new $className();
          foreach ($params["methods"] as $method) {
            $ins->$method($commandClass);
          }
        }
      }
    }
  }
}
