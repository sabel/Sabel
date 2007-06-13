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
  public static function load($mdlName)
  {
    return self::begin(MODEL($mdlName));
  }

  public static function begin($model)
  {
    Sabel_DB_Config::loadDriver($model->getConnectionName())->begin();

    return $model;
  }

  public static function commit()
  {
    $instances = Sabel_DB_Transaction_Base::getInstances();
    if (!$instances) return;

    foreach ($instances as $ins) $ins->commit();
  }

  public static function rollback()
  {
    $instances = Sabel_DB_Transaction_Base::getInstances();
    if (!$instances) return;

    foreach ($instances as $ins) $ins->rollback();
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
