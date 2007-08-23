<?php

/**
 * Sabel_DB_Type_Integer
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Type_Integer implements Sabel_DB_Type_Interface
{
  private $next = null;

  public function getType()
  {
    return Sabel_DB_Type::INT;
  }

  public function add(Sabel_DB_Type_Interface $next)
  {
    $this->next = $next;
  }

  public function send(Sabel_DB_Schema_Column $co, $type)
  {
    $types = array("integer", "int", "int4", "serial", "tinyint");

    if (!in_array($type, $types)) {
      $this->next->send($co, $type);
      return;
    }

    $co->type = $this->getType();
    $co->max  = INT_MAX;
    $co->min  = INT_MIN;
  }
}
