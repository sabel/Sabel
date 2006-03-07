<?php

abstract class Config
{
  abstract public function get($key);
}

class ConfigImpl extends Config
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

class CachedConfigImpl extends Config
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
      self::$config = $cache->get('config');
    } else {
      $config = new ConfigImpl();
      $conf = $config->get('Memcache');
      if (!$fp = @fopen(self::CACHE_FILE, 'a+')) {
	throw new Exception(self::CACHE_FILE . " has't permission.");
      }
      fwrite($fp, $conf['server']);
      fclose($fp);
      $cache = MemCacheImpl::create($conf['server']);
      $cache->add('config', $config);
      self::$config = $config;
    }

    return new self();
  }

  public function get($key)
  {
    return self::$config->get($key);
  }
}

?>