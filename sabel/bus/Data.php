<?php

/**
 * Sabel_Bus
 *
 * @category   Bus
 * @package    org.sabel.bus
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Bus_Data
{
  private $holder = array();
  
  public function set($key, $value)
  {
    $this->holder[$key] = $value;
  }
  
  public function get($key)
  {
    return $this->holder[$key];
  }
}
