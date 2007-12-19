<?php

/**
 * Renderer_Addon
 *
 * @version    1.0
 * @category   Addon
 * @package    addon
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
  
  public function load()
  {
    return true;
  }
  
  public function loadProcessor($bus)
  {
    $renderer = new Renderer_Processor("renderer");
    $renderer->setRenderer(new Renderer_Sabel());
    $bus->getList()->find("view")->insertPrevious("renderer", $renderer);
  }
}
