<?php

/**
 * Form_Html
 *
 * @category   Addon
 * @package    addon.form
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Form_Html extends Sabel_Object
{
  /**
   * @var string
   */
  protected $name = "";
  
  /**
   * @var string
   */
  protected $value = null;
  
  /**
   * @var string
   */
  protected $id = "";
  
  /**
   * @var string
   */
  protected $class = "";
  
  /**
   * @param string $name
   *
   * @throws Sabel_Exception_InvalidArgument
   * @return self
   */
  public function setName($name)
  {
    if (is_string($name)) {
      $this->name = $name;
    } else {
      throw new Sabel_Exception_InvalidArgument("name must be a string.");
    }
    
    return $this;
  }
  
  /**
   * @param mixed $value
   *
   * @return self
   */
  public function setValue($value)
  {
    $this->value = $value;
    
    return $this;
  }
  
  /**
   * @param string $id
   *
   * @throws Sabel_Exception_InvalidArgument
   * @return self
   */
  public function setId($id)
  {
    if ($id !== null) {
      if (is_string($id)) {
        $this->id = $id;
      } else {
        throw new Sabel_Exception_InvalidArgument("id must be a string.");
      }
    }
    
    return $this;
  }
  
  /**
   * @param string $class
   *
   * @throws Sabel_Exception_InvalidArgument
   * @return self
   */
  public function setClass($class)
  {
    if ($class !== null) {
      if (is_string($class)) {
        $this->class = $class;
      } else {
        throw new Sabel_Exception_InvalidArgument("class must be a string.");
      }
    }
    
    return $this;
  }
  
  /**
   * @return self
   */
  public function clear()
  {
    $this->name  = "";
    $this->id    = "";
    $this->class = "";
    $this->vlaue = null;
    
    return $this;
  }
  
  public function open($uri = "", $method = "post")
  {
    // @todo absolute path, https
    
    $html = '<form action="' . uri($uri) . '" method="' . $method . '" ';
    $this->addIdAndClass($html);
    if ($this->name !== "") $html .= 'name="' . $this->name . '" ';
    
    return $html . '>' . PHP_EOL . '<fieldset>' . PHP_EOL;
  }
  
  public function close()
  {
    return "</fieldset>" . PHP_EOL . "</form>" . PHP_EOL;
  }
  
  public function submit($text = "submit")
  {
    $html = '<input type="submit" ';
    $this->addIdAndClass($html);
    $html .= 'value="' . $text . '" />';
    
    return $html . PHP_EOL;
  }
  
  public function text()
  {
    $html  = '<input type="text" ';
    $this->addIdAndClass($html);
    $html .= 'name="' . $this->name . '" value="' . $this->value . '" />';
    
    return $html;
  }
  
  public function password()
  {
    $html  = '<input type="password" ';
    $this->addIdAndClass($html);
    $html .= 'name="' . $this->name . '" value="' . $this->value . '" />';
    
    return $html;
  }
  
  public function textarea()
  {
    $html  = '<textarea ';
    $this->addIdAndClass($html);
    $html .= 'name="' . $this->name . '">' . $this->value . '</textarea>';
    
    return $html;
  }
  
  public function hidden()
  {
    $html  = '<input type="hidden" ';
    $this->addIdAndClass($html);
    $html .= 'name="' . $this->name . '" value="' . $this->value . '" />';
    
    return $html;
  }
  
  public function checkbox($data)
  {
    $value  = $this->value;
    $name   = $this->name;
    $checks = array();
    
    foreach ($data as $v => $text) {
      $check = '<input type="checkbox" ';
      $this->addIdAndClass($check);
      $check .= 'name="' . $name . '[]" value="' . $v . '"';
      
      if ($value !== null) {
        if (($value & $v) !== 0) $check .= ' checked="checked"';
      }
      
      $checks[] = $check . " />{$text}" . PHP_EOL;
    }
    
    return implode("&nbsp;", $checks);
  }
  
  public function radio($data)
  {
    $count  = 0;
    $radios = array();
    $name   = $this->name;
    $value  = $this->value;
    
    foreach ($data as $v => $text) {
      $radio = '<input type="radio" ';
      $this->addIdAndClass($radio);
      $radio .= 'name="' . $name . '" value="' . $v . '"';
      if ($count === 0 && $value === null || $v === $value) {
        $radio .= ' checked="checked"';
      }
      
      $radios[] = $radio . " />{$text}" . PHP_EOL;
      $count++;
    }
    
    return implode("&nbsp;", $radios);
  }
  
  public function select($data, $isHash = true)
  {
    $options = array();
    $selectedValue = $this->value;
    
    foreach ($data as $key => $value) {
      $k = ($isHash) ? $key : $value;
      if ($selectedValue === $k) {
        $openTag = '<option value="' . $k . '" selected="selected">';
      } else {
        $openTag = '<option value="' . $k . '">';
      }
      
      $options[] = $openTag . $value . '</option>';
    }
    
    $options = implode(PHP_EOL, $options);
    
    $html = '<select name="' . $this->name . '" ';
    $this->addIdAndClass($html);
    return $html . ">" . $options . PHP_EOL . "</select>";
  }
  
  public function datetime($yearRange, $withSecond, $defaultNull)
  {
    $datetime = new Form_Html_Datetime($this->name, $this->value);
    return $datetime->toHtml($yearRange, $withSecond, $defaultNull);
  }
  
  public function date($yearRange, $defaultNull)
  {
    $date = new Form_Html_Date($this->name, $this->value);
    return $date->toHtml($yearRange, $defaultNull);
  }
  
  protected function addIdAndClass(&$html)
  {
    if ($this->id !== "")    $html .= 'id="' . $this->id . '" ';
    if ($this->class !== "") $html .= 'class="' . $this->class . '" ';
  }
}
