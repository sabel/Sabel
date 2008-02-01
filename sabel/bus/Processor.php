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
  public function setBus($bus)
  {
    $this->bus = $bus;
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
