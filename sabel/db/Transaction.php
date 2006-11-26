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
  private static $list   = array();
  private static $active = false;

  public static function add($model)
  {
    if (!$model instanceof Sabel_DB_Relation)
      throw new Exception('argument must be an instance of Sabel_DB_Relation.');

    $driver  = $model->getDriver();
    $conName = $model->connectName;
    $db      = Sabel_DB_Connection::getDB($conName);

    if ($db === 'mysql') {
      $engine = $model->tableEngine;
      $check  = ($engine === 'InnoDB' || $engine === 'BDB');
    } else {
      $check = true;
    }

    if ($check) {
      self::begin($driver, $conName);
    } else {
      $msg = "begin transaction, but a table engine of the '{$model->table}' is {$engine}.";
      trigger_error($msg, E_USER_NOTICE);
    }
  }

  public static function begin($driver, $connectName = 'default')
  {
    if (!isset(self::$list[$connectName])) {
      $conn = Sabel_DB_Connection::getConnection($connectName);
      self::$list[$connectName]['conn']   = $conn;
      self::$list[$connectName]['driver'] = $driver;

      if (!is_null($result = $driver->begin($conn)))
        self::$list[$connectName]['conn'] = $result;

      self::$active = true;
    }
  }

  public static function isActive()
  {
    return self::$active;
  }

  public static function commit()
  {
    self::executeMethod('commit');
  }

  public static function rollback()
  {
    self::executeMethod('rollback');
  }

  private static function executeMethod($method)
  {
    if (sizeof(self::$list) > 0) {
      foreach (self::$list as $connection) {
        $connection['driver']->$method($connection['conn']);
      }
      self::$list   = array();
      self::$active = false;
    }
  }
}
