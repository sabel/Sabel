<?php

/**
 * Processor_Location
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Location extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $destination = $bus->get("destination");
    $repository  = new Sabel_View_Repository_File($destination);
    
    list ($module, $controller) = $destination->toArray();
    $base = $repository->getPathToBaseDirectory($module);
    
    // app/views/
    $rootLocation = new Sabel_View_Location_File("root", $destination);
    $rootLocation->setPath(RUN_BASE . Sabel_View_Repository_File::APP_VIEW);
    
    // app/{module}/views/
    $moduleLocation = new Sabel_View_Location_File("module", $destination);
    $moduleLocation->setPath($base . Sabel_View_Repository_File::VIEW_DIR);
    
    // app/{module}/views/{controller}/
    $leafLocation = new Sabel_View_Location_File("leaf", $destination);
    $leafLocation->setPath($base . Sabel_View_Repository_File::VIEW_DIR . $controller . DS);
    
    $repository->addLocation($rootLocation);
    $repository->addLocation($moduleLocation);
    $repository->addLocation($leafLocation);
    
    $bus->set("repository", $repository);
  }
}
