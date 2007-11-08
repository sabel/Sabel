<?php

/**
 * Form_Element
 *
 * @abstract
 * @category  Addon
 * @package   addon.form
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class Form_Element extends Sabel_Object
{
  const FORM     = 1;
  const TEXT     = 2;
  const PASSWORD = 3;
  const TEXTAREA = 4;
  const SELECT   = 5;
  const RADIO    = 6;
  const CHECK    = 7;
  const DATETIME = 8;
  const DATE     = 9;
  const HIDDEN   = 10;
  
  protected $name  = "";
  protected $value = null;
  protected $id    = "";
  protected $class = "";
  protected $data  = array();
  
  public abstract function toHtml($options = array());
  
  public function __construct($name)
  {
    if (is_string($name)) {
      $this->name = $name;
    } else {
      throw new Exception("name must be a string.");
    }
  }
  
  public function setValue($value)
  {
    $this->value = $value;
    
    return $this;
  }
  
  public function setId($id)
  {
    if ($id !== null) {
      if (is_string($id)) {
        $this->id = $id;
      } else {
        throw new Exception("id must be a string.");
      }
    }
    
    return $this;
  }
  
  public function setClass($class)
  {
    if ($class !== null) {
      if (is_string($class)) {
        $this->class = $class;
      } else {
        throw new Exception("class must be a string.");
      }
    }
    
    return $this;
  }
  
  public function setData($data)
  {
    if (is_array($data)) {
      $this->data = $data;
    } else {
      throw new Exception("data must be an array.");
    }
    
    return $this;
  }
  
  protected function addIdAndClass(&$html)
  {
    if ($this->id !== "")    $html .= 'id="' . $this->id . '" ';
    if ($this->class !== "") $html .= 'class="' . $this->class . '" ';
  }
}
