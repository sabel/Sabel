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
  const COMPILE_DIR = '/data/compiled/';
  
  protected $trim = true;
  
  public function partial($name, $options = array())
  {
    $context = Sabel_Context::getContext();
    $destination = clone $context->getDestination();
    $destination->setAction($name);
    
    return Sabel_View::render($destination, array("assign" => $options));
  }
}
