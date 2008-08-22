<?php

/**
 * Sabel_Bus_Processor
 *
 * @abstract
 * @category   Bus
 * @package    org.sabel.bus
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Bus_Processor extends Sabel_Object
{
  /**
   * @var string
   */
  public $name;
  
  /**
   * @var array
   */
  protected $properties = array();
  
  /**
   * @var array
   */
  protected $beforeEvents = array();
  
  /**
   * @var array
   */
  protected $afterEvents = array();
  
  abstract public function execute($bus);
  
  public function __construct($name)
  {
    if ($name === null || $name === "") {
      $message = __METHOD__ . "() name must be set.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    $this->name = $name;
    
    if (!empty($this->beforeEvents)) {
      $bus = $this->getBus();
      foreach ($this->beforeEvents as $target => $callback) {
        $bus->attachExecuteBeforeEvent($target, $this, $callback);
      }
    }
    
    if (!empty($this->afterEvents)) {
      $bus = $this->getBus();
      foreach ($this->afterEvents as $target => $callback) {
        $bus->attachExecuteAfterEvent($target, $this, $callback);
      }
    }
  }
  
  public function extract()
  {
    $names = func_get_args();
    
    if (count($names) > 0) {
      $bus = $this->getBus();
      if (is_array($names[0])) $names = $names[0];
      foreach ($names as $name) {
        $this->properties[$name] = $bus->get($name);
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
  
  protected function getBus()
  {
    return Sabel_Context::getContext()->getBus();
  }
}
