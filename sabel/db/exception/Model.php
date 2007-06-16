<?php

/**
 * Sabel_DB_Exception_Model
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Exception_Model extends Sabel_DB_Exception
{
  const PKG_NAME = "sabel.db.model";

  public static function error($method, $error)
  {
    parent::displayError($method, $error, self::PKG_NAME);
  }

  public static function isNotArray($method, $arguments, $prefix = "")
  {
    $error = "argument should be an array.";
    if ($prefix !== null) $error = $prefix . " " . $error;

    $extra = array("INVALID_ARG" => parent::createArguments($arguments));
    parent::displayError($method, $error, self::PKG_NAME, $extra);
  }
}

