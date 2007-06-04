<?php

/**
 * Sabel_DB_Transaction_Base
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Transaction_Base
{
  protected $active = false;
  protected $transactions = array();

  private function __construct() {}

  public function isActive($connectionName = null)
  {
    if ($connectionName === null) {
      return $this->active;
    } else {
      return (isset($this->transactions[$connectionName]));
    }
  }

  public function clear()
  {
    $this->active = false;
    $this->transactions = array();
  }
}
