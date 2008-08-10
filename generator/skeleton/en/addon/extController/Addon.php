<?php

/**
 * ExtController_Addon
 *
 * @category   Addon
 * @package    addon.acl
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class ExtController_Addon extends Sabel_Object
{
  public function execute($bus)
  {
    $bus->getProcessorList()->insertNext(
      "controller",
      "extController",
      new ExtController_Processor("extController")
    );
  }
}
