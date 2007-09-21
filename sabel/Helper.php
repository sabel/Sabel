<?php

/**
 * Sabel_Helper
 *
 * @category   Helper
 * @package    org.sabel.helper
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Helper
{
  public static function load($request, $destination)
  {
    list($m, $c, $a) = $destination->toArray();
    
    $appDir       = "app";
    $helperDir    = "helpers";
    $sharedHelper = "application";
    $helperSuffix = "php";
    
    $pref = DIR_DIVIDER . $appDir . DIR_DIVIDER . $m
          . DIR_DIVIDER . $helperDir . DIR_DIVIDER;
    
    $helpers = array();
    
    $helpers[] = DIR_DIVIDER . $appDir . DIR_DIVIDER . $helperDir
               . DIR_DIVIDER . "{$sharedHelper}.{$helperSuffix}";
               
    $helpers[] = $pref . "{$sharedHelper}.{$helperSuffix}";
    $helpers[] = $pref . "{$c}.{$helperSuffix}";
    
    foreach ($helpers as $helper) {
      Sabel::fileUsing(RUN_BASE . $helper, true);
    }
  }
}
