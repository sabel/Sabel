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
class Sabel_DB_Command_Select extends Sabel_DB_Command_Base
{
  protected $command = Sabel_DB_Command::SELECT;

  protected function run($executer)
  {
    $model   = $executer->getModel();
    $driver  = $executer->getDriver();

    $tblName = $model->getTableName();
    $query   = "SELECT " . $model->getProjection() . " FROM " . $tblName;
    $manager = $model->getConditionManager();

    if (is_object($manager)) $query .= $manager->build($driver);

    if ($constraints = $model->getConstraints()) {
      $query = $driver->getConstraintSqlClass()->build($query, $constraints);
    }

    $executer->setResult($driver->setSql($query)->execute());
  }
}
