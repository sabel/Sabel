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

  private static $statements = array();

  public static function create($stmtType)
  {
    if (isset(self::$statements[$stmtType])) {
      return new self::$statements[$stmtType];
    }

    switch ($stmtType) {
      case self::SELECT:
        return new Sabel_DB_Sql_Statement_Select();

      case self::INSERT:
        return new Sabel_DB_Sql_Statement_Insert();

      case self::UPDATE:
        return new Sabel_DB_Sql_Statement_Update();

      case self::DELETE:
        return new Sabel_DB_Sql_Statement_Delete();

      default:
        $message = "Sabel_DB_Sql_Statement::create() invalid statement type.";
        throw new Sabel_DB_Exception($message);
    }
  }

  public static function registStatement($stmtType, $className)
  {
    self::$statements[$stmtType] = $className;
  }
}
