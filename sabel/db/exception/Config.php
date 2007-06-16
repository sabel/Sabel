<?php

/**
 * Sabel_DB_Exception_Config
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Exception_Config extends Sabel_DB_Exception
{
  const PKG_NAME = "sabel.db.config";

  public static function notFound($connectionName)
  {
    $error = "connection name '{$connectionName}' is not found.";
    $extra = array("CONFIGS" => self::getConfig());

    parent::displayError("getConfig", $error, self::PKG_NAME, $extra);
  }

  public static function paramNotFound($method, $paramName)
  {
    $error = "parameter '{$paramName}' is not found.";
    $extra = array("CONFIGS" => self::getConfig());

    parent::displayError($method, $error, self::PKG_NAME, $extra);
  }

  private static function getConfig()
  {
    return get_db_params(ENVIRONMENT);
  }
}

