<?php

/**
 * Sabel_DB_Schema_Type_Byte
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage schema
 * @subpackage type
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Type_Byte implements Sabel_DB_Schema_Type_Sender
{
  private $next = null;

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $types = array('blob', 'bytea', 'longblob', 'mediumblob');

    if (in_array($type, $types)) {
      $co->type = Sabel_DB_Schema_Const::BYTE;
    } else {
      $this->next->send($co, $type);
    }
  }
}
