<?php

/**
 * Sabel_Locale_Server
 *
 * @category   Locale
 * @package    org.sabel.locale
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Locale_Server extends Sabel_Object
{
  protected $lcAll   = "";
  protected $locales = array();
  
  public function __construct()
  {
    $lcAll   = setlocale(LC_ALL, 0);
    $locales = array();
    
    foreach (explode(";", $lcAll) as $locale) {
      list ($key, $val) = explode("=", $locale);
      $locales[$key] = $val;
    }
    
    $this->lcAll   = $lcAll;
    $this->locales = $locales;
  }
  
  public function __get($key)
  {
    $key = "LC_" . strtoupper($key);
    
    if (isset($this->locales[$key])) {
      return $this->locales[$key];
    } else {
      return null;
    }
  }
  
  public function getLcAll()
  {
    return $this->lcAll;
  }
  
  public function getLocales()
  {
    return $this->locales;
  }
}
