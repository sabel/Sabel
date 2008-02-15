<?php

/**
 * Sabel_Locale_Browser
 *
 * @category   Locale
 * @package    org.sabel.locale
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Locale_Browser extends Sabel_Object
{
  protected
    $locale    = "",
    $languages = array();
  
  public function __construct($acceptLanguage = null)
  {
    if ($acceptLanguage === null) {
      $acceptLanguage = Sabel_Environment::get("HTTP_ACCEPT_LANGUAGE");
    }
    
    if ($acceptLanguage === "" || $acceptLanguage === null) {
      $this->languages = null;
    } else {
      $languages = array();
      foreach (explode(",", $acceptLanguage) as $lang) {
        if (strpos($lang, ";") === false) {
          $q = "1.0";
        } else {
          list ($lang, $q) = explode(";", $lang);
          $q = str_replace("q=", "", $q);
        }
        
        $languages[$q] = $lang;
      }
      
      krsort($languages, SORT_NUMERIC);
      $this->languages = array_values($languages);
      $this->locale    = $this->languages[0];
    }
  }
  
  public function getLanguages()
  {
    return $this->languages;
  }
  
  public function setLocale($locale)
  {
    $this->locale = $locale;
  }
  
  public function getLocale()
  {
    return $this->locale;
  }
}
