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
class Sabel_DB_Command_Delete extends Sabel_DB_Command_Base
{
  protected $command = Sabel_DB_Command::DELETE;

  public function run($executer)
  {
    $model  = $executer->getModel();
    $driver = $executer->getDriver();

    $query  = "DELETE FROM " . $model->getTableName();
    $query .= $model->getConditionManager()->build($driver);

    $driver->setSql($query);
  }
}
