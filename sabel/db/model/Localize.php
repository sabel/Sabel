<?php

/**
 * Sabel_DB_Model_Localize
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Model_Localize
{
  protected static $modelNames  = array();
  protected static $columnNames = array();
  
  public static function setName($mdlName, $name)
  {
    self::$modelNames[$mdlName] = $name;
  }
  
  public static function setColumnNames($mdlName, $names)
  {
    self::$columnNames[$mdlName] = $names;
  }
  
  public static function getName($mdlName)
  {
    if (isset(self::$modelNames[$mdlName])) {
      return self::$modelNames[$mdlName];
    } else {
      return $mdlName;
    }
  }
  
  public static function getColumnNames($mdlName)
  {
    if (isset(self::$columnNames[$mdlName])) {
      return self::$columnNames[$mdlName];
    } else {
      return array();
    }
  }
}
