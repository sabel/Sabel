<?php

/**
 * Sabel_I18n_Sabel_Gettext
 *
 * @category   i18n
 * @package    org.sabel.i18n
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_I18n_Sabel_Gettext
{
  private static $domain     = "";
  private static $locale     = null;
  private static $domainPath = array();
  private static $codeSet    = array();
  private static $messages   = array();
  
  public static function initialize($domain, $path, $codeSet, $locale)
  {
    self::$domain = $domain;
    self::$locale = $locale;
    self::$codeSet[$domain] = $codeSet;
    
    self::setDomainPath($domain, $path);
  }
  
  public static function _($msgid)
  {
    if (self::$locale === null) {
      return $msgid;
    } else {
      $domain   = self::$domain;
      $messages = self::getMessages(self::$domainPath[$domain]);
      
      if (isset($messages[$msgid])) {
        $message = ($messages[$msgid] === "") ? $msgid : $messages[$msgid];
      } else {
        $message = $msgid;
      }
      
      if (isset(self::$codeSet[$domain])) {
        $from = self::getInternalEncoding();
        return mb_convert_encoding($message, self::$codeSet[$domain], $from);
      } else {
        return $message;
      }
    }
  }
  
  public static function setDomain($domain)
  {
    self::$domain = $domain;
  }
  
  public static function setDomainPath($domain, $path)
  {
    if (substr($path, -1, 1) !== DIR_DIVIDER) $path .= DIR_DIVIDER;
    self::$domainPath[$domain] = $path;
  }
  
  public static function setCodeset($domain, $codeSet)
  {
    self::$codeSet[$domain] = $codeSet;
  }
  
  public static function setLocale($locale)
  {
    self::$locale = $locale;
  }
  
  private static function getMessages($path)
  {
    $locale = self::$locale;
    $domain = self::$domain;
    
    if (isset(self::$messages[$locale][$domain])) {
      return self::$messages[$locale][$domain];
    } else {
      $filePath = $path . $locale . DIR_DIVIDER . "LC_MESSAGES"
                . DIR_DIVIDER . $domain . ".php";
                
      if (is_readable($filePath)) {
        include ($filePath);
        return self::$messages[$locale][$domain] = $messages;
      } else {
        if (!IS_WIN && strpos($locale, "_") !== false) {
          list ($lang) = explode("_", $locale);
          $filePath = $path . $lang . DIR_DIVIDER . "LC_MESSAGES"
                    . DIR_DIVIDER . $domain . ".php";
                    
          if (is_readable($filePath)) {
            include ($filePath);
            return self::$messages[$locale][$domain] = $messages;
          }
        }
        
        return self::$messages[$locale][$domain] = false;
      }
    }
  }
  
  private static function getInternalEncoding()
  {
    static $encoding = null;
    
    if ($encoding === null) {
      return $encoding = ini_get("mbstring.internal_encoding");
    } else {
      return $encoding;
    }
  }
}

function _($msgid)
{
  return Sabel_I18n_Sabel_Gettext::_($msgid);
}

function gettext($msgid)
{
  return Sabel_I18n_Sabel_Gettext::_($msgid);
}
