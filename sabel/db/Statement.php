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
  const SELECT = 0x01;
  const INSERT = 0x02;
  const UPDATE = 0x04;
  const DELETE = 0x08;
  const QUERY  = 0x10;

  public static function create(Sabel_DB_Model $model, $stmtType = self::QUERY)
  {
    switch ($stmtType) {
      case self::SELECT:
        return new Sabel_DB_Statement_Select($model);

      case self::INSERT:
        return new Sabel_DB_Statement_Insert($model);

      case self::UPDATE:
        return new Sabel_DB_Statement_Update($model);

      case self::DELETE:
        return new Sabel_DB_Statement_Delete($model);

      case self::QUERY:
        return new Sabel_DB_Statement_Query($model);

      default:
        $message = "Sabel_DB_Statement::create() invalid statement type.";
        throw new Sabel_DB_Exception($message);
    }
  }
}
