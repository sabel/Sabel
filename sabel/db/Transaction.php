<?php

/**
 * Sabel_DB_Transaction
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Transaction
{
  private static $active = false;

  public static function activate()
  {
    self::$active = true;
  }

  public static function isActive()
  {
    return self::$active;
  }

  public static function begin($connectionName)
  {
    Sabel_DB_Config::loadDriver($connectionName)->begin();
    self::$active = true;
  }

  public static function commit()
  {
    self::finish("commit");
  }

  public static function rollback()
  {
    self::finish("rollback");
  }

  private static function finish($method)
  {
    $instances = Sabel_DB_Transaction_Base::getInstances();
    if (!$instances) return;

    foreach ($instances as $instance) $instance->$method();
    self::$active = false;
  }

  public static function registBefore()
  {
    // @todo
  }

  public static function registAfter()
  {
    // @todo
  }
}
