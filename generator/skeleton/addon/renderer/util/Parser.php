<?php

/**
 * Renderer_Util_Parser
 *
 * @category   Addon
 * @package    addon.renderer
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Renderer_Util_Parser extends Sabel_Object
{
  public function getElements($template)
  {
    $regex = '/<[^\/!?]("[^"]*"|\'[^\']*\'|[^\'">])*>/';
    
    if (preg_match_all($regex, $template, $matches)) {
      $elements = array();
      foreach ($matches[0] as $tag) {
        $elements[] = new Renderer_Util_Element($tag);
      }
      
      return $elements;
    } else {
      return array();
    }
  }
}
