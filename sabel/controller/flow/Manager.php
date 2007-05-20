<?php

/**
 * Sabel_Controller_Flow_Manager
 *
 * @category   Flow
 * @package    org.sabel.controller.flow
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Flow_Manager
{
  const PREFIX = "flow_";
  
  private
    $token   = "",
    $storage = null;
  
  public function __construct()
  {
    $this->storage = Sabel_Context::getStorage();
    $this->initializeToken();
  }
  
  /**
   * @todo remove $_REQUEST dependecy because it's experimental code.
   */
  private final function initializeToken()
  {
    $storage = $this->storage;
    
    if (array_key_exists("token", $_REQUEST)) {
      $token = $_REQUEST["token"];
      if (!$storage->has($this->key($token))) {
        $token = $this->createToken();
      }
      $this->token = $token;
    } else {
      $token = $this->createToken();
      if ($storage->has($this->key($token))) {
        $this->token = $this->createToken();
      } else {
        $this->token = $token;
      }
    }
  }
  
  public function getToken()
  {
    return $this->token;
  }
  
  /**
   * save flow to storage with token
   * 
   * @param Sabel_Controller_Flow $flow
   * @return instance of Sabel_Controller_Flow
   */
  public function save(Sabel_Controller_Flow $flow)
  {
    $this->storage->write($this->key(), $flow);
    return $flow;
  }
  
  /**
   *
   * @return instance of Sabel_Controller_Flow
   */
  public function restore()
  {
    return $this->storage->read($this->key());
  }
  
  public function remove()
  {
    $this->storage->remove($this->key());
  }
  
  protected final function key($token = null)
  {
    if ($token === null) {
      return self::PREFIX . $this->token;
    } else {
      return self::PREFIX . $token;
    }
  }
  
  protected function createToken()
  {
    $token = "";
    
    $token  = substr(sha1(uniqid(microtime().mt_rand(), true)), 0, 5);
    $token .= substr(sha1(uniqid(microtime().mt_rand(), true)), 35, 40);
    
    return $token;
  }
}
