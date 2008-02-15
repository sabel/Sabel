<?php

/**
 * Renderer_Addon
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.renderer
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Renderer_Addon extends Sabel_Object
{
  public function execute($bus)
  {
    $renderer = new Renderer_Sabel();
    
    if ($renderer->hasMethod("initialize")) {
      $renderer->initialize();
    }
    
    $bus->set("renderer", $renderer);
  }
}
