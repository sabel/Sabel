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
  const NAME_SEPARATOR = ":";
  
  /**
   * @var Sabel_Db_Model
   */
  protected $model = null;
  
  /**
   * @var string
   */
  protected $mdlName = "";
  
  /**
   * @var boolean
   */
  protected $isSelected = false;
  
  /**
   * @var array
   */
  protected $updateValues = array();
  
  /**
   * @var Sabel_Db_Metadata_Column[]
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
  
  public function __construct($model)
  {
    if (is_string($model)) {
      $model = MODEL($model);
    }
    
    $this->model      = $model;
    $this->mdlName    = $model->getName();
    $this->columns    = $model->getColumns();
    $this->isSelected = $model->isSelected();
  }
  
  /**
   * @return Sabel_Db_Model
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
  
  /**
   * @return mixed
   */
  public function get($key)
  {
    return $this->model->__get($key);
  }
  
  /**
   * @param string $key
   * @param mixed  $val
   *
   * @return void
   */
  public function __set($key, $val)
  {
    $this->set($key, $val);
  }
  
  /**
   * @return mixed
   */
  public function __get($key)
  {
    return $this->get($key);
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
    
    $validator = new Sabel_Db_Validator($this->model);
    $this->errors = $validator->validate($ignores);
    return empty($this->errors);
  }
  
  /**
   * @param string $colName
   *
   * @return string
   */
  public function name($colName)
  {
    static $names = null;
    
    if ($names === null) {
      $names = Sabel_Db_Model_Localize::getColumnNames($this->mdlName);
    }
    
    return (isset($names[$colName])) ? $names[$colName] : $colName;
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
  
  public function open($uri, $class = null, $id = null, $method = "post", $name = "")
  {
    return $this->getHtmlWriter("", $name, $id, $class)->open($uri, $method);
  }
  
  public function close()
  {
    $hidden = "";
    if ($this->isSelected && ($vColumn = $this->model->getVersionColumn()) !== null) {
      $hidden = $this->hidden($vColumn);
    }
    
    return $hidden . $this->getHtmlWriter("", "")->close();
  }
  
  public function submit($text = null, $class = null, $id = null)
  {
    return $this->getHtmlWriter("", "", $id, $class)->submit($text);
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
    $eName  = $this->createInputName($name);
    $writer = $this->getHtmlWriter($name, $eName, $id, $class);
    return $writer->radio($values);
  }
  
  public function select($name, $values, $class = null, $id = null, $isHash = true)
  {
    $eName  = $this->createInputName($name);
    $writer = $this->getHtmlWriter($name, $eName, $id, $class);
    return $writer->select($values, $isHash);
  }
  
  public function datetime($name, $yearRange = null, $withSecond = false, $defaultNull = false)
  {
    $eName  = $this->createInputName("sbl_datetime") . "[{$name}]";
    $writer = $this->getHtmlWriter($name, $eName);
    
    return $writer->datetime($yearRange, $withSecond, $defaultNull);
  }
  
  public function date($name, $yearRange = null, $defaultNull = false)
  {
    $eName  = $this->createInputName("sbl_date") . "[{$name}]";
    $writer = $this->getHtmlWriter($name, $eName);
    
    return $writer->date($yearRange, $defaultNull);
  }
  
  protected function createInputName($name)
  {
    return $this->mdlName . self::NAME_SEPARATOR . $name;
  }
  
  private function getHtmlWriter($name, $inputName, $id = null, $class = null)
  {
    if ($name !== "" && !in_array($name, $this->allowCols, true)) {
      $this->allowCols[] = $name;
    }
    
    static $htmlWriter = null;
    
    $value = null;
    if ($htmlWriter === null) {
      $htmlWriter = new Form_Html();
    } else {
      $htmlWriter->clear();
    }
    
    $value = $this->get($name);
    if (isset($this->columns[$name]) && $this->columns[$name]->isBool()) {
      if ($value !== null) $value = ($value) ? 1 : 0;
    } elseif (is_string($value)) {
      $value = htmlescape($value);
    }
    
    return $htmlWriter->setName($inputName)
                      ->setValue($value)
                      ->setId($id)
                      ->setClass($class);
  }
  
  public function __sleep()
  {
    l("[form] serialize form object", SBL_LOG_DEBUG);
    
    $model = $this->model;
    $this->model = $model->toArray();
    
    if ($this->isSelected) {
      $this->updateValues = $model->getUpdateValues();
    }
    
    $this->columns = array();
    
    return array_keys(get_object_vars($this));
  }
  
  public function __wakeup()
  {
    l("[form] unserialize form object", SBL_LOG_DEBUG);
    
    $values = $this->model;
    $this->model = MODEL($this->mdlName);
    
    if ($this->isSelected) {
      $this->model->setProperties($values);
      $this->model->setUpdateValues($this->updateValues);
    } else {
      $this->model->setValues($values);
    }
    
    $this->columns = $this->model->getColumns();
  }
}
