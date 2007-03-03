<?php

//Sabel::using('Sabel_DB_Type_Interface');

/**
 * Sabel_DB_Type_Text
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage type
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Type_Text implements Sabel_DB_Type_Interface
{
  private $next = null;

  public function getType()
  {
    return Sabel_DB_Type_Const::TEXT;
  }

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $types = array('text', 'mediumtext', 'tinytext');

    if (in_array($type, $types)) {
      $co->type = $this->getType();
    } else {
      $this->next->send($co, $type);
    }
  }
}
