<?php

abstract class Config
{
  abstract public function get($key);
}

class ConfigImpl extends Config
{
  private $data;

  public function initialize()
  {
    $this->data = Spyc::YAMLLoad('app/configs/config.yml');
  }

  public function get($key)
  {
    return $this->data[$key];
  }
}

class CachedConfig extends Config
{
  private $config;
  private $cache;

  public function __construct($configImpl = null)
  {
    $this->cache = MemCacheImpl::create();

    if (is_null($configImpl)) {
      $this->config = new ConfigImpl();
    } else {
      $cachedConfigImpl = $this->cache->get('configobject');
      if ($cachedConfigImpl) {
	$this->config = $cachedConfigImpl;
      } else {
	$configImpl->initialize();
	$this->config = $configImpl;
	$this->cache->add('configobject', $configImpl);
      }
    }
  }

  public function get($key)
  {
    return $this->config->get($key);
  }
}

?>