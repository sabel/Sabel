<?php

/**
 * Sabel_DB_Command_Delete
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Command_Delete
{
  const COMMAND = "DELETE";

  public static function build($command)
  {
    $result = Sabel_DB_Command_Before::execute(self::COMMAND, $command);
    if ($result !== Sabel_DB_Command_Before::CONTINUOUS) return;

    $model  = $command->getModel();
    $driver = $command->getDriver();

    $query  = "DELETE FROM " . $model->getTableName();
    $query .= $model->getConditionManager()->build($driver);
    $driver->setSql($query);

    Sabel_DB_Command_After::execute(self::COMMAND, $command);
  }
}
