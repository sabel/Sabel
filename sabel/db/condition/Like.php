<?php

/**
 * Sabel_DB_Condition_Like
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_Like extends Sabel_DB_Abstract_Condition
{
  const BEGINS_WITH = 1;
  const CONTAINS    = 2;
  const ENDS_WITH   = 3;
  
  /**
   * @var int
   */
  protected $type = Sabel_DB_Condition::LIKE;
  
  /**
   * @var int
   */
  private $likeType = self::BEGINS_WITH;
  
  /**
   * @var boolean
   */
  private $escape = true;
  
  public function type($type)
  {
    if ($type >= 1 && $type <= 3) {
      $this->likeType = $type;
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
  
  public function build(Sabel_DB_Abstract_Statement $stmt)
  {
    $value = $this->value;
    
    if ($this->escape && (strpos($value, "%") !== false || strpos($value, "_") !== false)) {
      $escapeChars = "ZQXJKVBWYGFPMUCDzqxjkvbwygfpmu";
      
      for ($i = 0; $i < 30; $i++) {
        $esc = $escapeChars{$i};
        if (strpos($value, $esc) === false) {
          $value = preg_replace("/([%_])/", $esc . '$1', $value);
          $like  = "LIKE @param%d@ escape '{$esc}'";
          break;
        }
      }
    } else {
      $like = "LIKE @param%d@";
    }
    
    return $this->createQuery($stmt, $value, $like);
  }
  
  private function createQuery($stmt, $value, $part)
  {
    $value = $this->addSpecialCharacter($value);
    $num = ++self::$counter;
    $stmt->setBindValue("param{$num}", $value);
    
    $column = $this->getQuotedColumn($stmt);
    if ($this->isNot) $column = "NOT " . $column;
    
    return $column . " " . sprintf($part, $num);
  }
  
  private function addSpecialCharacter($value)
  {
    switch ($this->likeType) {
      case self::ENDS_WITH:
        return "%" . $value;
      case self::CONTAINS:
        return "%" . $value . "%";
      default:
        return $value . "%";
    }
  }
}
