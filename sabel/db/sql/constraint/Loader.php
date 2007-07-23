<?php

/**
 * Sabel_DB_Sql_Constraint_Loader
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Constraint_Loader
{
  const COMMON = "Common";
  const OCI    = "Oci";
  const MSSQL  = "Mssql";
  const IBASE  = "Ibase";

  protected static $instances = array();

  public static function load($type = self::COMMON)
  {
    $className = "Sabel_DB_Sql_Constraint_" . $type;

    if (isset(self::$instances[$className])) {
      $instance = self::$instances[$className];
    } else {
      $instance = self::$instances[$className] = new $className();
    }

    return $instance;
  }
}
