<?php

/**
 * Sabel_Storage_Memcache
 *
 * @category   Storage
 * @package    org.sabel.storage
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Storage_Memcache implements Sabel_Storage
{
  /**
   * @var Memcache
   */
  protected $memcache = null;
  
  /**
   * @var string
   */
  protected $namespace = "";
  
  public function __construct(array $config = array())
  {
    if (!extension_loaded("memcache")) {
      throw new Sabel_Exception_Runtime("memcache extension not loaded.");
    }
    
    $server = (isset($config["server"])) ? $config["server"] : "localhost";
    $port   = (isset($config["port"]))   ? $config["port"]   : 11211;
    
    if (isset($config["namespace"])) {
      $this->namespace = $config["namespace"];
    }
    
    $this->memcache = new Memcache();
    $this->addServer($server, $port);
  }
  
  public function addServer($server, $port = 11211, $weight = 1)
  {
    $this->memcache->addServer($server, $port, true, $weight);
  }
  
  public function setNamespace($namespace)
  {
    if (is_string($namespace)) {
      $this->namespace = $namespace;
    } else {
      $message = __METHOD__ . "() argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function fetch($key)
  {
    $result = $this->memcache->get($this->getKey($key));
    return ($result === false) ? null : $result;
  }
  
  public function store($key, $value, $timeout = null)
  {
    if ($timeout === null) {
      $timeout = (int)ini_get("session.gc_maxlifetime");
    } elseif (!is_numeric($timeout) || $timeout < 1) {
      $message = __METHOD__ . "() invalid timeout value.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    $this->memcache->set($this->getKey($key), $value, false, $timeout);
  }
  
  public function has($key)
  {
    return ($this->fetch($key) !== null);
 }
  
  public function clear($key)
  {
    $this->memcache->delete($this->getKey($key));
  }
  
  private function getKey($key)
  {
    return $this->namespace . $key;
  }
}
