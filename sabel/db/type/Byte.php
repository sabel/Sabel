<?php

/**
 * Sabel_DB_Type_Byte
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Type_Byte implements Sabel_DB_Type_Interface
{
  private $next = null;

  public function getType()
  {
    return Sabel_DB_Type::BYTE;
  }

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $type  = strtolower($type);
    $types = array("blob", "bytea", "longblob", "mediumblob");

    if (in_array($type, $types)) {
      $co->type = $this->getType();
    } else {
      $this->next->send($co, $type);
    }
  }
}
