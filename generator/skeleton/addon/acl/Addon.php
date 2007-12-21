<?php

/**
 * Acl_Addon
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.acl
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Acl_Addon extends Sabel_Object
{
  const VERSION = 1;
  
  public function version()
  {
    return self::VERSION;
  }
  
  public function load()
  {
    return false;
  }
  
  public function loadProcessor($bus)
  {
    $bus->attachExecuteEvent("redirector", $this, "eventCallback");
  }
  
  public function eventCallback($bus)
  {
    $acl = new Acl_Processor("acl");
    $bus->getList()->find("redirector")->insertNext("acl", $acl);
  }
}
