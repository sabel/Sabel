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
  private $string = "";
  private $renderer = null;
  
  public function set($string)
  {
    $this->string = $string;
  }
  
  public final function setRenderer($renderer)
  {
    if ($renderer instanceof Sabel_View_Renderer) {
      $this->renderer = $renderer;
    } else {
      throw new Exception("pass invalid renderer");
    }
  }
  
  public function isResourceMissing()
  {
    return ($this->string === "");
  }
  
  public function fetch($values)
  {
    return $this->renderer->rendering($this->string, $values);
  }
}
