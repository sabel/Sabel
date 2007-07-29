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
  
  private $gettext = null;
  
  public static function create()
  {
    $ins = new self();
    $ins->gettext = Sabel_I18n_Gettext::getInstance();
    
    return $ins;
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
    
    $locations = array();
    $path      = $this->getPath($module);
    $spcPath   = $path . self::VIEW_DIR;
    $tplFile   = $name . self::TPL_SUFFIX;
    
    // app/views/{action}.tpl
    $locations[] = array("path" => RUN_BASE . self::APP_VIEW, "file" => $tplFile);
    
    // app/{module}/views/{action}.tpl
    $locations[] = array("path" => $spcPath, "file" => $tplFile);
    
    // app/{module}/views/{controller}/{action}.tpl
    $mcaPath = $spcPath . $controller . DIR_DIVIDER;
    $locations[] = array("path" => $mcaPath, "file" => $tplFile);
    
    // app/{module}/views/{controller}.{action}.tpl
    $locations[] = array("path" => $spcPath, "file" => $controller . "." . $tplFile);
    
    // app/{module}/{action}.tpl
    // $locations[] = array("path" => $path, "file" => $tplFile);
    
    $gettext = $this->gettext;
    if ($gettext->isInitialized() && !$gettext->isGettext()) {
      $locale = $gettext->getBrowser()->getLocale();
      foreach ($locations as $l) {
        $localePath = $l["path"] . $name . DIR_DIVIDER;
        $locator->addLocation($localePath, $locale . self::TPL_SUFFIX);
        $locator->addLocation($l["path"], $l["file"]);
      }
    } else {
      foreach ($locations as $l) {
        $locator->addLocation($l["path"], $l["file"]);
      }
    }
  }
  
  private final function getPath($module)
  {
    return RUN_BASE . Sabel_Const::MODULES_DIR . $module . DIR_DIVIDER;
  }
}
