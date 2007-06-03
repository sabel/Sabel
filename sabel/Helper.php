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
    
    $pref = "{$appDir}/{$m}/{$helperDir}/";
    
    $helpers = array();
    
    $helpers[] = "/{$appDir}/{$helperDir}/{$sharedHelper}.{$helperSuffix}";
    $helpers[] = $pref . "{$sharedHelper}.{$helperSuffix}";
    $helpers[] = $pref . "{$c}.{$helperSuffix}";
    
    $helpers[] = "/{$appDir}/{$helperDir}/{$sharedHelper}.{$helperSuffix}";
                     
    foreach ($helpers as $helper) {
      $path = RUN_BASE . $helper;
      if (is_file($path)) Sabel::fileUsing($path);
    }
  }
}
