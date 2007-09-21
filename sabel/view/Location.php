<?php

/**
 * Sabel_View_Location
 *
 * @category   View
 * @package    org.sabel.view.location
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Hamanaka Kazuhiro <hamanaka.kazuhiro@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_View_Location
{
  protected $name = "";
  protected $destination = null;
  
  public function __construct($name, Sabel_Destination $destination)
  {
    $this->name = $name;
    $this->destination = $destination;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  abstract public function isResourceValid($name);
  abstract public function getResource($name);
  abstract public function getResourceList();
}
