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
  private $column = null;

  public function __construct($name)
  {
    $this->column = new Sabel_DB_Schema_Column();
    $this->column->name = $name;
  }

  public function getColumn()
  {
    return $this->column;
  }

  public function type($type)
  {
    $const = constant("Sabel_DB_Type::{$type}");

    if ($const === null) {
      throw new Exception("does not support datatype '{$type}'.");
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

    $this->column->default = $value;
    return $this;
  }

  public function length($length)
  {
    if ($this->column->isString()) {
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
