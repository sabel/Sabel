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
  
  public function partial($templateName)
  {
    $v = new Sabel_View('wiki');
    $v->setTemplateName($templateName);
    return $v->rendering(false);
  }
}
