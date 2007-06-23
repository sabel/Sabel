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
  protected $pkg_name = "sabel.db.config";

  public function notFound($connectionName)
  {
    $message = "connection name '{$connectionName}' is not found.";
    $extra   = array("CONFIGS" => self::getConfig());

    return parent::message("getConfig", $message, $extra);
  }

  public function undefinedIndex($method, $index)
  {
    $message = "parameter '{$index}' is not found.";
    $extra   = array("CONFIGS" => self::getConfig());

    return parent::message($method, $message, $extra);
  }

  private static function getConfig()
  {
    return get_db_params(ENVIRONMENT);
  }
}
