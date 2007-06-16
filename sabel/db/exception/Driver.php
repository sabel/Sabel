<?php

/**
 * Sabel_DB_Exception_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Exception_Driver extends Sabel_DB_Exception
{
  const PKG_NAME = "sabel.db.driver";

  public static function execError($sql, $error, $connnectionName, $extra = null)
  {
    $message = array();
    $params  = Sabel_DB_Config::get($connnectionName);

    $message["EXECUTE_QUERY"]   = $sql;
    $message["CONNECTION_NAME"] = $connnectionName;
    $message["PARAMETERS"]      = $params;

    if ($extra !== null) $message = $message + $extra;

    parent::displayError("execute", $error, self::PKG_NAME, $message);
  }
}

