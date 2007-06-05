<?php

/**
 * Sabel_View_Locator
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Locator_Factory
{
  const VIEW_DIR   = "views/";
  const APP_VIEW   = "/app/views/";
  const DEF_LAYOUT = "layout.tpl";
  const TPL_SUFFIX = ".tpl";
  
  public static function create()
  {
    return new self();
  }
  
  public function make($destination)
  {
    $locator = new Sabel_View_Locator_File();
    $this->setLocations($locator, $destination);
    return $locator;
  }
  
  private final function setLocations($locator, $destination)
  {
    if (!$destination instanceof Sabel_Destination) {
      $msg  = "call without require argument ";
      $msg .= "Sabel_Controller_Executer::__construct(arg)";
      $msg .= " arg must be Sabel_Destination";
      throw new Sabel_Exception_Runtime($msg);
    }
    
    list($module, $controller, $name) = $destination->toArray();
    
    $tpldir = Sabel_Const::TEMPLATE_DIR;
    $path = $this->getPath($module);
    $spcPath = $path . self::VIEW_DIR;
    
    $locator->addLocation(RUN_BASE . self::APP_VIEW, $name);
    $locator->addLocation($spcPath, $name.self::TPL_SUFFIX);
    $locator->addLocation($spcPath, $controller . "/" . $name.self::TPL_SUFFIX);
    $locator->addLocation($spcPath, $controller . "." . $name.self::TPL_SUFFIX);
    $locator->addLocation($path, $name);
    $locator->addLocation($path . $tpldir, $name);
    $locator->addLocation($path, $name);
    $locator->addLocation($path, $name.self::TPL_SUFFIX);
  }
  
  private final function getPath($module)
  {
    return RUN_BASE . Sabel_Const::MODULES_DIR . $module . DIR_DIVIDER;
  }
}
