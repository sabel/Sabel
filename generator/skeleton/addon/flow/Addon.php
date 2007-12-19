<?php

/**
 * Flow_Addon
 *
 * @version    1.0
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Flow_Addon extends Sabel_Object
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
    $executer = $bus->getList()->find("executer");
    
    if (is_object($executer)) {
      $flowProcessor = new Flow_Processor("flow");
      $executer->insertPrevious("flow", $flowProcessor);
      $bus->attachExecuteAfterEvent("executer", $flowProcessor, "afterExecute");
    }
  }
}
