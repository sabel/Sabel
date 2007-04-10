<?php

/**
 * Sabel_DB_Command_ArrayInsert
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Command_ArrayInsert extends Sabel_DB_Command_Base
{
  protected $command = Sabel_DB_Command::ARRAY_INSERT;

  public function run($executer)
  {
    $model  = $executer->getModel();
    $driver = $executer->getDriver();
    $query  = $driver->getSqlClass($model)->buildInsertSql($driver);

    $driver->setSql($driver->getSqlClass($model)->buildInsertSql($driver));
  }
}
