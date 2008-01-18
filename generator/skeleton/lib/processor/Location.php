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
    list ($module, $controller) = $this->destination->toArray();
    
    $template = new Sabel_View_Template_File(VIEW_DIR_NAME . DS);
    $template->addPath("controller", $module . DS . VIEW_DIR_NAME . DS . $controller . DS);
    $template->addPath("module", $module . DS . VIEW_DIR_NAME . DS);
    
    $this->repository = new Sabel_View_Repository($template);
    
    if ($this->controller instanceof Sabel_Controller_Page) {
      $this->controller->repository = $this->repository;
    }
  }
}
