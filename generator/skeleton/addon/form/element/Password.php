<?php

/**
 * Form_Element_Password
 *
 * @category  Addon
 * @package   addon.form
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Form_Element_Password extends Form_Element
{
  public function toHtml($options = array())
  {
    $html  = '<input type="password" ';
    $this->addIdAndClass($html);
    $html .= 'name="' . $this->name . '" value="' . $this->value . '" />';
    
    return $html;
  }
}