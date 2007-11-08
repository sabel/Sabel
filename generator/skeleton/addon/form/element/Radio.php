<?php

/**
 * Form_Element_Radio
 *
 * @category  Addon
 * @package   addon.form
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Form_Element_Radio extends Form_Element
{
  public function toHtml($options = array())
  {
    $count  = 0;
    $radios = array();
    $name   = $this->name;
    $value  = $this->value;
    
    if (isset($options["isNullable"])) {
      $isNullable = $options["isNullable"];
    } else {
      $isNullable = true;
    }
    
    foreach ($this->data as $v => $text) {
      $radio = '<input type="radio" ';
      $this->addIdAndClass($radio);
      $radio .= 'name="' . $name . '" value="' . $v . '"';
      if ($count === 0 && $value === null && !$isNullable || $v === $value) {
        $radio .= ' checked="checked"';
      }
      
      $radios[] = $radio . " />{$text}\n";
      $count++;
    }
    
    return implode("&nbsp;", $radios);
  }
}
