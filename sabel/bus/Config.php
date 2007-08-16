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
abstract class Sabel_Bus_Config
{
  private $bus = null;
  
  abstract public function configure();
  
  public function __construct()
  {
    $this->bus = new Sabel_Bus();
    $this->bus->init(array("storage" => null, "request" => null));
  }
  
  public function add($processor)
  {
    $this->bus->add($processor);
  }
  
  public function addGroup($processor)
  {
    $this->bus->addGroup($processor);
    return $this;
  }
  
  public function getGroup($name)
  {
    return $this->bus->getGroup($name);
  }
  
  public function getBus()
  {
    return $this->bus;
  }
}
