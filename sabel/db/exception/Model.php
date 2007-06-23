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
  protected $pkg_name = "sabel.db.model";

  public function exception($method, $error)
  {
    return parent::message($method, $error);
  }

  public function missing($method, $arguments, $prefix = null)
  {
    $error = "argument should be an array.";
    if ($prefix !== null) $error = $prefix . " " . $error;

    $extra = array("INVALID_ARG" => $this->createArguments($arguments));
    return parent::message($method, $error, $extra);
  }
}
