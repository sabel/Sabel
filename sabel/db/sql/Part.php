<?php

/**
 * Sabel_DB_Sql_Part
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Part implements Sabel_DB_Sql_Part_Interface
{
  protected $value  = null;
  protected $format = null;
  
  public function __construct($value, $format = null)
  {
    $this->value  = $value;
    $this->format = $format;
  }
  
  public function __toString()
  {
    if ($this->format === null) {
      return $this->value;
    } else {
      return sprintf($this->format, $this->value);
    }
  }
  
  public function getValue(Sabel_DB_Abstract_Sql $sql)
  {
    return $this->__toString();
  }
}
