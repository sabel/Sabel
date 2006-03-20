<?php

class ConfigImpl
{
  private $data;

  public function __construct()
  {
    $parser = new Spyc();
    $this->data = $parser->load('app/configs/config.yml');
  }

  public function get($key)
  {
    return $this->data[$key];
  }
}

class CachedConfigImpl
{
  const CACHE_FILE = 'serverconf.txt';
  protected static $config;

  public static function create()
  {
    if(is_file(self::CACHE_FILE)) {
      $fp = fopen(self::CACHE_FILE, 'r');
      $server = fgets($fp);
      fclose($fp);
      $cache = MemCacheImpl::create($server);
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
    $config = new ConfigImpl();
    $conf = $config->get('Memcache');
    if (!$fp = @fopen(self::CACHE_FILE, 'w')) {
      throw new Exception(self::CACHE_FILE . " has't permission.");
    }
    fwrite($fp, $conf['server']);
    fclose($fp);
    $cache = MemCacheImpl::create($conf['server']);
    $cache->add('sabel_config_cache', $config);
    self::$config = $config;
  }

  public function get($key)
  {
    return self::$config->get($key);
  }
}

?>
