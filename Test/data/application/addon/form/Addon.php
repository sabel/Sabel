<?php

/**
 * Form_Addon
 *
 * @category   Addon
 * @package    addon.form
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Form_Addon extends Sabel_Object
{
  public function execute($bus)
  {
    $bus->getProcessorList()
        ->insertNext("initializer", "form", new Form_Processor("form"));
  }
}
