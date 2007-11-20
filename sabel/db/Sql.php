<?php

/**
 * Sabel_DB_Sql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql
{
  const SELECT = 0x01;
  const INSERT = 0x02;
  const UPDATE = 0x04;
  const DELETE = 0x08;
  const QUERY  = 0x10;
  
  public static function create($tblName, $connectionName, $type = self::QUERY)
  {
    $driverName = Sabel_DB_Config::getDriverName($connectionName);
    
    if (substr($driverName, 0, 4) === "pdo-") {
      $className = "Sabel_DB_Pdo_Sql";
    } else {
      $className  = "Sabel_DB_" . ucfirst($driverName) . "_Sql";
    }
    
    $sql = new $className($connectionName);
    return $sql->table($tblName)->setType($type);
  }
}
