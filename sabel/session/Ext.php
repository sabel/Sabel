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
    $useCookies     = false;
  
  public function start()
  {
    if ($this->started) return false;
    
    $sessionId = $this->initSession();
    if ($sessionId === false) return false;
    
    if ($this->sessionId === "") {
      $this->sessionId  = $sessionId;
      $this->attributes = $this->getSessionData($this->sessionId);
    } else {
      $this->attributes = $this->getSessionData($sessionId);
    }
    
    $this->initialize();
    
    return true;
  }
  
  protected function readSessionSettings()
  {
    $maxLifetime = ini_get("session.gc_maxlifetime");
    $this->maxLifetime    = ($maxLifetime === "") ? 0 : (int)$maxLifetime;
    $this->sessionName    = ini_get("session.name");
    $this->useOnlyCookies = (ini_get("session.use_only_cookies") === "1");
    $this->useCookies     = (ini_get("session.use_cookies")   === "1");
  }
  
  protected function initSession()
  {
    $sesName = $this->sessionName;
    
    if ($this->useOnlyCookies) {
      if (isset($_COOKIE[$sesName])) {
        return $_COOKIE[$sesName];
      } else {
        $sessionId = $this->createSessionId();
        $this->setSessionIdToCookie($sessionId);
        return $sessionId;
      }
    }
    
    if ($this->useCookies && isset($_COOKIE[$sesName])) {
      return $_COOKIE[$sesName];
    }
    
    $method = strtolower(Sabel_Environment::get("REQUEST_METHOD"));
    if ($method !== "get" && $method !== "post") return false;
    
    $_VARS = ($method === "get") ? $_GET : $_POST;
    $sessionId = (isset($_VARS[$sesName])) ? $_VARS[$sesName] : $this->createSessionId();
    
    if ($this->useCookies) {
      $this->setSessionIdToCookie($sessionId);
    }
    
    return $sessionId;
  }
  
  protected function setSessionIdToCookie($sessionId)
  {
    if ($this->useOnlyCookies || $this->useCookies) {
      setcookie($this->sessionName, $sessionId, 0, "/");
    }
  }
}
