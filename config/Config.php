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
  protected static $config;

  public static function create()
  {
    if(is_file('serverconf.txt')) {
      $fp = fopen('serverconf.txt', 'r');
      $server = fgets($fp);
      fclose($fp);
      $cache = MemCacheImpl::create($server);
      self::$config = $cache->get('config');
    } else {
      $config = new ConfigImpl();
      $conf = $config->get('Memcache');
      $fp = fopen('serverconf.txt', 'a+');
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