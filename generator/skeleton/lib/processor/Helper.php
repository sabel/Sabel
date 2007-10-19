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
    list($m, $c, $a) = $bus->get("destination")->toArray();
    
    $appDir       = "app";
    $helperDir    = "helpers";
    $sharedHelper = "application";
    $commonHelper = "common";
    $helperSuffix = "php";
    
    $pref = DS . $appDir . DS . $m . DS . $helperDir . DS;
    
    $helpers = array();
    
    $helpers[] = DS . $appDir . DS . $helperDir . DS . "{$sharedHelper}.{$helperSuffix}";
    $helpers[] = DS . $appDir . DS . $helperDir . DS . "{$commonHelper}.{$helperSuffix}";
    $helpers[] = $pref . "{$sharedHelper}.{$helperSuffix}";
    $helpers[] = $pref . "{$c}.{$helperSuffix}";
    
    foreach ($helpers as $helper) {
      Sabel::fileUsing(RUN_BASE . $helper, true);
    }
  }
}
