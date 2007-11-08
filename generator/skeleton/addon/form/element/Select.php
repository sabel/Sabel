<?php

/**
 * Form_Element_Select
 *
 * @category  Addon
 * @package   addon
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Form_Element_Select extends Form_Element
{
  public function toHtml($options = array())
  {
    if (isset($options["isNullable"])) {
      $isNullable = $options["isNullable"];
    } else {
      $isNullable = true;
    }
    
    if (isset($options["useKey"])) {
      $useKey = $options["useKey"];
    } else {
      $useKey = true;
    }
    
    $options = $this->createOptions($isNullable, $useKey);
    
    $html = '<select name="' . $this->name . '" ';
    $this->addIdAndClass($html);
    return $html . ">" . $options . "\n</select>";
  }
  
  private function createOptions($isNullable, $useKey)
  {
    $html = array();
    $selectedValue = $this->value;
    if ($isNullable) $html[] = '<option value=""></option>';
    
    foreach ($this->data as $key => $value) {
      $k = ($useKey) ? $key : $value;
      if ($selectedValue === $k) {
        $html[] = '<option value="' . $k . '" selected="selected">';
      } else {
        $html[] = '<option value="' . $k . '">';
      }
      
      $html[] = $value . '</option>';
    }
    
    return implode("\n", $html);
  }
}
