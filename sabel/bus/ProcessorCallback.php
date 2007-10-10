<?php

/**
 * Sabel_Bus_ProcessorCallback
 *
 * @category   Bus
 * @package    org.sabel.bus
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Bus_ProcessorCallback
{
  public $name;
  public $method;
  public $when;
  
  public function __construct($processor, $method, $when)
  {
    $this->processor = $processor;
    $this->method = $method;
    $this->when = $when;
  }
}
