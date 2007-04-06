<?php

/**
 * Sabel_View_Resource_String
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Resource_String implements Sabel_View_Resource
{
  protected final $string = "";
  
  public function set($string)
  {
    $this->string = $string;
  }
  
  public function isResourceMissing()
  {
    return ($this->string === "");
  }
}
