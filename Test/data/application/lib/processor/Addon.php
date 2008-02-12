<?php

/**
 * Processor_Addon
 *
 * @category   Processor
 * @package    lib.processor
 * @version    1.0
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class TestProcessor_Addon extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $config = $bus->getConfig("addon");
    $addons = $config->configure();
    
    foreach ($addons as $addon) {
      $className = ucfirst($addon) . "_Addon";
      $instance = new $className();
      $instance->execute($bus);
    }
  }
}
