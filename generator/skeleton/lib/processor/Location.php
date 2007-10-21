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
    $this->repository = new Sabel_View_Repository_File($this->destination);
    
    list ($module, $controller) = $this->destination->toArray();
    $base = $this->repository->getPathToBaseDirectory($module);
    
    // app/views/
    $rootLocation = new Sabel_View_Location_File("root", $this->destination);
    $rootLocation->setPath(RUN_BASE . DS . APP_VIEW);
    
    // app/{module}/views/
    $moduleLocation = new Sabel_View_Location_File("module", $this->destination);
    $moduleLocation->setPath($base . VIEW_DIR);
    
    // app/{module}/views/{controller}/
    $leafLocation = new Sabel_View_Location_File("leaf", $this->destination);
    $leafLocation->setPath($base . VIEW_DIR . $controller . DS);
    
    $this->repository->addLocation($rootLocation);
    $this->repository->addLocation($moduleLocation);
    $this->repository->addLocation($leafLocation);
  }
}
