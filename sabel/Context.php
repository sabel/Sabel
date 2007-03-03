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
  
  private static $pageController = null;
  private static $candidate = null;
  private static $view = null;
  
  private static $storage = null;
  
  public static function setStorage($storage)
  {
    self::$storage = $storage;
  }
  
  public static function getStorage()
  {
    return self::$storage;
  }
  
  public static function setCurrentCandidate($candidate)
  {
    self::$candidate = $candidate;
  }
  
  public static function getCurrentCandidate()
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
  
  public static function setPageController($controller)
  {
    self::$pageController = $controller;
  }
  
  public static function getPageController()
  {
    return self::$pageController;
  }
  
  public static function setParameter($name, $value)
  {
    self::$parameters[$name] = $value;
  }
  
  public static function getParameter($name)
  {
    return self::$parameters[$name];
  }
  
  public static function log($log)
  {
    Sabel_Logger_File::singleton()->log($log);
  }
  
  public static function getLogger()
  {
    return Sabel_Logger_File::singleton();
  }
  
  public static function getContainer()
  {
    // @TODO alter container make.
    return new Sabel_Container_DI();
  }
  
  public static function getCache()
  {
    $config = CachedConfigImpl::create()->get('Memcache');
    return MemCacheImpl::create($config['server']);
  }

  public static function getStorage()
  {
    return Sabel_Storage_Session::create();
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
