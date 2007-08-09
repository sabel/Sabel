<?php

/**
 * Sabel_Controller_Executer
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Creator
{
  /**
   * create controller instance
   *
   * @return a subclass instance of Sabel_Controller_Page
   */
  public function create($destination)
  {
    if (!$destination instanceof Sabel_Destination) {
      throw new Sabel_Exception_Runtime("invalid destination object");
    }
    
    list($module, $controller,) = $destination->toArray();
    $class     = null;
    $instance  = null;
    
    $class  = ucfirst($module);
    $class .= "_" . ucfirst(trim(Sabel_Const::CONTROLLER_DIR, "/"));
    
    if ($controller !== "") {
      $class .= "_" . ucfirst($controller);
    } else {
      $class .= "_" . ucfirst(Sabel_Const::DEFAULT_CONTROLLER);
    }
    
    if (class_exists($class, true)) {
      $instance = new $class();
    } else {
      throw new Sabel_Exception_Runtime("controller not found");
    }
    
    return $instance;
  }
}