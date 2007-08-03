<?php

/**
 * Helpers_Form_Select_Datetime
 *
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Helpers_Form_Select_Datetime
{
  protected $options = array("yMin" => 1970,
                             "yMax" => 2038);
                             
  protected $name      = "";
  protected $timestamp = null;
  
  public function __construct($name, $datetime = null)
  {
    $this->name = $name;
    
    if ($datetime === null) {
      $this->timestamp = time();
    } else {
      $this->timestamp = strtotime($datetime);
    }
  }
  
  public function create($withSecond = false)
  {
    $name    = $this->name;
    $select  = new Helpers_Form_Select($name . "_year");
    $options = array();
    
    for ($i = $this->options["yMin"]; $i <= $this->options["yMax"]; $i++) {
      $options[] = $i;
    }
    
    $select->setOptions($options);
    $value = $this->selectedValue("year", $this->options["yMin"]);
    $select->setSelected((int)$value);
    $html  = array($select->create());
    
    $html[] = $this->numSelect("month",  $name . "_month",  1, 12);
    $html[] = $this->numSelect("day",    $name . "_day",    1, 31);
    $html[] = $this->numSelect("hour",   $name . "_hour",   0, 23);
    $html[] = $this->numSelect("minute", $name . "_minute", 0, 59);
    
    if ($withSecond) {
      $html[] = $this->numSelect("second", $name . "_second", 0, 59);
    }
    
    return implode("&nbsp;", $html);
  }
  
  public function setYearMax($max)
  {
    $this->options["yMax"] = $max;
  }
  
  public function setYearMin($min)
  {
    $this->options["yMin"] = $min;
  }
  
  protected function numSelect($type, $name, $start, $end)
  {
    $html = array('<select name="' . $name . '">');
    $val  = (int)$this->selectedValue($type, $start);
    
    for ($i = $start; $i <= $end; $i++) {
      if ($i === $val) {
        $html[] = '<option value="' . $i . '" selected="selected">' . $i . '</option>';
      } else {
        $html[] = '<option value="' . $i . '">' . $i . '</option>';
      }
    }
    
    return implode("\n", $html) . "\n</select>";
  }
  
  protected function selectedValue($type, $start)
  {
    if ($this->timestamp === null) {
      return $start;
    } else {
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
  }
}
