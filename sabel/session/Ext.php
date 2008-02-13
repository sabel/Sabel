<?php

/**
 * Sabel_Session_Ext
 *
 * @abstract
 * @category   Session
 * @package    org.sabel.session
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Session_Ext extends Sabel_Session_Abstract
{
  protected
    $sessionName    = "",
    $maxLifetime    = 0,
    $useOnlyCookies = false,
    $useCookies     = false,
    $hashFunction   = "md5";
  
  protected function readSessionSettings()
  {
    $maxLifetime = ini_get("session.gc_maxlifetime");
    $this->maxLifetime    = ($maxLifetime === "") ? 0 : (int)$maxLifetime;
    $this->sessionName    = ini_get("session.name");
    $this->useOnlyCookies = (ini_get("session.use_only_cookies") === "1");
    $this->useCookies     = (ini_get("session.use_cookies")   === "1");
    $this->hashFunction   = (ini_get("session.hash_function") === "1") ? "sha1" : "md5";
  }
  
  protected function createSessionId()
  {
    $func = $this->hashFunction;
    return $func(uniqid(mt_rand(), true));
  }
  
  protected function getSessionId()
  {
    $sesName = $this->sessionName;
    
    if ($this->useOnlyCookies) {
      return (isset($_COOKIE[$sesName])) ? $_COOKIE[$sesName] : "";
    }
    
    if ($this->useCookies && isset($_COOKIE[$sesName])) {
      return $_COOKIE[$sesName];
    }
    
    if (isset($_SERVER["REQUEST_METHOD"])) {
      switch (strtolower($_SERVER["REQUEST_METHOD"])) {
        case "post":
          $sessionId = (isset($_POST[$sesName])) ? $_POST[$sesName] : "";
          break;
          
        case "get":
          $sessionId = (isset($_GET[$sesName])) ? $_GET[$sesName] : "";
          break;
          
        default:
          $sessionId = "";
          break;
      }
      
      if ($sessionId !== "") {
        define("SID", $sesName . "=" . $sessionId);
      }
      
      return $sessionId;
    } else {
      return "";
    }
  }
  
  protected function setSessionIdToCookie()
  {
    if ($this->useOnlyCookies || $this->useCookies) {
      setcookie($this->sessionName, $this->sessionId, 0, "/");
    }
  }
}
