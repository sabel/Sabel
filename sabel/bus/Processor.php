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
abstract class Sabel_Bus_Processor
{
  public $name;
  
  public function __construct($name = null)
  {
    if ($name === null || $name === "") {
      throw new Sabel_Exception_Runtime("name must be set");
    }
    
    $this->name = $name;
  }
  
  abstract public function execute($bus);
}
