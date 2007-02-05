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
  public static function load($mdlName)
  {
    return self::createModel($mdlName);
  }

  public static function fusion($mdlNames)
  {
    $models = array();
    foreach ($mdlNames as $name) $models[] = self::createModel($name);
    return Sabel::load('Sabel_DB_Model_Fusion', $models, $mdlNames);
  }

  protected static function createModel($mdlName)
  {
    Sabel::using($mdlName);
    if (class_exists($mdlName, false)) {
      return new $mdlName();
    } else {
      return Sabel::load('Dummy', $mdlName);
    }
  }
}
