<?php

/**
 * Validator
 *
 * @category   Request
 * @package    lib
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Validator extends Sabel_Request_Validator
{
  public function __construct()
  {
    // $this->displayNames = array("INPUT_NAME" => "DISPLAY_NAME");
  }
  
  public function required($name, $value)
  {
    if (realempty($value)) {
      return $this->getDisplayName($name) . "を入力してください";
    }
  }
  
  public function integer($name, $value)
  {
    if (!realempty($value) && !ctype_digit($value)) {
      return $this->getDisplayName($name) . "は整数で入力してください";
    }
  }
  
  public function numeric($name, $value)
  {
    if (!realempty($value) && !is_numeric($value)) {
      return $this->getDisplayName($name) . "は数値で入力してください";
    }
  }
  
  public function naturalNumber($name, $value)
  {
    if (!realempty($value) && !is_natural_number($value)) {
      return $this->getDisplayName($name) . "は整数で入力してください";
    }
  }
  
  public function maxLength($name, $value, $max)
  {
    if (!realempty($value) && strlen($value) > $max) {
      return $this->getDisplayName($name) . "は{$max}文字以内で入力してください";
    }
  }
  
  public function maxWidth($name, $value, $max)
  {
    if (!realempty($value) && (mb_strwidth($value) / 2) > $max) {
      return $this->getDisplayName($name) . "は全角{$max}文字以内で入力してください";
    }
  }
  
  public function alnum($name, $value)
  {
    if (!realempty($value) && preg_match('/^[0-9a-zA-Z]+$/', $value) === 0) {
      return $this->getDisplayName($name) . "は半角英数字で入力してください";
    }
  }
  
  public function nnumber($name, $value)
  {
    return $this->naturalNumber($name, $value);
  }
  
  protected function getDisplayName($name)
  {
    if (isset($this->displayNames[$name])) {
      return $this->displayNames[$name];
    } else {
      return $name;
    }
  }
}
