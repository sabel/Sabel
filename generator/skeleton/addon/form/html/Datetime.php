<?php

/**
 * Form_Html_Datetime
 *
 * @category   Addon
 * @package    addon.form
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Form_Html_Datetime extends Form_Html_AbstractDatetime
{
  public function toHtml($yearRange, $withSecond, $defaultNull)
  {
    if ($this->value !== null) {
      $this->timestamp = strtotime($this->value);
    } else {
      $this->timestamp = time();
    }
    
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
