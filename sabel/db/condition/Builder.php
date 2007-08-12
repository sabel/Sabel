<?php

/**
 * Sabel_DB_Condition_Builder
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_Builder implements Sabel_DB_Condition_Builder_Interface
{
  protected $count = 1;
  protected $stmt  = null;

  public function __construct(Sabel_DB_Abstract_Statement $stmt)
  {
    $this->count = 1;
    $this->stmt  = $stmt;
  }

  public function build(Sabel_DB_Condition_Object $condition)
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

  public function buildNormal(Sabel_DB_Condition_Object $condition)
  {
    $bindKey = "param" . $this->count++;
    $this->stmt->setBind(array($bindKey => $condition->value));
    return $this->getKey($condition) . " = :{$bindKey}";
  }

  public function buildIsNull($key)
  {
    return $key . " IS NULL";
  }

  public function buildIsNotNull($key)
  {
    return $key . " IS NOT NULL";
  }

  public function buildIn(Sabel_DB_Condition_Object $condition)
  {
    // @todo escape or bind.
    return $this->getKey($condition) . " IN (" . implode(", ", $condition->value) . ")";
  }

  public function buildLike(Sabel_DB_Condition_Object $condition)
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

  public function buildBetween(Sabel_DB_Condition_Object $condition)
  {
    $f   = $this->count++;
    $t   = $this->count++;
    $val = $condition->value;

    $this->stmt->setBind(array("from{$f}" => $val[0],
                               "to{$t}"   => $val[1]));

    return $this->getKey($condition) . " BETWEEN :from{$f} AND :to{$t}";
  }

  public function buildCompare(Sabel_DB_Condition_Object $condition)
  {
    $bindKey = "param" . $this->count++;
    list ($lg, $val) = $condition->value;

    $this->stmt->setBind(array($bindKey => $val));
    return $condition->key . " $lg :{$bindKey}";
  }

  protected function createLike($val, $condition, $esc = null)
  {
    $bindKey = "param" . $this->count++;
    $this->stmt->setBind(array($bindKey => $val));

    $query = $this->getKey($condition) . " LIKE :{$bindKey}";
    if (isset($esc)) $query .= " escape '{$esc}'";

    return $query;
  }

  protected function getKey($condition)
  {
    return ($condition->not) ? "NOT " . $condition->key : $condition->key;
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
