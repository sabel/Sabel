<?php

/**
 * Helpers_Form_Select
 *
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Helpers_Form_Select
{
  protected $name     = "";
  protected $size     = 1;
  protected $options  = array();
  protected $selected = null;
  
  public function __construct($name, $size = 1)
  {
    $this->name = $name;
    $this->size = $size;
  }
  
  public function setOptions($options)
  {
    $this->options = $options;
  }
  
  public function setSelected($value)
  {
    $this->selected = $value;
  }
  
  public function create($id = null, $class = null, $multiple = false)
  {
    $idAndClass = $this->getIdAndClass($id, $class);
    $multiple   = ($multiple) ? ' multiple="multiple"' : '';
    $selected   = $this->selected;
    $options    = $this->options;
    
    $html = '<select ' . $idAndClass . 'name="' . $this->name
          . '" size="' . $this->size . '"' . $multiple . '>';
          
    $html = array($html);
    
    if (is_hash($options)) {
      $this->hashOptions($html, $options, $selected);
    } else {
      $this->arrayOptions($html, $options, $selected);
    }
    
    return implode("\n", $html) . "\n</select>";
  }
  
  protected function arrayOptions(&$html, $options, $selected)
  {
    foreach ($this->options as $value) {
      if ($selected === $value) {
        $html[] = '<option value="' . $value . '" selected="selected">'
                . $value . '</option>';
      } else {
        $html[] = '<option value="' . $value . '">' . $value . '</option>';
      }
    }
  }
  
  protected function hashOptions(&$html, $options, $selected)
  {
    foreach ($this->options as $key => $value) {
      if ($selected === $key) {
        $html[] = '<option value="' . $key . '" selected="selected">'
                . $value . '</option>';
      } else {
        $html[] = '<option value="' . $key . '">' . $value . '</option>';
      }
    }
  }
  
  protected function getIdAndClass($id, $class)
  {
    if ($id !== null && $class !== null) {
      return 'id="' . $id . '" class="' . $class . '" ';
    } elseif ($id !== null) {
      return 'id="' . $id . '" ';
    } elseif ($class !== null) {
      return 'class="' . $class . '" ';
    } else {
      return "";
    }
  }
}
