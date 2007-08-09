<?php

/**
 * Sabel_Processor_Locale
 *
 * @category   Processor
 * @package    org.sabel.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Processor_Locale implements Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $injector = Sabel_Container::injector(new Factory());
    $bus->set("locale", $injector->newInstance("Sabel_Locale"));
  }
}
