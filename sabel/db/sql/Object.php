<?php

/**
 * Sabel_DB_Sql_Object
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Object
{
  /**
   * table: (string) table name.
   * join: (string) join query. ( ex. " INNER JOIN location l ON l.id = member.location_id" )
   * projection: (string) projection.
   * condition: (string) condition of sql. ( ex. " WHERE id = :id" )
   * constraints: (array) constraints. ( ex. array("limit" => 1) )
   * saveValues: (array) values of update or insert. ( ex. array("name" => "new name") )
   * sequenceColumn: (string) name of sequence column.
   */

  public $table          = "";
  public $join           = "";
  public $projection     = "*";
  public $condition      = "";
  public $constraints    = array();
  public $saveValues     = array();
  public $sequenceColumn = "";
}
