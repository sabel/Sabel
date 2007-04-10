<?php

/**
 * Sabel_DB_Command_Select
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Command_Select
{
  const COMMAND = "SELECT";

  public static function build($command)
  {
    $result = Sabel_DB_Command_Before::execute(self::COMMAND, $command);
    if ($result !== Sabel_DB_Command_Before::CONTINUOUS) return;

    $model   = $command->getModel();
    $driver  = $command->getDriver();
    $query   = $driver->getSqlClass($model)->buildSelectSql($driver);
    $manager = $model->getConditionManager();

    if (is_object($manager)) $query .= $manager->build($driver);

    if ($constraints = $model->getConstraints()) {
      $query = $driver->getConstraintSqlClass()->build($query, $constraints);
    }

    $driver->setSql($query);
    Sabel_DB_Command_After::execute(self::COMMAND, $command);
  }
}
