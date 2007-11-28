<?php

if (!defined("GETTEXT_DEFAULT_DOMAIN")) {
  define("GETTEXT_DEFAULT_DOMAIN", "messages");
}

if (!defined("GETTEXT_DEFAULT_DOMAIN_PATH")) {
  define("GETTEXT_DEFAULT_DOMAIN_PATH", RUN_BASE . DS . "locale");
}

/**
 * Sabel_I18n_Gettext
 *
 * @category   i18n
 * @package    org.sabel.i18n
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_I18n_Gettext
{
  const GETTEXT     = 1;
  const PHP_GETTEXT = 2;
  const SABEL       = 3;
  
  protected static $ins  = null;
  protected static $type = null;
  
  protected $browser     = null;
  protected $domain      = GETTEXT_DEFAULT_DOMAIN;
  protected $domainPath  = array(GETTEXT_DEFAULT_DOMAIN => GETTEXT_DEFAULT_DOMAIN_PATH);
  protected $codeset     = array();
  protected $initialized = false;
  
  private function __construct() {}
  
  public static function getInstance()
  {
    if (self::$ins === null) {
      self::$ins = new self();
    }
    
    return self::$ins;
  }
  
  public function isInitialized()
  {
    return $this->initialized;
  }
  
  public function setDomain($domain)
  {
    $this->domain = $domain;
    
    if (!isset($this->domainPath[$domain])) {
      $this->domainPath[$domain] = GETTEXT_DEFAULT_DOMAIN_PATH;
    }
    
    if ($this->initialized) {
      if (self::$type === self::SABEL) {
        $path = $this->domainPath[$domain];
        Sabel_I18n_Sabel_Gettext::setDomain($domain);
        Sabel_I18n_Sabel_Gettext::setDomainPath($domain, $path);
      } else {
        textdomain($domain);
      }
    }
    
    return $this;
  }
  
  public function setDomainPath($path, $domain = null)
  {
    if ($domain === null) $domain = $this->domain;
    $this->domainPath[$domain] = $path;
    
    if ($this->initialized) {
      if (self::$type === self::SABEL) {
        Sabel_I18n_Sabel_Gettext::setDomainPath($domain, $path);
      } else {
        bindtextdomain($domain, $path);
      }
    }
    
    return $this;
  }
  
  public function setCodeset($codeset, $domain = null)
  {
    if ($domain === null) $domain = $this->domain;
    $this->codeset[$domain] = $codeset;
    
    if ($this->initialized) {
      if (self::$type === self::SABEL) {
        Sabel_I18n_Sabel_Gettext::setCodeset($domain, $codeset);
      } else {
        bind_textdomain_codeset($domain, $codeset);
      }
    }
    
    return $this;
  }
  
  public function init($type = self::SABEL)
  {
    if ($this->initialized) return;

    $browser = new Sabel_Locale_Browser();
    
    if (extension_loaded("gettext")) {
      $type   = self::GETTEXT;
      $config = CONFIG_DIR_PATH . DS . "locales.php";
      Sabel::fileUsing($config, true);
    } else {
      $dir = dirname(__FILE__) . DS;
      if ($type === self::SABEL) {
        Sabel::fileUsing($dir . "sabel" . DS . "Gettext.php", true);
      } else {
        Sabel::fileUsing($dir . "php-gettext" . DS . "gettext.inc", true);
      }
    }
    
    self::$type = $type;
    
    if (($languages = $browser->getLanguages()) !== null) {
      if (self::$type === self::GETTEXT) {
        $this->gettextInit($languages);
      } else {
        $dirs   = $this->getLocaleDirs();
        $locale = null;
        foreach ($languages as $language) {
          if (strpos($language, "-") !== false) {
            list ($ll, $cc) = explode("-", $language);
            $language = $ll . "_" . strtoupper($cc);
          } else {
            $ll = "";
          }
          
          if (isset($dirs[$language])) {
            $locale = $language;
            break;
          } elseif (isset($dirs[$ll])) {
            $locale = $ll;
            break;
          }
        }
        
        if (self::$type === self::SABEL) {
          $this->sabelInit($locale);
        } else {
          if ($locale !== null) $this->phpGettextInit();
        }
        
        $browser->setLocale($locale);
      }
    }
    
    $this->browser = $browser;
    $this->initialized = true;
  }
  
  private function sabelInit($locale)
  {
    $domain  = $this->domain;
    $path    = $this->domainPath[$domain];
    $codeset = null;
    
    if (isset($this->codeset[$domain])) {
      $codeset = $this->codeset[$domain];
    }
    
    Sabel_I18n_Sabel_Gettext::initialize($domain, $path, $codeset, $locale);
  }
  
  private function phpGettextInit()
  {
    $domain = $this->domain;
    bindtextdomain($domain, $this->domainPath[$domain]);
    textdomain($domain);
    
    if (!empty($this->codeset)) {
      bind_textdomain_codeset($domain, $this->codeset[$domain]);
    }
  }
  
  private function getLocaleDirs()
  {
    if (ENVIRONMENT === PRODUCTION) {
      $cache = CACHE_DIR_PATH . DS . "ldirs" . PHP_SUFFIX;
      if (is_file($cache)) {
        include ($cache);
        $dirs = $locales;
      } else {
        $dirs = $this->_getLocaleDirs();
        $code = array("<?php\n" . '$locales = array(');
        foreach (array_keys($dirs) as $dir) {
          $code[] = '"' . $dir . '" => 1,';
        }
        file_put_contents($cache, implode("", $code) . ");");
      }
    } else {
      $dirs = $this->_getLocaleDirs();
    }
    
    return $dirs;
  }
  
  private function _getLocaleDirs()
  {
    $dir  = RUN_BASE . DS . "locale" . DS;
    $dirs = array();
    
    foreach (scandir($dir) as $item) {
      if ($item === "." || $item === "..") continue;
      if (is_dir($dir . $item)) $dirs[$item] = 1;
    }
    
    return $dirs;
  }
  
  private function gettextInit($languages)
  {
    $domain = $this->domain;
    
    foreach ($languages as $language) {
      $locale = locales($language);
      
      if ($locale === "" || $locale === null) continue;
      if (setlocale(LC_ALL, $locale) === false) continue;
      
      bindtextdomain($domain, $this->domainPath[$domain]);
      textdomain($domain);
      
      if (!empty($this->codeset)) {
        bind_textdomain_codeset($domain, $this->codeset[$domain]);
      }
      
      $this->browser->setLocale($locale);
      break;
    }
  }
  
  public function setBrowser($browser)
  {
    $this->browser = $browser;
  }
  
  public function getBrowser()
  {
    return $this->browser;
  }
  
  public function isGettext()
  {
    return (self::$type === self::GETTEXT);
  }
  
  public function isPhpGettext()
  {
    return (self::$type === self::PHP_GETTEXT);
  }
  
  public function isSabel()
  {
    return (self::$type === self::SABEL);
  }
}
