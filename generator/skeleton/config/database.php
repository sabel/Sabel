<?php

switch (ENVIRONMENT) {
  case 'production':
    $param = array('default' => array(
                     'driver'   => 'mysql',
                     'host'     => 'localhost',
                     'database' => 'default',
                     'schema'   => 'default',
                     'user'     => 'roor',
                     'password' => '')
                   );
    break;

  case 'test':
    $param = array('default' => array(
                     'driver'   => 'mysql',
                     'host'     => 'localhost',
                     'database' => 'default',
                     'schema'   => 'default',
                     'user'     => 'root',
                     'password' => '')
                   );
    break;

  case 'development':
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
