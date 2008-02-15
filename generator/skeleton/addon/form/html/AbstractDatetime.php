<?php

/**
 * Form_Html_AbstractDatetime
 *
 * @abstract
 * @category   Addon
 * @package    addon.form
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class Form_Html_AbstractDatetime extends Sabel_Object
{
  protected
    $name      = "",
    $value     = null,
    $timestamp = null;
    
  public function __construct($name, $value = null)
  {
    $this->name  = $name;
    $this->value = $value;
  }
  
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
    
    return implode(PHP_EOL, $html) . PHP_EOL . "</select>";
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
