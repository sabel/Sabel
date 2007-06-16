<?php

/**
 * Sabel_DB_Exception_Sql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Exception_Sql extends Sabel_DB_Exception
{
  const PKG_NAME = "sabel.db.sql";

  public static function error($method, $error)
  {
    parent::displayError($method, $error, self::PKG_NAME);
  }
}

