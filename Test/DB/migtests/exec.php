<?php

define("RUN_BASE", getcwd());
require_once ("/usr/local/lib/php/Sabel/Sabel.php");
require_once (RUN_BASE . "/config/INIT.php");
require_once (RUN_BASE . "/config/environment.php");

$configs = array("sqlite" => array(
                   "package"  => "sabel.db.pdo.sqlite",
                   "database" => "/home/ebine/test.sq3"),
                 "mysql" => array(
                   "package"  => "sabel.db.mysql",
                   "host"     => "127.0.0.1",
                   "database" => "sdb_test",
                   "port"     => "3306",
                   "user"     => "root",
                   "password" => ""),
                 "pgsql" => array(
                   "package"  => "sabel.db.pgsql",
                   "host"     => "127.0.0.1",
                   "database" => "sdb_test",
                   "user"     => "pgsql",
                   "password" => "pgsql"),
                 "oci" => array(
                   "package"  => "sabel.db.oci",
                   "host"     => "127.0.0.1",
                   "database" => "XE",
                   "schema"   => "DEVELOP",
                   "user"     => "DEVELOP",
                   "password" => "DEVELOP")
                 );

foreach ($configs as $key => $param) {
  Sabel_DB_Config::add($key, $param);
}

$args    = $_SERVER["argv"];
$path    = $args[1];
$conName = $args[2];
$type    = $args[3];

$schema = Sabel_DB::createMetadata($conName);
$stmt = Sabel_DB::createStatement($conName);

Sabel_DB_Migration_Manager::setSchema($schema);
Sabel_DB_Migration_Manager::setStatement($stmt);
Sabel_DB_Migration_Manager::setDirectory(RUN_BASE . "/migration/tmp");
Sabel_DB_Migration_Manager::setApplyMode($type);

$dirs = explode(".", Sabel_DB_Config::getPackage($conName));
$className = implode("_", array_map("ucfirst", $dirs)) . "_Migration";
$mig = new $className();
$mig->execute($path);
