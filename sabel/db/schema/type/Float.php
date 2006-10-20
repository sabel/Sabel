<?php

/**
 * Sabel_DB_Schema_Type_Float
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage schema
 * @subpackage type
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Type_Float implements Sabel_DB_Schema_Type_Sender
{
  private $next = null;

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    if ($type === 'float') {
      $co->type = Sabel_DB_Schema_Const::FLOAT;
      $co->max  =  3.4028235E38;
      $co->min  = -3.4028235E38;
    } else {
      $this->next->send($co, $type);
    }
  }
}
