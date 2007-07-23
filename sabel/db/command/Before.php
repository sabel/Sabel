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
class Sabel_DB_Command_Before extends Sabel_DB_Command_Interrupt
{
  const INTERRUPT_IMMEDIATE = "INTERRUPT_IMMEDIATE";
  const INTERRUPT           = "INTERRUPT";
  const CONTINUOUS          = "CONTINUOUS";

  protected static $before    = array();
  protected static $instances = array();

  public static function regist($class, $commands, $options = null)
  {
    $method = (isset($options["method"])) ? $options["method"] : "execute";

    self::$before[$class] = array("commands" => $commands,
                                  "method"   => $method,
                                  "options"  => $options);
  }

  public static function execute($commandId, $executer)
  {
    $result = self::CONTINUOUS;
    if (empty(self::$before)) return $result;

    $modelName  = $executer->getModel()->getModelName();
    $driverName = get_class($executer->getDriver());

    foreach (self::$before as $className => $params) {
      if (($commandId & $params["commands"]) === 0) continue;

      if (!self::isApplicable($params["options"], $driverName, $modelName)) continue;

      if (isset(self::$instances[$className])) {
        $ins = self::$instances[$className];
      } else {
        $ins = self::$instances[$className] = new $className();
      }

      $method = $params["method"];
      $res = $ins->$method($executer);

      if ($res === self::INTERRUPT_IMMEDIATE) {
        return self::INTERRUPT_IMMEDIATE;
      } elseif ($res === self::INTERRUPT) {
        $result = self::INTERRUPT;
      }
    }

    return $result;
  }

  public static function clear()
  {
    self::$before    = array();
    self::$instances = array();
  }
}
