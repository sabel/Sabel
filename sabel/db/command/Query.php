<?php

/**
 * Sabel_DB_Command_Query
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Command_Query extends Sabel_DB_Command_Base
{
  protected $command = Sabel_DB_Command::QUERY;

  protected function run($executer)
  {
    $args = $executer->getArguments();
    $driver = $executer->getDriver();

    if ($args[1]) {
      $query = vsprintf($args[0], $driver->escape($args[1]));
    } else {
      $query = $args[0];
    }

    $result = $driver->setSql($query)->execute();
    $executer->setResult($result);

    return $result;
  }
}
