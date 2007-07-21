<?php

/**
 * Sabel_DB_Schema_Column
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Column
{
  public $name      = null;
  public $type      = null;
  public $nullable  = null;
  public $default   = null;
  public $primary   = null;
  public $increment = null;
  public $max       = null;
  public $min       = null;

  public function isInt($strict = false)
  {
    if ($strict) {
      return ($this->type === Sabel_DB_Type::INT);
    } else {
      return ($this->type === Sabel_DB_Type::INT    ||
              $this->type === Sabel_DB_Type::BIGINT ||
              $this->type === Sabel_DB_Type::SMALLINT);
    }
  }

  public function isBigint()
  {
    return ($this->type === Sabel_DB_Type::BIGINT);
  }

  public function isSmallint()
  {
    return ($this->type === Sabel_DB_Type::SMALLINT);
  }

  public function isFloat($strict = false)
  {
    if ($strict) {
      return ($this->type === Sabel_DB_Type::FLOAT);
    } else {
      return ($this->type === Sabel_DB_Type::FLOAT ||
              $this->type === Sabel_DB_Type::DOUBLE);
    }
  }

  public function isDouble()
  {
    return ($this->type === Sabel_DB_Type::DOUBLE);
  }

  public function isString()
  {
    return ($this->type === Sabel_DB_Type::STRING);
  }

  public function isText()
  {
    return ($this->type === Sabel_DB_Type::TEXT);
  }

  public function isDatetime()
  {
    return ($this->type === Sabel_DB_Type::DATETIME);
  }

  public function isBool()
  {
    return ($this->type === Sabel_DB_Type::BOOL);
  }

  public function isByte()
  {
    return ($this->type === Sabel_DB_Type::BYTE);
  }

  public function isNumeric()
  {
    return ($this->isInt() || $this->isFloat() || $this->isDouble());
  }

  public function isUnknown($strict = false)
  {
    if ($strict) {
      return ($this->type === Sabel_DB_Type::UNKNOWN);
    } else {
      return ($this->type === Sabel_DB_Type::UNKNOWN || $this->type === null);
    }
  }

  public function cast($value)
  {
    switch ($this->type) {
      case Sabel_DB_Type::INT:
        return _int_cast_func($value, INT_MAX);

      case Sabel_DB_Type::SMALLINT:
        return _int_cast_func($value, SMALLINT_MAX);

      case Sabel_DB_Type::STRING:
      case Sabel_DB_Type::TEXT:
      case Sabel_DB_Type::BIGINT:
        return (string)$value;

      case Sabel_DB_Type::FLOAT:
      case Sabel_DB_Type::DOUBLE:
        if (is_string($value) && $value === (string)(float)$value || is_int($value)) {
          return (float)$value;
        } else {
          return $value;
        }

      case Sabel_DB_Type::BOOL:
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
        }

        return $value;

      default:
        return $value;
    }
  }
}

function _int_cast_func($value, $max)
{
  if (is_string($value) && is_numeric($value) && $value <= $max) {
    if ($value === "0" || ($pos = strpos($value, ".")) === false && $value{0} !== "0") {
      return (int)$value;
    } elseif (substr_count($value, ".") === 1 && preg_match("/0+$/", substr($value, ++$pos))) {
      return (int)$value;
    } else {
      return $value;
    }
  } elseif (is_float($value) && fmod($value, 1.0) === 0.0 && $value <= $max) {
    return (int)$value;
  } else {
    return $value;
  }
}
