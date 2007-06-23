<?php

/**
 * Sabel_DB_Exception_Connection
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Exception_Connection extends Sabel_DB_Exception
{
  protected $pkg_name = "sabel.db.connection";

  public function exception($error, $extra)
  {
    return parent::message("connect", $error, $extra);
  }
}
