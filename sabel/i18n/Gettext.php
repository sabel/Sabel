<?php

if (!defined("GETTEXT_DEFAULT_DOMAIN")) {
  define("GETTEXT_DEFAULT_DOMAIN", "messages");
}

/**
 * Sabel_I18n_Gettext
 *
 * @category   I18n
 * @package    org.sabel.i18n
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_I18n_Gettext
{
  protected static $ins  = null;
  
  protected $browser     = null;
  protected $fileName    = "";
  protected $localesDir  = "";
  protected $codeset     = array();
  protected $initialized = false;
  
  private function __construct()
  {
    if (defined("LOCALE_DIR_PATH")) {
      $this->localesDir = LOCALE_DIR_PATH;
    } else {
      $this->localesDir = RUN_BASE . DS . "locale";
    }
    
    $fileName = "messages";
    if (defined("PHP_SUFFIX")) $fileName .= PHP_SUFFIX;
    
    $this->fileName = $fileName;
  }
  
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
  
  public function setMessagesFileName($name, $suffix = PHP_SUFFIX)
  {
    $this->fileName = $name . $suffix;
    
    if ($this->initialized) {
      Sabel_I18n_Sabel_Gettext::setMessagesFileName($this->fileName);
    }
    
    return $this;
  }
  
  public function setLocalesDir($path)
  {
    $this->localesDir = $path;
    
    if ($this->initialized) {
      Sabel_I18n_Sabel_Gettext::setLocalesDir($path);
    }
    
    return $this;
  }
  
  public function setCodeset($codeset, $fileName = null, $suffix = PHP_SUFFIX)
  {
    if ($fileName === null) {
      $fileName = $this->fileName;
    } else {
      $fileName = $fileName . $suffix;
    }
    
    $this->codeset[$fileName] = $codeset;
    
    if ($this->initialized) {
      Sabel_I18n_Sabel_Gettext::setCodeset($fileName, $codeset);
    }
    
    return $this;
  }
  
  public function init($force = false)
  {
    if ($this->initialized && !$force) return;
    
    $this->browser = new Sabel_Locale_Browser();
    $this->initialized = true;
    
    if (($languages = $this->browser->getLanguages()) !== null) {
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
      
      $this->browser->setLocale($locale);
      
      if (isset($this->codeset[$this->fileName])) {
        $codeset = $this->codeset[$this->fileName];
      } else {
        $codeset = null;
      }
      
      Sabel_I18n_Sabel_Gettext::initialize($this->fileName, $this->localesDir, $codeset, $locale);
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
        $code = array("<?php" . PHP_EOL . PHP_EOL . '$locales = array(');
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
    $dir  = $this->localesDir . DS;
    $dirs = array();
    
    foreach (scandir($dir) as $item) {
      if ($item === "." || $item === "..") continue;
      if (is_dir($dir . $item)) $dirs[$item] = 1;
    }
    
    return $dirs;
  }
  
  public function setBrowser($browser)
  {
    $this->browser = $browser;
  }
  
  public function getBrowser()
  {
    return $this->browser;
  }
}
