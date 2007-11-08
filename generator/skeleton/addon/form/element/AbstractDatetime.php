<?php

/**
 * Form_Element_AbstractDatetime
 *
 * @abstract
 * @category  Addon
 * @package   addon.form
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class Form_Element_AbstractDatetime extends Form_Element
{
  protected $timestamp = null;
  
  protected function numSelect($type, $name, $start, $end, $defaultNull)
  {
    $html = array('<select name="' . $name . '[' . $type . ']">');
    
    if ($defaultNull) {
      $html[] = '<option></option>';
    }
    
    $val  = (int)$this->selectedValue($type);
    
    for ($i = $start; $i <= $end; $i++) {
      if ($i === $val) {
        $html[] = '<option value="' . $i . '" selected="selected">' . $i . '</option>';
      } else {
        $html[] = '<option value="' . $i . '">' . $i . '</option>';
      }
    }
    
    return implode("\n", $html) . "\n</select>";
  }
  
  protected function selectedValue($type)
  {
    if ($this->timestamp === null) {
      return null;
    }
    
    switch ($type) {
      case "year":
        return date("Y", $this->timestamp);

      case "month":
        return date("n", $this->timestamp);

      case "day":
        return date("j", $this->timestamp);

      case "hour":
        return date("G", $this->timestamp);

      case "minute":
        return date("i", $this->timestamp);

      case "second":
        return date("s", $this->timestamp);
    }
  }
  
  protected function getYearRange($yearRange)
  {
    return ($yearRange === null) ? array(1980, 2035) : $yearRange;
  }
}
