<?php

/**
 * Helper_Addon
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.helper
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Helper_Addon extends Sabel_Object
{
  const VERSION = 1;
  
  public function version()
  {
    return self::VERSION;
  }
  
  public function load()
  {
    return true;
  }
  
  public function loadProcessor($bus)
  {
    $helper = new Helper_Processor("helper");
    $bus->getProcessorList()->insertNext("router", "helper", $helper);
  }
}
