<?php

/**
 * Renderer_Processor
 *
 * @version    1.0
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <ebine.yutaka@gmail.com>
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
    $this->renderer = $this;
  }
  
  public function rendering($contents, $assigns, $path = null)
  {
    return $this->viewRenderer->rendering($contents, $assigns, $path);
  }
}
