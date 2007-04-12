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
  public static function getId($db, $command)
  {
    $model  = $command->getModel();
    $driver = $command->getDriver();

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

      case "ibase":
        list ($idColumn, $tblName, $values) = self::getProperties($model);

        if ($idColumn !== null && !isset($values[$idColumn])) {
          $genName = strtoupper("{$tblName}_{$idColumn}_gen");
          $sql  = "SELECT GEN_ID({$genName}, 1) AS id " . 'FROM RDB$DATABASE';
          $rows = $driver->setSql($sql)->execute();
          $id = (int)$rows[0]["id"];
          $values[$idColumn] = $id;
          $model->setSaveValues($values);
          return $id;
        }
        break;
    }
  }

  protected static function getProperties($model)
  {
    return array($model->getIncrementColumn(),
                 $model->getTableName(),
                 $model->getSaveValues());
  }
}
