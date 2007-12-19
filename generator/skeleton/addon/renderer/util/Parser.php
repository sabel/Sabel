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
    $elems    = array();
    $char     = null;
    $chars    = array();
    $prevChar = null;
    $inAttr   = false;
    $ignore   = false;
    
    for ($i = 0, $length = mb_strlen($template); $i < $length; $i++) {
      $prevChar = $char;
      $chars[]  = $char = mb_substr($template, $i, 1);
      $nextChar = mb_substr($template, $i + 1, 1);
      
      if ($char === '"') {
        $inAttr = !$inAttr;
      } elseif ($char === "<" && ($nextChar === "?" || $nextChar === "!")) {
        $ignore = true;
      } elseif (!$inAttr && $char === ">") {
        if ($ignore && $prevChar === "?" || $prevChar === "-") {
          $ignore = false;
        } else {
          $tagName = trim(implode("", $chars));
          if (!$this->isClose($tagName)) {
            $elems[] = new Renderer_Util_Element($tagName);
          }
        }
        
        $chars = array();
      }
    }
    
    return $elems;
  }
  
  private function isClose($tagName)
  {
    return ((isset($tagName{0}) && $tagName{0} !== "<") ||
            (mb_substr($tagName, 0, 1) === "<" &&
             mb_substr($tagName, 1, 1) === "/"));
  }
}
