<?php

Sabel::using('Sabel_DB_Type_Interface');

/**
 * Sabel_DB_Type_Double
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage type
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Type_Double implements Sabel_DB_Type_Interface
{
  private $next = null;

  public function getType()
  {
    return Sabel_DB_Type_Const::DOUBLE;
  }

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    if ($type === 'double') {
      $co->type = $this->getType();
      $co->max  =  1.79769E308;
      $co->min  = -1.79769E308;
    } else {
      $this->next->send($co, $type);
    }
  }
}
