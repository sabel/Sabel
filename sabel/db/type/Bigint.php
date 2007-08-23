<?php

/**
 * Sabel_DB_Type_Bigint
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Type_Bigint implements Sabel_DB_Type_Interface
{
  private $next = null;

  public function getType()
  {
    return Sabel_DB_Type::BIGINT;
  }

  public function add(Sabel_DB_Type_Interface $next)
  {
    $this->next = $next;
  }

  public function send(Sabel_DB_Schema_Column $co, $type)
  {
    $types = array("bigint", "int8", "bigserial");

    if (in_array($type, $types)) {
      $co->type = $this->getType();
      $co->max  =  9223372036854775807;
      $co->min  = -9223372036854775808;
    } else {
      $this->next->send($co, $type);
    }
  }
}
