<?php

/**
 * Sabel_DB_Command_Join
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Command_Join extends Sabel_DB_Command_Base
{
  protected $command = Sabel_DB_Command::JOIN;

  protected function run($executer)
  {
    $args = $executer->getArguments();
    $joinQuery = $args[0];

    $model  = $executer->getModel();
    $driver = $executer->getDriver();
    $conditionManager = $model->loadConditionManager();

    if (!$conditionManager->isEmpty()) {
      $joinQuery .= " " . $conditionManager->build($driver);
    }

    if ($constraints = $model->getConstraints()) {
      $joinQuery = $driver->loadConstraintSqlClass()->build($joinQuery, $constraints);
    }

    $result = $driver->setSql($joinQuery)->execute();
    $executer->setResult($result);
    return $result;
  }
}
