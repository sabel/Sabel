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

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $types = array("integer", "int", "int4", "serial",
                   "smallint", "tinyint");

    if (!in_array($type, $types)) {
      $this->next->send($co, $type);
      return;
    }

    $co->type = $this->getType();

    switch($type) {
      case "integer":
      case "int":
      case "int4":
      case "serial":
        $co->max =  2147483647;
        $co->min = -2147483648;
        break;

      case "smallint":
        $co->max =  32767;
        $co->min = -32768;
        break;

      // @todo...
      case "tinyint":
        $co->max =  127;
        $co->min = -128;
        break;
    }
  }
}
