<?php

/**
 * Sabel_View_Renderer
 *
 * @abstract
 * @category   Template
 * @package    org.sabel.template
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_View_Renderer
{
  const COMPILE_DIR = "/data/compile";
  
  protected $trim = true;
  
  public function partial($name, $controller = null, $options = array())
  {
    $context = Sabel_Context::getContext();
    $destination = clone $context->getBus()->get("destination");
    $responses   = $context->getBus()->get("response")->getResponses();
    
    if ($controller !== null) {
      $destination->setController($controller);
    }
    
    $destination->setAction($name);
    
    $repository = new Sabel_View_Repository_File($destination);
    $renderer = new Sabel_View_Renderer_Class();
    $resource = $repository->find();
    return $renderer->rendering($resource->fetch(), $responses);
  }
}
