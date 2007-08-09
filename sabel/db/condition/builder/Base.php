<?php

/**
 * Sabel_DB_Condition_Builder_Base
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Condition_Builder_Base
{
  protected $driver = null;

  public function build($condition)
  {
    switch ($condition->type) {
      case Sabel_DB_Condition_Object::NORMAL:
        return $this->buildNormal($condition);

      case Sabel_DB_Condition_Object::BETWEEN:
        return $this->buildBetween($condition);

      case Sabel_DB_Condition_Object::LIKE:
        return $this->buildLike($condition);

      case Sabel_DB_Condition_Object::IN:
        return $this->buildIn($condition);

      case Sabel_DB_Condition_Object::ISNULL:
        return $this->buildIsNull($condition->key);

      case Sabel_DB_Condition_Object::ISNOTNULL:
        return $this->buildIsNotNull($condition->key);

      case Sabel_DB_Condition_Object::COMPARE:
        return $this->buildCompare($condition);
    }
  }

  protected function getKey($condition)
  {
    return ($condition->not) ? "NOT " . $condition->key : $condition->key;
  }

  public function buildIsNull($key)
  {
    return $key . " IS NULL";
  }

  public function buildIsNotNull($key)
  {
    return $key . " IS NOT NULL";
  }

  public function buildIn($condition)
  {
    $values = $this->driver->escape($condition->value);
    return $this->getKey($condition) . " IN (" . implode(", ", $values) . ")";
  }

  public function buildLike($condition)
  {
    return $this->getLike($condition);
  }

  protected function getLike($condition)
  {
    $value = $condition->value;

    if (is_array($value)) {
      $escape = $value[1];
      $val    = $value[0];
    } else {
      $escape = true;
      $val    = $value;
    }

    if (!$escape) {
      return $this->createLike($val, $condition);
    } else {
      list($val, $esc) = $this->escapeForLike($val);
      return $this->createLike($val, $condition, $esc);
    }
  }

  protected function escapeForLike($val)
  {
    $escapeChars = "ZQXJKVBWYGFPMUCDzqxjkvbwygfpmu";

    for ($i = 0; $i < 30; $i++) {
      $esc = $escapeChars{$i};
      if (strpbrk($val, $esc) === false) {
        $val = preg_replace("/([%_])/", $esc . '$1', $val);
        return array($val, $esc);
      }
    }
  }
}
