<?php

/**
 * Sabel_DB_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver
{
  public static function create($connectionName = "default")
  {
    $driverName = Sabel_DB_Config::getDriverName($connectionName);

    if (strpos($driverName, "pdo") === false) {
      $className = "Sabel_DB_" . ucfirst($driverName) . "_Driver";
    } else {
      list (, $db) = explode("-", $driverName);
      $className = "Sabel_DB_Pdo_Driver_" . ucfirst($db);
    }

    if (Sabel_DB_Transaction::isActive()) {
      $connection = Sabel_DB_Transaction::getConnection($connectionName);
      if ($connection === null) {
        $driver = new $className($connectionName);
        Sabel_DB_Transaction::begin($driver, $connectionName);
      } else {
        $driver = new $className($connectionName, $connection);
        $driver->autoCommit(false);
      }
    } else {
      $driver = new $className($connectionName);
    }

    return $driver;
  }
}
