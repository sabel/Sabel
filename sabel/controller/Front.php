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
  
  public function ignition($request = null)
  {
    $builder = new Sabel_Map_Builder(RUN_BASE.'/config/map.yml');
    
    $map = Sabel_Map_Facade::create();
    
    if (is_object($request)) {
      $request = $request;
    } else if (is_string($request)) {
      $request = new Sabel_Request_Request(null, $request);
    } else {
      $request = new Sabel_Request_Request();
    }
    
    $map->setRequestUri($request);
    $builder->build($map);
    
    $mapEntry = $map->find();
    
    $class = Sabel_Controller_Loader::create($mapEntry)->load();
    
    $class->setEntry($mapEntry);
    $class->setup();
    $class->initialize();
    
    return $class->execute();
  }
}