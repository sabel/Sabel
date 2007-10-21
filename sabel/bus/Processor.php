<?php

/**
 * Sabel_Bus_Processor
 *
 * @category   Bus
 * @package    org.sabel.bus
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Bus_Processor extends Sabel_Object
{
  public $name;
  protected $holder = array();
  protected $bus = null;
  
  public function __construct($name = null)
  {
    if ($name === null || $name === "") {
      throw new Sabel_Exception_Runtime("name must be set");
    }
    
    $this->name = $name;
  }
  
  public function setBus($bus)
  {
    $this->bus = $bus;
    $this->holder =& $bus->getHolder();
  }
  
  abstract public function execute($bus);
  
  protected function __get($name)
  {
    return $this->holder[$name];
  }
  
  protected function __set($name, $value)
  {
    $this->holder[$name] = $value;
    $this->bus->set($name, $value);
  }
}
