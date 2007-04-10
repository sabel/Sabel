<?php

/**
 * Sabel_DB_Sql_Loader
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Loader
{
  const COMMON = "Common";
  const PDO    = "Pdo";

  protected static $classes = array();

  public static function getClass($model, $type = self::COMMON)
  {
    $className = "Sabel_DB_Sql_" . $type;

    if (isset(self::$classes[$className])) {
      $instance = self::$classes[$className];
    } else {
      $instance = self::$classes[$className] = new $className();
    }

    $instance->setModel($model);
    return $instance;
  }
}
