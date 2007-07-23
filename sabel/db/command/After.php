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
class Sabel_DB_Command_After extends Sabel_DB_Command_Interrupt
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

      if (!self::isApplicable($params["options"], $driverName, $modelName)) continue;

      if (isset(self::$instances[$className])) {
        $ins = self::$instances[$className];
      } else {
        $ins = self::$instances[$className] = new $className();
      }

      $method = $params["method"];
      $ins->$method($executer, $result);
    }
  }

  public static function clear()
  {
    self::$after     = array();
    self::$instances = array();
  }
}
