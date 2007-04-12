<?php

/**
 * Sabel_DB_Command_Update
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Command_Update extends Sabel_DB_Command_Base
{
  protected $command = Sabel_DB_Command::UPDATE;

  protected function run($executer)
  {
    $model  = $executer->getModel();
    $driver = $executer->getDriver();

    $query = $driver->getSqlClass($model)->buildUpdateSql($driver);
    $conds = $model->getConditionManager()->getUniqueConditions();

    $manager = new Sabel_DB_Condition_Manager();
    foreach ($conds as $condition) $manager->add($condition);

    $query .= $manager->build($driver);

    $executer->setResult($driver->setSql($query)->execute());
  }
}
