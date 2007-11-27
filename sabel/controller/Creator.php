<?php

/**
 * Sabel_Controller_Creator
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Creator extends Sabel_Object
{
  const CONTROLLERS_DIR    = "controllers";
  const DEFAULT_CONTROLLER = "index";
  
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
    $class    = null;
    $instance = null;
    
    $class  = ucfirst($module);
    $class .= "_" . ucfirst(self::CONTROLLERS_DIR);
    
    if ($controller !== "") {
      $class .= "_" . ucfirst($controller);
    } else {
      $class .= "_" . ucfirst(self::DEFAULT_CONTROLLER);
    }
    
    if (class_exists($class, true)) {
      $instance = new $class();
      l("instanciate " . $class);
    } else {
      throw new Sabel_Exception_Runtime("controller not found");
    }
    
    return $instance;
  }
}
