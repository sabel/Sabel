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
  const BEGINS_WITH = 1;
  const CONTAINS    = 2;
  const ENDS_WITH   = 3;
  const FIXED       = 4;
  
  private $type   = self::BEGINS_WITH;
  private $escape = true;
  
  public function build(Sabel_DB_Abstract_Statement $stmt, &$counter)
  {
    $value = $this->value;
    
    if ($this->escape && (strpos($value, "%") !== false || strpos($value, "_") !== false)) {
      $escapeChars = "ZQXJKVBWYGFPMUCDzqxjkvbwygfpmu";
      
      for ($i = 0; $i < 30; $i++) {
        $esc = $escapeChars{$i};
        if (strpos($value, $esc) === false) {
          $value = preg_replace("/([%_])/", $esc . '$1', $value);
          $value = $this->addSpecialCharacter($value);
          $num   = ++$counter;
          $stmt->setBindValue("param{$num}", $value);
          $query = $this->conditionColumn($stmt) . " LIKE @param{$num}@ escape '{$esc}'";
          break;
        }
      }
    } else {
      $value = $this->addSpecialCharacter($value);
      $num = ++$counter;
      $stmt->setBindValue("param{$num}", $value);
      $query = $this->conditionColumn($stmt) . " LIKE @param{$num}@";
    }
    
    return $query;
  }
  
  public function type($type)
  {
    if ($type >= 1 && $type <= 4) {
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
    if ($this->type === self::BEGINS_WITH) {
      return $value . "%";
    } elseif ($this->type === self::ENDS_WITH) {
      return "%" . $value;
    } elseif ($this->type === self::CONTAINS) {
      return "%" . $value . "%";
    } else {
      return $value;
    }
  }
}
