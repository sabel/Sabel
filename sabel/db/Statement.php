<?php

/**
 * Sabel_DB_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Statement
{
  const SELECT  = 0x01;
  const INSERT  = 0x02;
  const UPDATE  = 0x04;
  const DELETE  = 0x08;

  public static function create($executer, $stmtType)
  {
    switch ($stmtType) {
      case self::SELECT:
        return self::createSelectStatement($executer);

      case self::INSERT:
        return self::createInsertStatement($executer);

      case self::UPDATE:
        return self::createUpdateStatement($executer);

      case self::DELETE:
        return self::createDeleteStatement($executer);

      default:
        $message = "Sabel_DB_Sql_Statement::create() invalid statement type.";
        throw new Sabel_DB_Exception($message);
    }
  }

  public static function createSelectStatement($executer, $sql = "")
  {
    // @todo two arguments are bad for interface.
    return Sabel_DB_Sql_Statement_Loader::load("select")->create($executer, $sql);
  }

  public static function createInsertStatement($executer)
  {
    return Sabel_DB_Sql_Statement_Loader::load("insert")->create($executer);
  }

  public static function createUpdateStatement($executer)
  {
    return Sabel_DB_Sql_Statement_Loader::load("update")->create($executer);
  }

  public static function createDeleteStatement($executer)
  {
    return Sabel_DB_Sql_Statement_Loader::load("delete")->create($executer);
  }
}
