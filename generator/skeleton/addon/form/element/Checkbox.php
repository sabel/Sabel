<?php

/**
 * Form_Element_Checkbox
 *
 * @category  Addon
 * @package   addon.form
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Form_Element_Checkbox extends Form_Element
{
  public function toHtml($options = array())
  {
    $value  = $this->value;
    $name   = $this->name;
    $checks = array();
    
    foreach ($this->data as $v => $text) {
      $check = '<input type="checkbox" ';
      $this->addIdAndClass($check);
      $check .= 'name="' . $name . '[]" value="' . $v . '"';
      
      if ($value !== null) {
        if (($value & $v) !== 0) $check .= ' checked="checked"';
      }
      
      $checks[] = $check . " />{$text}\n";
    }
    
    return implode("&nbsp;", $checks);
  }
}
