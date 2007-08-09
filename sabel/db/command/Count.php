<?php

/**
 * Sabel_DB_Command_Count
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Command_Count extends Sabel_DB_Command_Base
{
  protected $command = Sabel_DB_Command::COUNT;

  protected function run($executer)
  {
    $query  = "SELECT COUNT(*) AS cnt FROM ";
    $model  = $executer->getModel();
    $driver = $executer->getDriver();
    $args   = $executer->getArguments();
    $query .= $model->getTableName();

    if (isset($args[0])) $query .= $args[0];

    $conditionManager = $model->loadConditionManager();

    if (!$conditionManager->isEmpty()) {
      $query .= " " . $conditionManager->build($driver);
    }

    $query = $driver->loadConstraintSqlClass()->build($query, array("limit" => 1));
    $rows  = $driver->setSql($query)->execute();
    $count = (int)$rows[0]["cnt"];
    $executer->setResult($count);

    return $count;
  }
}
