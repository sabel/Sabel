<?php

/**
 * Sabel_DB_Exception_Validate
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Exception_Validate extends Sabel_DB_Exception
{
  protected $pkg_name = "sabel.db.validate";

  public function exception($method, $message)
  {
    return parent::message($method, $message);
  }
}
