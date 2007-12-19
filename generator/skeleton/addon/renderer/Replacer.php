<?php

/**
 * Renderer_Replacer
 *
 * @category   Addon
 * @package    addon.renderer
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Renderer_Replacer extends Sabel_Object
  implements Sabel_View_Preprocessor_Interface
{
  public function execute($contents)
  {
    $parser = new Renderer_Util_Parser();
    $elements = $parser->getElements($contents);
    if (empty($elements)) return $contents;
    
    $contents = $this->simpleReplace($contents);
    
    foreach ($elements as $element) {
      $method = $element->name() . "_replace";
      if ($this->hasMethod($method)) {
        $tag = $element->tag();
        $rep = $this->$method($element);
        $contents = str_replace($tag, $rep, $contents);
      }
    }
    
    return $contents;
  }
  
  public function simpleReplace($contents)
  {
    $search  = array("</if>", "<else/>", "<else />", "</foreach>");
    $replace = array("<? endif ?>", "<? else : ?>", "<? else : ?>", "<? endforeach ?>");
    
    return str_replace($search, $replace, $contents);
  }
  
  public function if_replace($element, $if = "if")
  {
    if (($equal = $element->equal) !== null) {
      $params = array_map("trim", explode(",", $equal));
      if (count($params) < 2) {
        throw new Sabel_Exception_Runtime("too few parameters.");
      }
      
      $fmt = "<? {$if} (%s === %s) : ?>";
      return sprintf($fmt, $params[0], $params[1]);
    } elseif (($expr = $element->expr) !== null) {
      return "<? {$if} ({$expr}) : ?>";
    } elseif (($nemp = $element->notempty) !== null) {
      return "<? {$if} (!realempty({$nemp})) : ?>";
    } elseif (($empty = $element->empty) !== null) {
      return "<? {$if} (realempty({$empty})) : ?>";
    } elseif (($attrs = $element->getAttributes()) !== null) {
      list ($func, $arg) = each($attrs);
      return "<? {$if} ({$func}({$arg})) : ?>";
    }
    
    throw new Sabel_Exception_Runtime("if parameter is empty.");
  }
  
  public function elseif_replace($element)
  {
    return $this->if_replace($element, "elseif");
  }
  
  public function elif_replace($element)
  {
    return $this->elseif_replace($element);
  }
  
  public function foreach_replace($element)
  {
    if (($args = $element->args) === null) {
      throw new Sabel_Exception_Runtime("foreach parameter is empty.");
    }
    
    $params = explode(",", $args);
    $argc = count($params);
    
    if ($argc < 2) {
      throw new Sabel_Exception_Runtime("too few parameters.");
    } elseif ($argc === 2) {
      $fmt = "<? foreach (%s as %s) : ?>";
      return sprintf($fmt, $params[0], $params[1]);
    } else {
      $fmt = "<? foreach (%s as %s => %s) : ?>";
      return sprintf($fmt, $params[0], $params[1], $params[2]);
    }
  }
  
  public function partial_replace($element)
  {
    if (($name = $element->name) === null) {
      throw new Sabel_Exception_Runtime("template name is null.");
    }
    
    if (($controller = $element->controller) === null) {
      $controller = "null";
    } else {
      $controller = '"' . $controller . '"';
    }
    
    if (($assign = $element->assign) === null) {
      $fmt = '<?= $this->partial("%s", %s) ?>';
      return sprintf($fmt, $name, $controller);
    } else {
      $fmt = '<?= $this->partial("%s", %s, %s) ?>';
      
      $assigns = explode(",", $assign);
      if (strpos($assigns[0], ":") === false) {
        return sprintf($fmt, $name, $controller, $assigns[0]);
      } else {
        $buf = "";
        foreach ($assigns as $hash) {
          list ($key, $val) = explode(":", $hash);
          $buf[] = "'{$key}' => {$val}";
        }
        $assign = "array(" . implode(", ", $buf) . ")";
        return sprintf($fmt, $name, $controller, $assign);
      }
    }
  }
  
  public function formerr_replace($element)
  {
    if (($form = $element->form) === null) {
      throw new Sabel_Exception_Runtime("form name is null.");
    }
    
    if ($form{0} !== '$') $form = '$' . $form . "Form";
    
    $fmt = '<? if (%1$s->hasError()) : ?>' . PHP_EOL
         . '  <?= $this->partial("error", null, array("errors" => %1$s->getErrors())) ?>' . PHP_EOL
         . '<? endif ?>';
         
    return sprintf($fmt, $form);
  }
}
