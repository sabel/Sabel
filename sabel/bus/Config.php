<?php

/**
 * Abstract Bus Config
 *
 * @category   Bus
 * @package    org.sabel.bus
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Bus_Config implements Sabel_Config
{
  protected $bus = null;
  
  public function __construct()
  {
    $this->bus = new Sabel_Bus();
    $this->bus->init(array("storage" => null, "request" => null));
  }
  
  public function add($processor, $enableListener = false)
  {
    if ($enableListener) {
      $this->bus->addProcessorAndListener($processor);
    } else {
      $this->bus->addProcessor($processor);
    }
  }
  
  public function get($name)
  {
    return $this->bus->getProcessor($name);
  }
  
  public function getBus()
  {
    return $this->bus;
  }
}
