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
        return (int)$rows[0]["id"];

      case "pgsql":
        list ($idColumn, $tblName, $values) = self::getProperties($model);

        if ($idColumn !== null && !isset($values[$idColumn])) {
          $sql  = "SELECT nextval('{$tblName}_{$idColumn}_seq') AS id";
          $rows = $driver->setSql($sql)->execute();
          if (($id = (int)$rows[0]["id"]) === 0) {
            throw new Exception("{$tblName}_{$idColumn}_seq is not found.");
          } else {
            $values[$idColumn] = $id;
            $model->setSaveValues($values);
            return $id;
          }
        }
        break;

      case "mssql":
        $rows = $driver->setSql("SELECT SCOPE_IDENTITY() AS id")->execute();
        return (int)$rows[0]["id"];

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
