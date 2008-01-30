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
  /**
   * @var array
   */
  protected static $modelNames  = array();
  
  /**
   * @var array
   */
  protected static $columnNames = array();
  
  /**
   * @param string $mdlName
   * @param string $name
   *
   * @return void
   */
  public static function setName($mdlName, $name)
  {
    self::$modelNames[$mdlName] = $name;
  }
  
  /**
   * @param string $mdlName
   * @param array  $names
   *
   * @return void
   */
  public static function setColumnNames($mdlName, array $names)
  {
    self::$columnNames[$mdlName] = $names;
  }
  
  /**
   * @param string $mdlName
   *
   * @return string
   */
  public static function getName($mdlName)
  {
    if (isset(self::$modelNames[$mdlName])) {
      return self::$modelNames[$mdlName];
    } else {
      return $mdlName;
    }
  }
  
  /**
   * @param string $mdlName
   *
   * @return array
   */
  public static function getColumnNames($mdlName)
  {
    if (isset(self::$columnNames[$mdlName])) {
      return self::$columnNames[$mdlName];
    } else {
      return array();
    }
  }
}
