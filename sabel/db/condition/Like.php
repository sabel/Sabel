<?php

/**
 * Sabel_DB_Condition_Like
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_Like extends Sabel_DB_Abstract_Condition
{
  const PREFIX  = 1;
  const CONTAIN = 2;
  const SUFFIX  = 3;
  
  private $type   = self::PREFIX;
  private $escape = true;
  
  public function build(Sabel_DB_Abstract_Statement $sql, &$counter)
  {
    $value = $this->value;
    
    if ($this->escape && (strpos($value, "%") !== false || strpos($value, "_") !== false)) {
      $escapeChars = "ZQXJKVBWYGFPMUCDzqxjkvbwygfpmu";
      
      for ($i = 0; $i < 30; $i++) {
        $esc = $escapeChars{$i};
        if (strpos($value, $esc) === false) {
          $value = preg_replace("/([%_])/", $esc . '$1', $value);
          $value = $this->addSpecialCharacter($value);
          $bindKey = $sql->setBindValue("param" . ++$counter, $value);
          $query = $this->getColumnWithNot() . " LIKE " . $bindKey . " escape '{$esc}'";
          break;
        }
      }
    } else {
      $value   = $this->addSpecialCharacter($value);
      $bindKey = $sql->setBindValue("param" . ++$counter, $value);
      $query   = $this->getColumnWithNot() . " LIKE " . $bindKey;
    }
    
    return $query;
  }
  
  public function type($type)
  {
    if ($type >= 1 && $type <= 3) {
      $this->type = $type;
    } else {
      throw new Sabel_Exception_InvalidArgument("invalid type.");
    }
    
    return $this;
  }
  
  public function escape($bool)
  {
    if (is_bool($bool)) {
      $this->escape = $bool;
    } else {
      throw new Sabel_Exception_InvalidArgument("argument must be a boolean.");
    }
    
    return $this;
  }
  
  private function addSpecialCharacter($value)
  {
    if ($this->type === self::PREFIX) {
      return $value . "%";
    } elseif ($this->type === self::SUFFIX) {
      return "%" . $value;
    } else {
      return "%" . $value . "%";
    }
  }
}
