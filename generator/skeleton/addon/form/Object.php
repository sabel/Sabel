<?php

/**
 * Form_Object
 *
 * @category   Addon
 * @package    addon.form
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Form_Object extends Sabel_Object
{
  /**
   * @var Sabel_DB_Model
   */
  protected $model = null;
  
  /**
   * @var string
   */
  protected $mdlName = "";
  
  /**
   * @var string
   */
  protected $formName = "";
  
  /**
   * @var string
   */
  protected $token = null;
  
  /**
   * @var Sabel_DB_Metadata_Column[]
   */
  protected $columns = array();
  
  /**
   * @var array
   */
  protected $errors = array();
  
  /**
   * @var array
   */
  protected $allowCols = array();
  
  public function __construct($model, $fName, $token = null)
  {
    if (is_string($model)) {
      $model = MODEL($model);
    }
    
    $this->model    = $model;
    $this->formName = $fName;
    $this->token    = $token;
    $this->mdlName  = $model->getName();
    $this->columns  = $model->getColumns();
  }
  
  /**
   * @return string
   */
  public function getToken()
  {
    return $this->token;
  }
  
  /**
   * @return string
   */
  public function getFormName()
  {
    return $this->formName;
  }
  
  /**
   * @return Sabel_DB_Model
   */
  public function getModel()
  {
    return $this->model;
  }
  
  /**
   * @param string $key
   * @param mixed  $val
   *
   * @return void
   */
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
  
  /**
   * @param array $allowCols
   *
   * @return void
   */
  public function setAllowColumns(array $allowCols)
  {
    $this->allowCols = $allowCols;
  }
  
  /**
   * @return array
   */
  public function getAllowColumns()
  {
    return $this->allowCols;
  }
  
  /**
   * @param array $errors
   *
   * @return void
   */
  public function setErrors($errors)
  {
    $this->errors = $errors;
  }
  
  /**
   * @return array
   */
  public function getErrors()
  {
    return $this->errors;
  }
  
  /**
   * @return boolean
   */
  public function hasError()
  {
    return !empty($this->errors);
  }
  
  /**
   * @return void
   */
  public function unsetErrors()
  {
    $this->errors = array();
  }
  
  /**
   * @param array $ignores
   *
   * @return boolean
   */
  public function validate($ignores = array())
  {
    if (is_string($ignores)) {
      $ignores = array($ignores);
    }
    
    $model = $this->model;
    $validator = new Sabel_DB_Validator($model);
    $annot = $model->getReflection()->getAnnotation("validateIgnores");
    
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
  
  /**
   * @param string $colName
   *
   * @return string
   */
  public function name($colName)
  {
    static $names = array();
    $mdlName = $this->mdlName;
    
    if (empty($names[$mdlName])) {
      $names[$mdlName] = Sabel_DB_Model_Localize::getColumnNames($mdlName);
    }
    
    return (isset($names[$mdlName][$colName])) ? $names[$mdlName][$colName] : $colName;
  }
  
  /**
   * @param string $colName
   * @param string $mark
   * @param string $tag
   *
   * @return string
   */
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
    $html = $this->getHtmlWriter("", $name, $id, $class)->open($uri, $method);
    
    if ($this->token === null) {
      return $html;
    } else {
      return $html . '<input type="hidden" name="token" value="' . $this->token . '"/>' . PHP_EOL;
    }
  }
  
  public function end()
  {
    return $this->getHtmlWriter("", "")->close();
  }
  
  public function submit($text = null, $class = null, $id = null)
  {
    return $this->getHtmlWriter("", "", $id, $class)->submit($text);
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
    $eName = $this->createInputName($name);
    return $this->getHtmlWriter($name, $eName, $id, $class)->text();
  }
  
  public function password($name, $class = null, $id = null)
  {
    $eName = $this->createInputName($name);
    return $this->getHtmlWriter($name, $eName, $id, $class)->password();
  }
  
  public function textarea($name, $class = null, $id = null)
  {
    $eName = $this->createInputName($name);
    return $this->getHtmlWriter($name, $eName, $id, $class)->textarea();
  }
  
  public function hidden($name, $class = null, $id = null)
  {
    $eName = $this->createInputName($name);
    return $this->getHtmlWriter($name, $eName, $id, $class)->hidden();
  }
  
  public function checkbox($name, $values, $class = null, $id = null)
  {
    $eName = $this->createInputName($name);
    return $this->getHtmlWriter($name, $eName, $id, $class)->checkbox($values);
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
    
    $eName  = $this->createInputName($name);
    $writer = $this->getHtmlWriter($name, $eName, $id, $class);
    return $writer->radio($values, $isNullable);
  }
  
  public function select($name, $values, $class = null, $id = null, $isHash = true)
  {
    $isNullable = (isset($this->columns[$name])) ? $this->columns[$name]->nullable : true;
    
    $eName  = $this->createInputName($name);
    $writer = $this->getHtmlWriter($name, $eName, $id, $class);
    return $writer->select($values, $isNullable, $isHash);
  }
  
  public function datetime($name, $yearRange = null, $withSecond = false, $defaultNull = false)
  {
    $eName  = $this->createInputName("datetime") . "[{$name}]";
    $writer = $this->getHtmlWriter($name, $eName);
    
    return $writer->datetime($yearRange, $withSecond, $defaultNull);
  }
  
  public function date($name, $yearRange = null, $defaultNull = false)
  {
    $eName  = $this->createInputName("date") . "[{$name}]";
    $writer = $this->getHtmlWriter($name, $eName);
    return $writer->date($yearRange, $defaultNull);
  }
  
  protected function createInputName($name)
  {
    return $this->mdlName . "::" . $name;
  }
  
  private function getHtmlWriter($name, $inputName, $id = null, $class = null)
  {
    if ($name !== "") $this->allowCols[] = $name;
    
    $html = new Form_Html($inputName);
    return $html->setValue($this->get($name))->setId($id)->setClass($class);
  }
  
  public function __sleep()
  {
    $this->model   = $this->model->toArray();
    $this->columns = array();
    
    return array_keys(get_object_vars($this));
  }
  
  public function __wakeup()
  {
    $model = MODEL($this->mdlName);
    $model->setProperties($this->model);
    
    $this->model   = $model;
    $this->columns = $model->getColumns();
  }
}
