<?php

/**
 * Sabel_DB_Schema_Type_String
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage schema
 * @subpackage type
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Type_String implements Sabel_DB_Schema_Type_Sender
{
  private $next = null;

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $types = array('varchar', 'char', 'character varying' , 'character', 'cstring');

    if (in_array($type, $types)) {
      $co->type = Sabel_DB_Schema_Const::STRING;
    } else {
      $this->next->send($co, $type);
    }
  }
}
