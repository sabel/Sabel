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
    $this->support["memcache"] = extension_loaded("memcache");
  }
  
  public function create($type = "")
  {
    $instance = null;
    
    if (isset($this->support["apc"]) && $this->support["apc"] === true) {
      Sabel::using("Sabel_Cache_Apc");
      $instance = new Sabel_Cache_Apc();
    } else {
      Sabel::using("Sabel_Cache_File");
      $instance = new Sabel_Cache_File();
    }
    
    return $instance;
  }
}