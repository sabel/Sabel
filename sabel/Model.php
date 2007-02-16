<?php

Sabel::using('Sabel_DB_Model');

/**
 * Sabel_Model
 *
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Model
{
  public static function load($mdlName, $arg1 = null, $arg2 = null)
  {
    return self::createModel($mdlName, $arg1, $arg2);
  }

  public static function fusion($mdlNames)
  {
    $models = array();
    foreach ($mdlNames as $name) $models[] = self::createModel($name);
    return Sabel::load('Sabel_DB_Model_Fusion', $models, $mdlNames);
  }

  protected static function createModel($mdlName, $arg1 = null, $arg2 = null)
  {
    Sabel::using($mdlName);
    if (class_exists($mdlName, false)) {
      return new $mdlName($arg1, $arg2);
    } else {
      return Sabel::load('Proxy', $mdlName);
    }
  }
}
