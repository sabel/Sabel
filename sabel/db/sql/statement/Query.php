<?php

/**
 * Sabel_DB_Sql_Statement_Query
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Statement_Query extends Sabel_DB_Abstract_Statement
{
  public function getStatementType()
  {
    return Sabel_DB_Statement::QUERY;
  }

  public function create($executer, $inputs)
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
