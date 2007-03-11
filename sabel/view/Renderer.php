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
  const CACHE_DIR   = '/cache/';
  
  protected $trim = true;
  
  abstract public function rendering($path, $name, $values);
  
  public function partial($template_name, $options = array())
  {
    $v = new Sabel_View();
    
    if (isset($options["values"])) {
      $v->assignByArray(array());
    }
    
    if (is_readable(RUN_BASE . "/app/views/" . $template_name)) {
      $v->setTemplatePath(RUN_BASE . "/app/views/");
    } else {
      $v->decideTemplatePath(Sabel_Context::getCurrentCandidate(), true);
    }
    
    $v->setTemplateName($template_name);
    if ($v->isTemplateMissing()) throw new Exception('Template file is not found');
    
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