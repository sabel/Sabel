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
    $form = Form_Element_Factory::create(Form_Element::FORM, $name);
    $form->setId($id)->setClass($class);
    return $form->toHtml(array("type" => "open", "uri" => $uri, "method" => $method));
  }
  
  public function end()
  {
    $form = Form_Element_Factory::create(Form_Element::FORM, "");
    return $form->toHtml(array("type" => "close"));
  }
  
  public function submit($value = null, $class = null, $id = null)
  {
    $form = Form_Element_Factory::create(Form_Element::FORM, "");
    $form->setId($id)->setClass($class);
    return $form->toHtml(array("type" => "submit", "text" => $value));
  }
  
  public function text($name, $class = null, $id = null)
  {
    return $this->createSimpleElement(Form_Element::TEXT, $name, $id, $class);
  }
  
  public function password($name, $class = null, $id = null)
  {
    return $this->createSimpleElement(Form_Element::PASSWORD, $name, $id, $class);
  }
  
  public function textarea($name, $class = null, $id = null)
  {
    return $this->createSimpleElement(Form_Element::TEXTAREA, $name, $id, $class);
  }
  
  public function hidden($name, $class = null, $id = null)
  {
    return $this->createSimpleElement(Form_Element::HIDDEN, $name, $id, $class);
  }
  
  public function checkbox($name, $values, $class = null, $id = null)
  {
    $checkbox = Form_Element_Factory::create(Form_Element::CHECK, $this->createName($name));
    $checkbox->setValue($this->getValue($name))->setData($values)->setId($id)->setClass($class);
    return $checkbox->toHtml();
  }
  
  public function select($name, $values, $class = null, $id = null, $useKey = true)
  {
    if (isset($this->columns[$name])) {
      $isNullable = $this->columns[$name]->nullable;
    } else {
      $isNullable = true;
    }
    
    $select = Form_Element_Factory::create(Form_Element::SELECT, $this->createName($name));
    $select->setValue($this->getValue($name))->setData($values)->setId($id)->setClass($class);
    return $select->toHtml(array("isNullable" => $isNullable, "useKey" => $useKey));
  }
  
  public function datetime($name, $yearRange = null, $withSecond = false, $defaultNull = false)
  {
    $name  = $this->createName("datetime") . "[{$name}]";
    $dtime = Form_Element_Factory::create(Form_Element::DATETIME, $name);
    $dtime->setValue($this->getValue($name));
    return $dtime->toHtml(array("yearRange"   => $yearRange,
                                "withSecond"  => $withSecond,
                                "defaultNull" => $defaultNull));
  }
  
  public function date($name, $yearRange = null, $defaultNull = false)
  {
    $name  = $this->createName("date") . "[{$name}]";
    $dtime = Form_Element_Factory::create(Form_Element::DATE, $name);
    $dtime->setValue($this->getValue($name));
    return $dtime->toHtml(array("yearRange" => $yearRange, "defaultNull" => $defaultNull));
  }
  
  public function radio($name, $values, $class = null, $id = null)
  {
    if (isset($this->columns[$name])) {
      if ($this->columns[$name]->isBool()) {
        $value = ($this->getValue($name)) ? 1 : 0;
      }
      
      $isNullable = $this->columns[$name]->nullable;
    } else {
      $isNullable = true;
      $value = $this->getValue($name);
    }
    
    $radio = Form_Element_Factory::create(Form_Element::RADIO, $this->createName($name));
    $radio->setValue($value)->setData($values)->setId($id)->setClass($class);
    return $radio->toHtml(array("isNullable" => $isNullable));
  }
  
  protected function createName($name)
  {
    return $this->mdlName . "::" . $name;
  }
  
  protected function getValue($name)
  {
    $value = $this->model->$name;
    return (is_string($value)) ? htmlspecialchars($value) : $value;
  }
  
  private function createSimpleElement($elementType, $name, $id, $class)
  {
    $element = Form_Element_Factory::create($elementType, $this->createName($name));
    $element->setValue($this->getValue($name))->setId($id)->setClass($class);
    return $element->toHtml();
  }
}
