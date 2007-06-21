<?php

function get_db_params($env = null)
{
  $env = ($env === null) ? ENVIRONMENT : $env;

  switch ($env) {
    case PRODUCTION:
      $params = array("default" => array(
                        "driver"   => "mysql",
                        "host"     => "localhost",
                        "database" => "default",
                        "user"     => "root",
                        "password" => "")
                     );
      break;

    case TEST:
      $params = array("default" => array(
                        "driver"   => "mysql",
                        "host"     => "localhost",
                        "database" => "default",
                        "user"     => "root",
                        "password" => "")
                     );
      break;

    case DEVELOPMENT:
      $params = array("default" => array(
                        "driver"   => "mysql",
                        "host"     => "localhost",
                        "database" => "default",
                        "user"     => "root",
                        "password" => "")
                     );
      break;
  }

  return $params;
}

Sabel_DB_Config::initialize();

