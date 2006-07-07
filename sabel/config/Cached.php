<?php

uses('sabel.config.Interface');
uses('sabel.config.Impl');

class Sabel_Config_Cached implements Sabel_Config_Interface
{
  const CACHE_FILE = 'serverconf.txt';
  protected static $config;

  public static function create()
  {
    if(is_file(self::CACHE_FILE)) {
      $fp = fopen(self::CACHE_FILE, 'r');
      $server = fgets($fp);
      fclose($fp);
      $cache = Sabel_Cache_Memcache::create($server);
      $config = $cache->get('sabel_config_cache');
      if (is_object($config)) {
        self::$config = $config;
      } else {
        self::initializeConfig();
      }
    } else {
      self::initializeConfig();
    }

    return new self();
  }

  protected static function initializeConfig()
  {
    $config = new Sabel_Config_Impl();
    $conf = $config->get('Memcache');
    if (!$fp = @fopen(self::CACHE_FILE, 'w')) {
      throw new Exception(self::CACHE_FILE . " hasn't permission.");
    }
    fwrite($fp, $conf['server']);
    fclose($fp);
    $cache = Sabel_Cache_Memcache::create($conf['server']);
    $cache->add('sabel_config_cache', $config);
    self::$config = $config;
  }

  public function get($key)
  {
    return self::$config->get($key);
  }
}

?>
