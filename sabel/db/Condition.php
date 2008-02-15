<?php

/**
 * Sabel_DB_Condition
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition
{
  const EQUAL         = 1;
  const ISNULL        = 2;
  const ISNOTNULL     = 3;
  const IN            = 4;
  const BETWEEN       = 5;
  const LIKE          = 6;
  const GREATER_EQUAL = 7;
  const GREATER_THAN  = 8;
  const LESS_EQUAL    = 9;
  const LESS_THAN     = 10;
  const DIRECT        = 11;
  
  /**
   * @param const   $type   self
   * @param string  $column
   * @param mixed   $value
   * @param boolean $not
   *
   * @return Sabel_DB_Abstract_Condition
   */
  public static function create($type, $column, $value = null, $not = false)
  {
    switch ($type) {
      case self::EQUAL:
        $condition = new Sabel_DB_Condition_Equal($column);
        break;
        
      case self::BETWEEN:
        $condition = new Sabel_DB_Condition_Between($column);
        break;
        
      case self::IN:
        $condition = new Sabel_DB_Condition_In($column);
        break;
        
      case self::LIKE:
        $condition = new Sabel_DB_Condition_Like($column);
        break;
        
      case self::ISNULL:
        $condition = new Sabel_DB_Condition_IsNull($column);
        break;
        
      case self::ISNOTNULL:
        $condition = new Sabel_DB_Condition_IsNotNull($column);
        break;
        
      case self::GREATER_EQUAL:
        $condition = new Sabel_DB_Condition_GreaterEqual($column);
        break;
        
      case self::LESS_EQUAL:
        $condition = new Sabel_DB_Condition_LessEqual($column);
        break;
        
      case self::GREATER_THAN:
        $condition = new Sabel_DB_Condition_GreaterThan($column);
        break;
        
      case self::LESS_THAN:
        $condition = new Sabel_DB_Condition_LessThan($column);
        break;
        
      case self::DIRECT:
        $condition = new Sabel_DB_Condition_Direct($column);
        break;
        
      default:
        throw new Sabel_DB_Exception("invalid condition type.");
    }
    
    $condition->setValue($value)->isNot($not);
    
    return $condition;
  }
}
