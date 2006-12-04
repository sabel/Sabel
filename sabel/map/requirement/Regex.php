<?php

/**
 * requirement compare with regex pattern
 *
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Map_Requirement_Regex implements Sabel_Map_Requirement_Interface
{
  protected $regex = '';
  
  public function __construct($regex)
  {
    $this->regex = $regex;
  }
  
  public function isMatch($value)
  {
    $match = preg_match($this->regex, $value, $matches);
    return (boolean) $match;
  }
}