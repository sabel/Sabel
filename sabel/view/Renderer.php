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
    $view    = new Sabel_View();
    $locator = Sabel_View_Locator_Factory::create()->make();
    
    $resources = $locator->locate($name);
    
    $view->assignByArray($options);
    return $view->rendering($resources->template);
  }
}
