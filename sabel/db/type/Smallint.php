<?php

/**
 * Sabel_DB_Type_Smallint
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Type_Smallint implements Sabel_DB_Type_Interface
{
  private $next = null;

  public function getType()
  {
    return Sabel_DB_Type::SMALLINT;
  }

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    if ($type === "smallint") {
      $co->type = $this->getType();
      $co->max  =  32767;
      $co->min  = -32768;
    } else {
      $this->next->send($co, $type);
    }
  }
}
