<?php

function update($anchor, $element, $uri, $method = "get", $parameters = "")
{
  $format = "
    <a onClick=\"
         new Ajax.Updater({success: '%s'}, '%s', {
           method:     '%s',
           parameters: '%s',
           onComplete: function(){}
         })
       \">%s</a>";
  
  return sprintf($format, $element, uri($uri, false), $method, $parameters, $anchor);
}

function remote($anchor, $uri, $method = "get", $parameters = "", $comp)
{
  $format = "
    <a onClick=\"
         new Ajax.Request('%s', {
           method:     '%s',
           parameters: '%s',
           onComplete: %s
         })
       \">%s</a>";
  
  return sprintf($format, uri($uri, false), $method, $parameters, $comp, $anchor);
}

class Sabel_View_Helper_Prototype_Page
{
  protected $jsLines = array();
  
  public function source($js)
  {
    $this->jsLines[] = $js;
  }
  
  function replace($id, $contents)
  {
    $jstemplate = '$("%s").innerHTML = "%s"';
    $this->jsLines[] = sprintf($jstemplate, $id, $contents);
  }
  
  public function insert()
  {
    $this->jsLines[] = "";
  }
  
  public function toJavaScript()
  {
    return join("\n", $this->jsLines);
  }
}
