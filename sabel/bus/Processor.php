<?php

/**
 * Sabel_Bus_Processor
 *
 * @abstract
 * @category   Bus
 * @package    org.sabel.bus
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Bus_Processor extends Sabel_Object
{
  /**
   * @var string
   */
  public $name;
  
  /**
   * @var Sabel_Bus
   */
  protected $bus = null;
  
  /**
   * @var array
   */
  protected $properties = array();
  
  abstract public function execute($bus);
  
  public function __construct($name)
  {
    if ($name === null || $name === "") {
      throw new Sabel_Exception_InvalidArgument("name must be set.");
    }
    
    $this->name = $name;
  }
  
  /**
   * @param Sabel_Bus $bus
   *
   * @return void
   */
  public function setBus(Sabel_Bus $bus)
  {
    $this->bus = $bus;
  }
  
  public function extract()
  {
    $names = func_get_args();
    
    if (count($names) > 0) {
      if (is_array($names[0])) $names = $names[0];
      foreach ($names as $name) {
        $this->properties[$name] = $this->bus->get($name);
      }
    }
  }
  
  public function __set($key, $val)
  {
    $this->properties[$key] = $val;
  }
  
  public function __get($key)
  {
    if (isset($this->properties[$key])) {
      return $this->properties[$key];
    } else {
      return null;
    }
  }
  
  /**
   * @param Sabel_Bus $bus
   *
   * @return void
   */
  public function shutdown($bus)
  {
    
  }
}
