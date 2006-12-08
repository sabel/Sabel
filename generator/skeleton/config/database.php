<?php

switch (ENVIRONMENT) {
  case PRODUCTION:
    $param = array('default' => array(
                     'driver'   => 'mysql',
                     'host'     => 'localhost',
                     'database' => 'default',
                     'schema'   => 'default',
                     'user'     => 'roor',
                     'password' => '')
                   );
    break;

  case TEST:
    $param = array('default' => array(
                     'driver'   => 'mysql',
                     'host'     => 'localhost',
                     'database' => 'default',
                     'schema'   => 'default',
                     'user'     => 'root',
                     'password' => '')
                   );
    break;

  case DEVELOPMENT:
    $param = array('default' => array(
                     'driver'   => 'mysql',
                     'host'     => 'localhost',
                     'database' => 'default',
                     'schema'   => 'default',
                     'user'     => 'root',
                     'password' => '')
                   );
    break;
}

foreach ($param as $connectName => $values) {
  Sabel_DB_Connection::addConnection($connectName, $values);
}