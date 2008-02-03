<?php

/**
 * Renderer_Processor
 *
 * @category   Addon
 * @package    addon.renderer
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Renderer_Processor extends Sabel_Bus_Processor
{
  private $viewRenderer = null;
  
  public function setRenderer(Sabel_View_Renderer $viewRenderer)
  {
    $this->viewRenderer = $viewRenderer;
  }
  
  public function execute($bus)
  {
    $bus->set("renderer", $this->viewRenderer);
  }
}
