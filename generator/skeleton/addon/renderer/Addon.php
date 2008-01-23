<?php

/**
 * Renderer_Addon
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.renderer
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Renderer_Addon extends Sabel_Object
{
  const VERSION = 1;
  
  public function version()
  {
    return self::VERSION;
  }
  
  public function execute($bus)
  {
    $processor = new Renderer_Processor("renderer");
    $renderer  = new Renderer_Sabel();
    
    if ($renderer->hasMethod("initialize")) {
      $renderer->initialize();
    }
    
    $processor->setRenderer($renderer);
    $bus->getProcessorList()->insertPrevious("view", "renderer", $processor);
  }
}
