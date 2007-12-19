<?php

/**
 * I18n_Addon
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.i18n
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class I18n_Addon extends Sabel_Object
{
  const VERSION = 1;
  
  public function version()
  {
    return self::VERSION;
  }
  
  public function load()
  {
    return false;
  }
  
  public function loadProcessor($bus)
  {
    $request = $bus->getList()->find("request");
    
    if (is_object($request)) {
      $i18n = new I18n_Processor("i18n");
      $request->insertNext("i18n", $i18n);
    }
  }
}
