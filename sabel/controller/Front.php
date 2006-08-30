<?php

/**
 * Front Controller Class.
 *
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Front
{
  public function __construct()
  {
    $cache = new Sabel_Cache_Apc();
    if (! ($conf = $cache->read('dbconf'))) {
      $conf = new Sabel_Config_Yaml(RUN_BASE . '/config/database.yml');
      $cache->write('dbconf', $conf);
    }
    
    $envConf = new Sabel_Config_Yaml(RUN_BASE . '/config/environment.yml');
    $env = $envConf->read('environment');
    $dev = $conf->read($env);
    $fm = '%s:host=%s;dbname=%s';
    $con['dsn']  = sprintf($fm, $dev['driver'], $dev['host'], $dev['database']);
    $con['user'] = $dev['user'];
    $con['pass'] = $dev['password'];
    
    Sabel_DB_Connection::addConnection('default', 'pdo', $con);
  }
  
  public function ignition()
  {
    $entry = Sabel_Core_Router::create()->routing();
    $class = Sabel_Controller_Loader::create($entry)->load();
    $class->execute();
  }
}