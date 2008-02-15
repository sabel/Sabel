<?php

/**
 * Acl_Addon
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.acl
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Acl_Addon extends Sabel_Object
{
  public function execute($bus)
  {
    $acl = new Acl_Processor("acl");
    $bus->getProcessorList()->insertPrevious("initializer", "acl", $acl);
  }
}
