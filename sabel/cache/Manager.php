<?php

/**
 * Cache Manager
 *
 * @category   Cache
 * @package    org.sabel.cache
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cache_Manager
{
  private $support = array();
  
  public function __construct()
  {
    $this->support["apc"]      = extension_loaded("apc");
    $this->support["xcache"]   = extension_loaded("xcache");
    $this->support["memcache"] = extension_loaded("memcache");
  }
  
  public function create($type = "")
  {
    $instance = null;
    
    if (ENVIRONMENT === DEVELOPMENT || ENVIRONMENT === TEST) {
      $instance = Sabel_Cache_Null::create();
    } elseif (isset($this->support["apc"]) && $this->support["apc"] === true) {
      $instance = Sabel_Cache_Apc::create();
    } elseif (isset($this->support["xcache"]) && $this->support["xcache"] === true) {
      $instance = Sabel_Cache_Xcache::create();
    } elseif (isset($this->support["memcache"]) && $this->support["memcache"] === true) {
      $instance = Sabel_Cache_Memcache::create();
    } else {
      $instance = Sabel_Cache_File::create();
    }
    
    return $instance;
  }
}