<?php

/**
 * Form_Html_Date
 *
 * @category  Addon
 * @package   addon.form
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Form_Html_Date extends Form_Html_AbstractDatetime
{
  public function toHtml($yearRange, $defaultNull)
  {
    if ($this->value !== null) {
      $this->timestamp = strtotime($this->value);
    } else {
      $this->timestamp = time();
    }
    
    $name = $this->name;
    list ($first, $last) = $this->getYearRange($yearRange);
    
    $html   = array();
    $html[] = $this->numSelect("year",  $name, $first, $last, $defaultNull);
    $html[] = $this->numSelect("month", $name, 1, 12, $defaultNull);
    $html[] = $this->numSelect("day",   $name, 1, 31, $defaultNull);
    
    return implode("&nbsp;", $html);
  }
}
