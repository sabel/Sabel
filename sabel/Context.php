<?php

/**
 * Sabel Context
 *
 * @category   Core
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Context
{
  private static $includePath = array();
  
  private static $context = null;
  
  private $plugin      = null;
  private $candidate   = null;
  private $destination = null;
  private $controller  = null;
  private $view        = null;
  private $storage     = null;
  private $injector    = null;
  
  private $disableLayout = false;
  
  public static function setContext($context)
  {
    self::$context = $context;
  }
  
  public static function getContext()
  {
    return self::$context;
  }
  
  public function setPlugin($plugin)
  {
    $this->plugin = $plugin;
  }
  
  public function getPlugin()
  {
    return $this->plugin;
  }
  
  public function setStorage($storage)
  {
    $this->storage = $storage;
  }
  
  public function getStorage()
  {
    return $this->storage;
  }
  
  public function setCandidate($candidate)
  {
    $this->candidate = $candidate;
  }
  
  public function getCandidate()
  {
    return $this->candidate;
  }
  
  public function setController($controller)
  {
    $this->controller = $controller;
  }
  
  public function getController()
  {
    return $this->controller;
  }
    
  public function setDestination($destination)
  {
    $this->destination = $destination;
  }
  
  public function getDestination()
  {
    return $this->destination;
  }
  
  public function setInjector($injector)
  {
    $this->injector = $injector;
  }
  
  public function getInjector()
  {
    return $this->injector;
  }
  
  public function setLayoutDisable($bool)
  {
    $this->disableLayout = $bool;
  }
  
  public function isLayoutDisabled()
  {
    return $this->disableLayout;
  }
  
  public function getView()
  {
    return new Sabel_View();
  }
  
  public static function log($message)
  {
    static $log;
    if (!isset($log)) $log = Sabel_Logger_Factory::create();
    $log->log($message);
  }
  
  public static function getLogger()
  {
    return Sabel_Logger_File::singleton();
  }
  
  public static function getCache()
  {
    $config = CachedConfigImpl::create()->get('Memcache');
    return MemCacheImpl::create($config['server']);
  }
  
  public static function addIncludePath($path)
  {
    if (!in_array($path, self::$includePath)) {
      self::$includePath[] = $path;
    }
  }
  
  public static function getIncludePath()
  {
    return self::$includePath;
  }
}
