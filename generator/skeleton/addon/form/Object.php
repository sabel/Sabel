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
  protected
    $token    = null,
    $model    = null,
    $mdlName  = "",
    $formName = "",
    $columns  = null,
    $errors   = array();
    
  public function __construct($model, $fName, $token = null)
  {
    if (is_string($model)) {
      $model = MODEL($model);
    }
    
    $this->token    = $token;
    $this->model    = $model;
    $this->formName = $fName;
    $this->mdlName  = $model->getName();
    $this->columns  = $model->getSchema()->getColumns();
  }
  
  public function getToken()
  {
    return $this->token;
  }
  
  public function getFormName()
  {
    return $this->formName;
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
    return $this->model->__get($key);
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
    if (is_string($ignores)) {
      $ignores = array($ignores);
    }
    
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
    $html = $this->createHtmlWriter("", $name, $id, $class)->open($uri, $method);
    
    if ($this->token === null) {
      return $html;
    } else {
      return $html . '<input type="hidden" name="token" value="' . $this->token . '"/>';
    }
  }
  
  public function end()
  {
    return $this->createHtmlWriter("", "")->close();
  }
  
  public function submit($text = null, $class = null, $id = null)
  {
    return $this->createHtmlWriter("", "", $id, $class)->submit($text);
  }
  
  public function button($uri, $text, $class = null, $id = null)
  {
    // @todo
    
    $fmt = '<input %s%stype="button" value="%s" '
         . 'onclick="window.location.href=\'http://%s%s%s\'" />';
       
    $id    = ($id === null)    ? "" : 'id="' . $id . '" ';
    $class = ($class === null) ? "" : 'class="' . $class . '" ';
    $token = ($this->token === null) ? "" : "?token={$this->token}";
    
    $domain = Sabel_Environment::get("http_host");
    return sprintf($fmt, $id, $class, $text, $domain, uri($uri), $token);
  }
  
  public function text($name, $class = null, $id = null)
  {
    $eName = $this->createName($name);
    return $this->createHtmlWriter($name, $eName, $id, $class)->text();
  }
  
  public function password($name, $class = null, $id = null)
  {
    $eName = $this->createName($name);
    return $this->createHtmlWriter($name, $eName, $id, $class)->password();
  }
  
  public function textarea($name, $class = null, $id = null)
  {
    $eName = $this->createName($name);
    return $this->createHtmlWriter($name, $eName, $id, $class)->textarea();
  }
  
  public function hidden($name, $class = null, $id = null)
  {
    $eName = $this->createName($name);
    return $this->createHtmlWriter($name, $eName, $id, $class)->hidden();
  }
  
  public function checkbox($name, $values, $class = null, $id = null)
  {
    $eName = $this->createName($name);
    return $this->createHtmlWriter($name, $eName, $id, $class)->checkbox($values);
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
    
    $eName  = $this->createName($name);
    $writer = $this->createHtmlWriter($name, $eName, $id, $class);
    return $writer->radio($values, $isNullable);
  }
  
  public function select($name, $values, $class = null, $id = null, $isHash = true)
  {
    $isNullable = (isset($this->columns[$name])) ? $this->columns[$name]->nullable : true;
    
    $eName  = $this->createName($name);
    $writer = $this->createHtmlWriter($name, $eName, $id, $class);
    return $writer->select($values, $isNullable, $isHash);
  }
  
  public function datetime($name, $yearRange = null, $withSecond = false, $defaultNull = false)
  {
    $eName  = $this->createName("datetime") . "[{$name}]";
    $writer = $this->createHtmlWriter($name, $eName);

    return $writer->datetime($yearRange, $withSecond, $defaultNull);
  }
  
  public function date($name, $yearRange = null, $defaultNull = false)
  {
    $eName  = $this->createName("date") . "[{$name}]";
    $writer = $this->createHtmlWriter($name, $eName);
    return $writer->date($yearRange, $defaultNull);
  }
  
  protected function createName($name)
  {
    return $this->mdlName . "::" . $name;
  }
  
  private function createHtmlWriter($name, $elementName, $id = null, $class = null, $data = array())
  {
    $html = new Form_Html($elementName);
    $html->setValue($this->get($name))->setId($id)->setClass($class)->setData($data);
    
    return $html;
  }
}
