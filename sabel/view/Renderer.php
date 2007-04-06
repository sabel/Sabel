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
    $locator = new Sabel_View_Locator_File();
    $condition = new Sabel_View_Locator_Condition(false);
    $condition->setCandidate(Sabel_Context::getCurrentCandidate());
    $condition->setName($name);
    $resource = $locator->locate($condition);
    
    $view = new Sabel_View();
    $view->assignByArray($options);
    return $view->rendering($resource);
  }
  
  public function temp()
  {
    $v = new Sabel_View();
    
    if (isset($options["values"])) {
      $v->assignByArray(array());
    }
    
    if (is_readable(RUN_BASE . "/app/views/" . $template_name)) {
      $v->setPath(RUN_BASE . "/app/views/");
    } else {
      $v->decide(Sabel_Context::getCurrentCandidate(), true);
    }
    
    $v->setName($template_name);
    if ($v->isResourceMissing()) throw new Exception('Template file is not found');
    
    if (isset($options["cache"]) && $options["cache"] === true) {
      $key = "";
      
      if (isset($options["key"])) {
        $key = $options["key"];
      }
      
      $cpath = RUN_BASE . "/cache/" . $template_name . $key;
      
      if (is_readable($cpath)) {
        $partial_html = file_get_contents($cpath);
      } else {
        $partial_html = $v->rendering(false);
        file_put_contents($cpath, $partial_html);
      }
    } else {
      $partial_html = $v->rendering(false);
    }
    
    return $partial_html;
  }
}
