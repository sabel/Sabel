<?php

/**
 * Sabel_DB_Command_Begin
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Command_Begin extends Sabel_DB_Command_Base
{
  protected $command = Sabel_DB_Command::BEGIN;

  public function run($executer)
  {
    $model = $executer->getModel();
    $executer->getDriver()->begin($model->getConnectionName());

    return Sabel_DB_Command_Executer::SKIP;
  }
}
