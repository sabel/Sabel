<?php

Sabel::using('Sabel_Context');
Sabel::using('Sabel_Const');

Sabel::using('Sabel_Map_Facade');

Sabel::using('Sabel_DB_Connection');
Sabel::using('Sabel_Exception_Runtime');
Sabel::using('Sabel_Config_Yaml');
Sabel::fileUsing('sabel/Functions.php');
Sabel::fileUsing('sabel/db/Functions.php');

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
      $conf = Sabel::load('Sabel_Config_Yaml', RUN_BASE . '/config/database.yml');
    } else {
      $cache = Sabel::load('Sabel_Cache_Apc');
      if (!($conf = $cache->read('dbconf'))) {
        $conf = Sabel::load('Sabel_Config_Yaml', RUN_BASE . '/config/database.yml');
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
  
  public function ignition($requestUri = null)
  {
    $builder = Sabel::load('Sabel_Map_Builder', RUN_BASE.'/config/map.yml');
    
    $map = Sabel_Map_Facade::create();
    
    if (is_object($requestUri)) {
      $request = $requestUri;
    } elseif (is_string($requestUri)) {
      $request = new Sabel_Request(null, $requestUri);
    } else {
      $request = Sabel::load('Sabel_Request');
    }
    
    $map->setRequestUri($request);
    $builder->build($map);
    
    $mapEntry = $map->find();
    Sabel_Context::setCurrentMapEntry($mapEntry);
    
    $loader = Sabel::load('Sabel_Controller_Loader');
    $controller = $loader->load();
    
    Sabel_Context::setPageController($controller);
    
    $controller->setup();
    $controller->initialize();
    $controller->initializeReservedNamesOfMethods();
    
    $responses = $controller->execute();
    
    ReflectionCache::create()->destruction();
    
    return array('html' => $controller->rendering(), 'responses' => $responses);
  }
}
