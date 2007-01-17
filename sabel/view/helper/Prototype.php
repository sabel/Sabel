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