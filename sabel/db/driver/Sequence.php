<?php

/**
 * Sabel_DB_Driver_Sequence
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver_Sequence
{
  public static function getId($db, $driver)
  {
    switch ($db) {
      case "mysql":
        $rows = $driver->setSql("SELECT last_insert_id() AS id")->execute();
        return (isset($rows[0]["id"])) ? (int)$rows[0]["id"] : null;

      case "pgsql":
        if ($model->getIncrementColumn()) {
          $rows = $driver->setSql("SELECT LASTVAL() AS id")->execute();
          return (isset($rows[0]["id"])) ? (int)$rows[0]["id"] : null;
        } else {
          return null;
        }

      case "mssql":
        $rows = $driver->setSql("SELECT SCOPE_IDENTITY() AS id")->execute();
        return (isset($rows[0]["id"])) ? (int)$rows[0]["id"] : null;
    }
  }

  public static function getIbaseGenId($driver, $generator)
  {
    $genName = strtoupper($generator);

    $sql  = "SELECT GEN_ID({$genName}, 1) AS id " . 'FROM RDB$DATABASE';
    $rows = $driver->setSql($sql)->execute();

    return (int)$rows[0]["id"];
  }
}
