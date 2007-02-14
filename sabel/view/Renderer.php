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
  
  public function partial($templateName, $values = array())
  {
    $v = new Sabel_View();
    $v->assignByArray($values);
    $v->decideTemplatePath(Sabel_Context::getCurrentCandidate());
    $v->setTemplateName($templateName);
    if ($v->isTemplateMissing()) throw new Exception('Template file is not found');
    return $v->rendering(false);
  }
}
