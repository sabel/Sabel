<?php

/**
 * Sabel_DB_Type_Text
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Type_Text implements Sabel_DB_Type_Interface
{
  private $next = null;

  public function getType()
  {
    return Sabel_DB_Type::TEXT;
  }

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    if (in_array($type, array("text", "mediumtext", "tinytext"))) {
      $co->type = $this->getType();
    } else {
      $this->next->send($co, $type);
    }
  }
}
