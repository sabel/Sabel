<?php

/**
 * Form_Element_Datetime
 *
 * @category  Addon
 * @package   addon.form
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Form_Element_Datetime extends Form_Element_AbstractDatetime
{
  public function toHtml($options = array())
  {
    if ($this->value !== null) {
      $this->timestamp = strtotime($this->value);
    } else {
      $this->timestamp = time();
    }
    
    $yearRange   = (isset($options["yearRange"]))   ? $options["yearRange"]   : null;
    $withSecond  = (isset($options["withSecond"]))  ? $options["withSecond"]  : false;
    $defaultNull = (isset($options["defaultNull"])) ? $options["defaultNull"] : false;
    
    $name = $this->name;
    list ($first, $last) = $this->getYearRange($yearRange);
    
    $html   = array();
    $html[] = $this->numSelect("year",   $name, $first, $last, $defaultNull);
    $html[] = $this->numSelect("month",  $name, 1, 12, $defaultNull);
    $html[] = $this->numSelect("day",    $name, 1, 31, $defaultNull);
    $html[] = $this->numSelect("hour",   $name, 0, 23, $defaultNull);
    $html[] = $this->numSelect("minute", $name, 0, 59, $defaultNull);
    
    if ($withSecond) {
      $html[] = $this->numSelect("second", $name, 0, 59);
    }
    
    return implode("&nbsp;", $html);
  }
}
