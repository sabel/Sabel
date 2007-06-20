<?php

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

  protected static function createModel($mdlName, $arg1 = null, $arg2 = null)
  {
    Sabel::using($mdlName);

    if (class_exists($mdlName, true)) {
      return new $mdlName($arg1, $arg2);
    } else {
      if ($arg1 === null) {
        return new Proxy($mdlName);
      } else {
        $proxy = new Proxy($mdlName);
        return $proxy->selectOne($arg1, $arg2);
      }
    }
  }
}
