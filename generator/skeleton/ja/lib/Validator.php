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
  protected $displayNames = array();
  protected $suites = array();
  
  public function __construct()
  {
    // $this->displayNames = array("INPUT_NAME" => "DISPLAY_NAME");
  }
  
  public function required($name, $value)
  {
    if ($value === null) {
      return $this->getDisplayName($name) . "を入力してください";
    }
  }
  
  public function integer($name, $value)
  {
    if ($value !== null && preg_match('/^[1-9][0-9]*$/', $value) === 0) {
      return $this->getDisplayName($name) . "は整数で入力してください";
    }
  }
  
  public function numeric($name, $value)
  {
    if ($value !== null && !is_numeric($value)) {
      return $this->getDisplayName($name) . "は数値で入力してください";
    }
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
