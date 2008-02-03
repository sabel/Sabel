<?php

/**
 * Acl_Config_Abstract
 *
 * @abstract
 * @category   Addon
 * @package    addon.acl
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Acl_Config_Abstract extends Sabel_Object
{
  protected
    $isAllow = false,
    $authUri = "";
    
  public function isPublic()
  {
    return ($this->isAllow === true);
  }
  
  public function isAllow($role = null)
  {
    if ($this->isPublic()) {
      return true;
    } else {
      $ar  = $this->isAllow;
      $or  = (strpos($ar, "|") !== false);
      $and = (strpos($ar, "&") !== false);
      
      if ($or && $and) {
        throw new Sabel_Exception_Runtime("invalid acl config.");
      }
      
      if ($or) {
        $ors = explode("|", $ar);
        foreach ($ors as $r) {
          if (in_array($r, $role)) return true;
        }
        
        return false;
      } elseif ($and) {
        $ands = explode("&", $ar);
        foreach ($ands as $r) {
          if (!in_array($r, $role)) return false;
        }
        
        return true;
      } else {
        return in_array($ar, $role);
      }
    }
  }
  
  public function allow($role = null)
  {
    if ($role === null) {
      $this->isAllow = true;
    } elseif (is_string($role)) {
      $this->isAllow = $role;
    } else {
      $message = "argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    return $this;
  }
  
  public function authUri($uri = null)
  {
    if ($uri === null) {
      return ($this->authUri === "") ? null : $this->authUri;
    } else {
      $this->authUri = $uri;
    }
  }
}
