<?php

/**
 * Form_Object
 *
 * @category  Addon
 * @package   addon.form
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Form_Object extends Sabel_Object
{
  protected $model   = null;
  protected $mdlName = "";
  protected $columns = null;
  protected $errors  = array();
  
  public function __construct($model)
  {
    if (is_string($model)) {
      $model = MODEL($model);
    }
    
    $this->model   = $model;
    $this->mdlName = $model->getName();
    $this->columns = $model->getSchema()->getColumns();
  }
  
  public function getModel()
  {
    return $this->model;
  }
  
  public function set($key, $val)
  {
    $this->model->__set($key, $val);
  }
  
  public function get($key)
  {
    $result = $this->model->__get($key);
    return (is_string($result)) ? htmlspecialchars($result) : $result;
  }
  
  public function __set($key, $val)
  {
    $this->set($key, $val);
  }
  
  public function __get($key)
  {
    return $this->get($key);
  }
  
  public function setErrors($errors)
  {
    $this->errors = $errors;
  }
  
  public function getErrors()
  {
    return $this->errors;
  }
  
  public function hasError()
  {
    return !empty($this->errors);
  }
  
  public function unsetErrors()
  {
    $errors = $this->errors;
    $this->errors = array();
    
    return $errors;
  }
  
  public function validate($ignores = array())
  {
    $model = $this->model;
    $validator = new Sabel_DB_Validator($model);
    $annot = $model->getReflection()->getAnnotation("validate_ignores");
    
    if ($annot !== null) {
      $ignores = array_merge($annot[0], $ignores);
    }
    
    if ($errors = $validator->validate($ignores)) {
      $this->errors = $errors;
      return false;
    } else {
      return true;
    }
  }
  
  public function name($colName)
  {
    static $names = array();
    $mdlName = $this->mdlName;
    
    if (empty($names[$mdlName])) {
      $names[$mdlName] = Sabel_DB_Model_Localize::getColumnNames($mdlName);
    }
    
    return (isset($names[$mdlName][$colName])) ? $names[$mdlName][$colName] : $colName;
  }
  
  public function mark($colName, $mark = "*", $tag = "em")
  {
    $name = $this->name($colName);
    
    if (isset($this->columns[$colName]) && !$this->columns[$colName]->nullable) {
      $name .= " <{$tag}>{$mark}</{$tag}>";
    }
    
    return $name;
  }
  
  public function start($uri, $class = null, $id = null, $method = "post", $name = "")
  {
    $options = array("type" => "open", "uri" => $uri, "method" => $method);
    return $this->createElement(Form_Element::FORM, "", $name, $id, $class, array(), $options);
  }
  
  public function end()
  {
    $options = array("type" => "close");
    return $this->createElement(Form_Element::FORM, "", "", null, null, array(), $options);
  }
  
  public function submit($value = null, $class = null, $id = null)
  {
    $options = array("type" => "submit", "text" => $value);
    return $this->createElement(Form_Element::FORM, "", "", $id, $class, array(), $options);
  }
  
  public function text($name, $class = null, $id = null)
  {
    $eName = $this->createName($name);
    return $this->createElement(Form_Element::TEXT, $name, $eName, $id, $class);
  }
  
  public function password($name, $class = null, $id = null)
  {
    $eName = $this->createName($name);
    return $this->createElement(Form_Element::PASSWORD, $name, $eName, $id, $class);
  }
  
  public function textarea($name, $class = null, $id = null)
  {
    $eName = $this->createName($name);
    return $this->createElement(Form_Element::TEXTAREA, $name, $eName, $id, $class);
  }
  
  public function hidden($name, $class = null, $id = null)
  {
    $eName = $this->createName($name);
    return $this->createElement(Form_Element::HIDDEN, $name, $eName, $id, $class);
  }
  
  public function checkbox($name, $values, $class = null, $id = null)
  {
    $eName = $this->createName($name);
    return $this->createElement(Form_Element::CHECK, $name, $eName, $id, $class, $values);
  }
  
  public function select($name, $values, $class = null, $id = null, $isHash = true)
  {
    $isNullable = (isset($this->columns[$name])) ? $this->columns[$name]->nullable : true;
    
    $eName   = $this->createName($name);
    $options = array("isNullable" => $isNullable, "useKey" => $isHash);
    return $this->createElement(Form_Element::SELECT, $name, $eName, $id, $class, $values, $options);
  }
  
  public function datetime($name, $yearRange = null, $withSecond = false, $defaultNull = false)
  {
    $eName   = $this->createName("datetime") . "[{$name}]";
    $options = array("yearRange"   => $yearRange,
                     "withSecond"  => $withSecond,
                     "defaultNull" => $defaultNull);
                     
    return $this->createElement(Form_Element::DATETIME, $name, $eName, null, null, array(), $options);
  }
  
  public function date($name, $yearRange = null, $defaultNull = false)
  {
    $eName   = $this->createName("date") . "[{$name}]";
    $options = array("yearRange" => $yearRange, "defaultNull" => $defaultNull);
    return $this->createElement(Form_Element::DATE, $name, $eName, null, null, array(), $options);
  }
  
  public function radio($name, $values, $class = null, $id = null)
  {
    $value = $this->get($name);
    
    if (isset($this->columns[$name])) {
      if ($this->columns[$name]->isBool()) {
        $value = ($this->get($name)) ? 1 : 0;
      }
      
      $isNullable = $this->columns[$name]->nullable;
    } else {
      $isNullable = true;
    }
    
    $eName   = $this->createName($name);
    $options = array("isNullable" => $isNullable);
    return $this->createElement(Form_Element::RADIO, $name, $eName, $id, $class, $values, $options);
  }
  
  protected function createName($name)
  {
    return $this->mdlName . "::" . $name;
  }
  
  private function createElement($elementType, $name, $elementName, $id, $class,
                                 $data = array(), $options = array())
  {
    $element = Form_Element_Factory::create($elementType, $elementName);
    $element->setValue($this->get($name))->setId($id)->setClass($class)->setData($data);
    return $element->toHtml($options);
  }
}
