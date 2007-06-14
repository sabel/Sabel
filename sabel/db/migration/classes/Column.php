<?php

/**
 * Sabel_DB_Migration_Classes_Column
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Classes_Column
{
  const EMPTY_DEFAULT = "SDB_EMPTY_DEFAULT";

  private $column   = null;
  private $isChange = false;

  public function __construct($name, $isChange = false)
  {
    $this->column = new Sabel_DB_Schema_Column();
    $this->column->name = $name;
    $this->isChange = $isChange;
  }

  public function getColumn()
  {
    return $this->column;
  }

  public function type($type)
  {
    $const = @constant($type);

    if ($const === null) {
      echo "[\x1b[1;31mERROR\x1b[m]: datatype '{$type}' is not supported.\n";
      exit;
    } else {
      $this->column->type = $const;
    }

    return $this;
  }

  public function primary($bool)
  {
    $this->setBoolean($bool, "primary");
    return $this;
  }

  public function increment($bool)
  {
    $this->setBoolean($bool, "increment");
    return $this;
  }

  public function nullable($bool)
  {
    $this->setBoolean($bool, "nullable");
    return $this;
  }

  public function defaultValue($value)
  {
    if ($this->column->isBool() && !is_bool($value)) {
      throw new Exception("default value for BOOL column must be a boolean.");
    }

    if ($this->isChange && $value === null) {
      $this->column->default = self::EMPTY_DEFAULT;
    } else {
      $this->column->default = $value;
    }

    return $this;
  }

  public function length($length)
  {
    if ($this->column->isString()) {
      $this->column->max = $length;
      return $this;
    } elseif ($this->isChange && $this->column->type === null) {
      $this->column->max = $length;
      return $this;
    } else {
      throw new Exception("length() for STRING column.");
    }
  }

  private function setBoolean($bool, $key)
  {
    if (is_bool($bool)) {
      $this->column->$key = $bool;
    } else {
      throw new Exception("argument for {$key}() must be a boolean.");
    }
  }
}
