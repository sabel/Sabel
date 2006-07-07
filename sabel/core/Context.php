<?php

class Sabel_Core_Context
{
  private static $parameters = array();
  
  private static $includePath = array();

  public static function getController()
  {
    // @TODO configuration.
    return new SabelPageWebController();
  }

  public static function setParameter($name, $value)
  {
    self::$parameters[$name] = $value;
  }

  public static function getParameter($name)
  {
    return self::$parameters[$name];
  }

  public static function getRequestModule()
  {
    return $_REQUEST['module'];
  }

  public static function getRequestAction()
  {
    return $_REQUEST['action'];
  }

  public static function getLogger()
  {
    return FileLogger::singleton();
  }
  
  public static function getContainer()
  {
    return new SabelDIContainer();
  }
  
  public static function getCache()
  {
    $config = CachedConfigImpl::create()->get('Memcache');
    return MemCacheImpl::create($config['server']);
  }
  
  public static function addIncludePath($path)
  {
    self::$includePath[] = $path;
  }
  
  public static function getIncludePath()
  {
    return self::$includePath;
  }
}

Sabel_Core_Context::addIncludePath('');

?>
