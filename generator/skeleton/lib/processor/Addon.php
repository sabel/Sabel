<?php

Sabel::fileUsing(CONFIG_DIR_PATH . DS . "Addon" . PHP_SUFFIX);

/**
 * Processor_Addon
 *
 * @category   Processor
 * @package    lib.processor
 * @version    1.0
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Addon extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $config = new Config_Addon();
    $addons = $config->configure();
    
    foreach ($addons as $addon) {
      $className = ucfirst($addon) . "_Addon";
      $instance = new $className();
      $instance->execute($bus);
    }
  }
}
