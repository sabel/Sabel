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
    if (ENVIRONMENT === 'development') {
      $conf = new Sabel_Config_Yaml(RUN_BASE . '/config/database.yml');
      $dbc = $conf->read(ENVIRONMENT);
    } else {
      $cache = new Sabel_Cache_Apc();
      if (!($conf = $cache->read('dbconf'))) {
        $conf = new Sabel_Config_Yaml(RUN_BASE . '/config/database.yml');
        $cache->write('dbconf', $conf);
      }
    }
    
    $dbc = $conf->read(ENVIRONMENT);
    if (isset($dbc['driver'])) {
      Sabel_DB_Connection::addConnection('default', $dbc);
    } else {
      foreach ($dbc as $connectionName => $connection) {
        Sabel_DB_Connection::addConnection($connectionName, $connection);
      }
    }
  }
  
  public function ignition()
  {
    $map = Container::create()->load('sabel.controller.Map');
    $map->load();
    $entry = $map->find();
    
    // @todo performance tuning here. taken 40ms
    $class = Sabel_Controller_Loader::create($entry)->load();
    $class->execute();
  }
}