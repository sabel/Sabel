<?php

/**
 * Sabel_DB_Model_Proxy
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Model_Proxy extends Sabel_DB_Abstract_Model
{
  public function __construct($mdlName)
  {
    $this->initialize($mdlName);
  }
}
