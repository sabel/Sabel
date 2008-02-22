<?php

/**
 * Flow_Addon
 *
 * @category   Addon
 * @package    addon.flow
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Flow_Addon extends Sabel_Object
{
  public function execute($bus)
  {
    $bus->getProcessorList()
        ->insertPrevious("executer", "flow", new Flow_Processor("flow"));
  }
}
