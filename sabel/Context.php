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
  private static $parameters  = array();
  private static $includePath = array();
  
  private static $candidate   = null;
  private static $destination = null;
  private static $controller  = null;
  private static $view        = null;
  private static $storage     = null;
  
  private static $disableLayout = false;
  
  public static function initialize()
  {
    self::setView(new Sabel_View());
  }
  
  public static function setStorage($storage)
  {
    self::$storage = $storage;
  }
  
  public static function getStorage()
  {
    return self::$storage;
  }
  
  public static function setCandidate($candidate)
  {
    self::$candidate = $candidate;
  }
  
  public static function getCandidate()
  {
    return self::$candidate;
  }
  
  public static function setView($view)
  {
    self::$view = $view;
  }
  
  public static function getView()
  {
    return self::$view;
  }
  
  public static function disableLayout()
  {
    self::$disableLayout = true;
  }
  
  public static function isLayoutDisabled()
  {
    return self::$disableLayout;
  }
  
  public static function setController($controller)
  {
    self::$controller = $controller;
  }
  
  public static function getController()
  {
    return self::$controller;
  }
  
  public static function setParameter($name, $value)
  {
    self::$parameters[$name] = $value;
  }
  
  public static function getParameter($name)
  {
    return self::$parameters[$name];
  }
  
  public static function setDestination($destination)
  {
    self::$destination = $destination;
  }
  
  public static function getDestination()
  {
    return self::$destination;
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
