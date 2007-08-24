<?php

/**
 * Sabel_DB_Type_String
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Type_String implements Sabel_DB_Type_Interface
{
  private $next = null;

  public function getType()
  {
    return Sabel_DB_Type::STRING;
  }

  public function add(Sabel_DB_Type_Interface $next)
  {
    $this->next = $next;
  }

  public function send(Sabel_DB_Schema_Column $co, $type)
  {
    $types = array("varchar", "char", "character varying",
                   "character", "varchar2", "cstring");

    if (in_array($type, $types)) {
      $co->type = $this->getType();
    } else {
      $this->next->send($co, $type);
    }
  }
}
