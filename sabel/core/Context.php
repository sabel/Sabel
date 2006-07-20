<?php

class Sabel_Core_Context
{
  private static $parameters  = array();
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

?>