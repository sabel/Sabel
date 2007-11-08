<?php

/**
 * Form_Element_Factory
 *
 * @abstract
 * @category  Addon
 * @package   addon.form
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Form_Element_Factory
{
  public static function create($elementType, $name)
  {
    switch ($elementType) {
      case Form_Element::FORM:
        return new Form_Element_Form($name);
        
      case Form_Element::TEXT:
        return new Form_Element_Text($name);
        
      case Form_Element::PASSWORD:
        return new Form_Element_Password($name);
        
      case Form_Element::TEXTAREA:
        return new Form_Element_Textarea($name);
        
      case Form_Element::SELECT:
        return new Form_Element_Select($name);
        
      case Form_Element::RADIO:
        return new Form_Element_Radio($name);
        
      case Form_Element::CHECK:
        return new Form_Element_Checkbox($name);
        
      case Form_Element::DATETIME:
        return new Form_Element_Datetime($name);
        
      case Form_Element::DATE:
        return new Form_Element_Date($name);
        
      case Form_Element::HIDDEN:
        return new Form_Element_Hidden($name);
    }
  }
}
