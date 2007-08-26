<?php

/**
 * Processor_Helper
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Helper extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    Sabel_Helper::load($bus->get("request"), $bus->get("destination"));
  }
}
