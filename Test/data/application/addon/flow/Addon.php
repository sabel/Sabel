<?php

/**
 * Flow_Addon
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.flow
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Flow_Addon extends Sabel_Object
{
  public function execute($bus)
  {
    if ($executer = $bus->getProcessorList()->get("executer")) {
      $flowProcessor = new Flow_Processor("flow");
      $bus->getProcessorList()->insertPrevious("executer", "flow", $flowProcessor);
      $bus->attachExecuteAfterEvent("executer", $flowProcessor, "afterExecute");
    }
  }
}
