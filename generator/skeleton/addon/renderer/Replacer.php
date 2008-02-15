<?php

/**
 * Renderer_Replacer
 *
 * @category   Addon
 * @package    addon.renderer
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Ebine Yutaka <ebine.yutaka@sabel.jp>
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
  
  protected function simpleReplace($contents)
  {
    $search  = array("</if>", "<else/>", "<else />", "</foreach>");
    $replace = array("<? endif ?>", "<? else : ?>", "<? else : ?>", "<? endforeach ?>");
    
    return str_replace($search, $replace, $contents);
  }
  
  protected function if_replace($element, $if = "if")
  {
    if (($equal = $element->equals) !== null) {
      $params = array_map("trim", explode(",", $equal));
      if (count($params) < 2) {
        throw new Sabel_Exception_Runtime("too few parameters.");
      }
      
      $fmt = "<? $if (%s === %s) : ?>";
      return sprintf($fmt, $params[0], $params[1]);
    } elseif (($expr = $element->expr) !== null) {
      return "<? $if ({$expr}) : ?>";
    } elseif (($empty = $element->isset) !== null) {
      return "<? $if (isset({$empty})) : ?>";
    } elseif (($empty = $element->empty) !== null) {
      return "<? $if (realempty({$empty})) : ?>";
    } elseif (($nemp = $element->notempty) !== null) {
      return "<? $if (!realempty({$nemp})) : ?>";
    } elseif (($attrs = $element->getAttributes()) !== null) {
      list ($func, $arg) = each($attrs);
      return "<? $if ({$func}({$arg})) : ?>";
    }
    
    throw new Sabel_Exception_Runtime("if parameter is empty.");
  }
  
  protected function elseif_replace($element)
  {
    return $this->if_replace($element, "elseif");
  }
  
  protected function elif_replace($element)
  {
    return $this->elseif_replace($element);
  }
  
  protected function foreach_replace($element)
  {
    $params = array();
    
    if (($from = $element->from) !== null) {
      $params["from"] = $from;
      if ($element->key !== null) $params["key"] = $element->key;
      $params["value"] = $element->value;
    } else {
      $args = $element->args;
      if ($args === null) $args = $element->params;
      
      if ($args === null) {
        $message = "foreach parameter is null.";
        throw new Sabel_Exception_InvalidArgument($message);
      } else {
        $exp  = array_map("trim", explode(",", $args));
        $argc = count($exp);
        if ($argc === 2) {
          $params["from"]  = $exp[0];
          $params["value"] = $exp[1];
        } elseif ($argc === 3) {
          $params["from"]  = $exp[0];
          $params["key"]   = $exp[1];
          $params["value"] = $exp[2];
        } else {
          $message = "wrong parameter count for foreach.";
          throw new Sabel_Exception_InvalidArgument($message);
        }
      }
    }
    
    $argc = count($params);
    
    if ($argc === 2) {
      $fmt = "<? foreach (%s as %s) : ?>";
      return sprintf($fmt, $params["from"], $params["value"]);
    } else {
      $fmt = "<? foreach (%s as %s => %s) : ?>";
      return sprintf($fmt, $params["from"], $params["key"], $params["value"]);
    }
  }
  
  protected function partial_replace($element)
  {
    if (($name = $element->name) === null) {
      throw new Sabel_Exception_Runtime("template name is null.");
    }
    
    if (($assign = $element->assign) === null) {
      $fmt = '<?= $this->partial("%s") ?>';
      return sprintf($fmt, $name);
    } else {
      $fmt = '<?= $this->partial("%s", %s) ?>';
      
      $assigns = explode(",", $assign);
      if (strpos($assigns[0], ":") === false) {
        return sprintf($fmt, $name, $assigns[0]);
      } else {
        $buf = "";
        foreach ($assigns as $hash) {
          list ($key, $val) = explode(":", $hash);
          $buf[] = "'" . trim($key) . "' => {$val}";
        }
        
        $assign = "array(" . implode(", ", $buf) . ")";
        return sprintf($fmt, $name, $assign);
      }
    }
  }
  
  protected function formerr_replace($element)
  {
    if (($form = $element->form) === null) {
      throw new Sabel_Exception_Runtime("form name is null.");
    }
    
    if ($form{0} !== '$') $form = '$' . $form . "Form";
    
    $fmt = '<? if (%1$s->hasError()) : ?>' . PHP_EOL
         . '  <?= $this->partial("error", array("errors" => %1$s->getErrors())) ?>' . PHP_EOL
         . '<? endif ?>';
         
    return sprintf($fmt, $form);
  }
  
  protected function echo_replace($element)
  {
    if (($arg = $element->arg) === null) {
      throw new Sabel_Exception_Runtime("empty argument.");
    }
    
    return "<?= $arg ?>";
  }
  
  protected function e_replace($element)
  {
    return $this->echo_replace($element);
  }
}
