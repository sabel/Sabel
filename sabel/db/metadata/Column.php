<?php

/**
 * Sabel_Db_Metadata_Column
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Metadata_Column extends Sabel_Object
{
  public $name      = null;
  public $type      = null;
  public $nullable  = null;
  public $default   = null;
  public $primary   = null;
  public $increment = null;
  public $max       = null;
  public $min       = null;
  public $value     = null;
  
  public function isInt($strict = false)
  {
    if ($strict) {
      return ($this->type === Sabel_Db_Type::INT);
    } else {
      return ($this->type === Sabel_Db_Type::INT    ||
              $this->type === Sabel_Db_Type::BIGINT ||
              $this->type === Sabel_Db_Type::SMALLINT);
    }
  }
  
  public function isBigint()
  {
    return ($this->type === Sabel_Db_Type::BIGINT);
  }
  
  public function isSmallint()
  {
    return ($this->type === Sabel_Db_Type::SMALLINT);
  }
  
  public function isFloat($strict = false)
  {
    if ($strict) {
      return ($this->type === Sabel_Db_Type::FLOAT);
    } else {
      return ($this->type === Sabel_Db_Type::FLOAT ||
              $this->type === Sabel_Db_Type::DOUBLE);
    }
  }
  
  public function isDouble()
  {
    return ($this->type === Sabel_Db_Type::DOUBLE);
  }
  
  public function isString()
  {
    return ($this->type === Sabel_Db_Type::STRING);
  }
  
  public function isText()
  {
    return ($this->type === Sabel_Db_Type::TEXT);
  }
  
  public function isDatetime()
  {
    return ($this->type === Sabel_Db_Type::DATETIME);
  }
  
  public function isDate()
  {
    return ($this->type === Sabel_Db_Type::DATE);
  }
  
  public function isBool()
  {
    return ($this->type === Sabel_Db_Type::BOOL);
  }
  
  public function isBinary()
  {
    return ($this->type === Sabel_Db_Type::BINARY);
  }
  
  public function isNumeric()
  {
    return ($this->isInt() || $this->isFloat() || $this->isDouble());
  }
  
  public function isUnknown($strict = false)
  {
    if ($strict) {
      return ($this->type === Sabel_Db_Type::UNKNOWN);
    } else {
      return ($this->type === Sabel_Db_Type::UNKNOWN || $this->type === null);
    }
  }
  
  public function cast($value)
  {
    if ($value === null) return null;
    
    switch ($this->type) {
      case Sabel_Db_Type::INT:
        return (is_int($value)) ? $value : $this->toInteger($value, PHP_INT_MAX, -PHP_INT_MAX - 1);
      
      case Sabel_Db_Type::SMALLINT:
        return $this->toInteger($value, 32767, -32768);
      
      case Sabel_Db_Type::STRING:
      case Sabel_Db_Type::TEXT:
      case Sabel_Db_Type::BIGINT:
        return (string)$value;
      
      case Sabel_Db_Type::BOOL:
        if (is_string($value)) {
          if ($value === "1" || $value === "t" || $value === "true") {
            return true;
          } elseif ($value === "0" || $value === "f" || $value === "false") {
            return false;
          }
        } elseif (is_int($value)) {
          if ($value === 1) {
            return true;
          } elseif ($value === 0) {
            return false;
          }
        } else {
          return $value;
        }
      
      case Sabel_Db_Type::DATETIME:
        $result = strtotime($value);
        return ($result === false) ? $value : date("Y-m-d H:i:s", $result);
      
      case Sabel_Db_Type::DATE:
        $result = strtotime($value);
        return ($result === false) ? $value : date("Y-m-d", $result);
      
      case Sabel_Db_Type::FLOAT:
      case Sabel_Db_Type::DOUBLE:
        if ((is_string($value) && $value === (string)(float)$value) || is_int($value)) {
          return (float)$value;
        } else {
          return $value;
        }
      
      default:
        return $value;
    }
  }
  
  public function setValue($value)
  {
    $this->value = $this->cast($value);
  }
  
  private function toInteger($value, $max, $min)
  {
    if (is_string($value)) {
      if (preg_match('/^[+|-]?[0-9]+(\.?[0-9]+)?$/', $value) === 0) {
        return $value;
      } else {
        return ($value >= $min && $value <= $max) ? (int)$value : $value;
      }
    } elseif (is_float($value) && fmod($value, 1.0) === 0.0 && $value <= $max) {
      return (int)$value;
    } else {
      return $value;
    }
  }
}
