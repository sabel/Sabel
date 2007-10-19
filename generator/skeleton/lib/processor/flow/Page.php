<?php

/**
 * Abstract Processor_Flow_Page Controller
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Processor_Flow_Page extends Sabel_Controller_Page
{
  protected function __get($name)
  {
    if (isset($this->attributes["flow"])) {
      if ($this->getAttribute("flow")->has($name)) {
        return $this->getAttribute("flow")->read($name);
      } elseif (array_key_exists($name, $this->attributes)) {
        return $this->attributes[$name];
      } else {
        return null;
      }
    }
  }
  
  protected function __set($name, $value)
  {
    if (isset($this->attributes["flow"])) {
      $this->getAttribute("flow")->write($name, $value);
    } else {
      $this->attributes[$name] = $value;
    }
  }
}
