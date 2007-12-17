<?php

/**
 * Sabel_DB_Sql_Part
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Part extends Sabel_Object
{
  protected $part = null;
  
  public function __construct($part)
  {
    $this->part = $part;
  }
  
  public function __toString()
  {
    return $this->part;
  }
}
