<?php

/**
 * Renderer_Sabel_Parser
 *
 * @category   Addon
 * @package    addon.renderer
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Renderer_Sabel_Parser extends Sabel_Object
{
  public function getElements($template)
  {
    $regex = '/<[^\/!?]("[^"]*"|\'[^\']*\'|[^\'">])*>/U';
    
    if (preg_match_all($regex, $template, $matches)) {
      $elements = array();
      foreach ($matches[0] as $tag) {
        $elements[] = new Renderer_Sabel_Element($tag);
      }
      
      return $elements;
    } else {
      return array();
    }
  }
}
