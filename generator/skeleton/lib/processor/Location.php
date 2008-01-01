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
    
    $leafLocation = new Sabel_View_Location_File("leaf", $this->destination);
    $leafLocation->setPath($base . VIEW_DIR_NAME . DS . $controller . DS);
    
    $moduleLocation = new Sabel_View_Location_File("module", $this->destination);
    $moduleLocation->setPath($base . VIEW_DIR_NAME . DS);
    
    $rootLocation = new Sabel_View_Location_File("root", $this->destination);
    $rootLocation->setPath(MODULES_DIR_PATH . DS . VIEW_DIR_NAME . DS);
    
    $this->repository->addLocation($leafLocation);
    $this->repository->addLocation($moduleLocation);
    $this->repository->addLocation($rootLocation);
  }
}
