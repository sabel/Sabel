<?php

/**
 * Form_Element_Form
 *
 * @category  Addon
 * @package   addon.form
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Form_Element_Form extends Form_Element
{
  public function toHtml($options = array())
  {
    if (isset($options["type"])) {
      $type = $options["type"];
    } else {
      $type = "open";
    }
    
    switch ($type) {
      case "open":
        return $this->open($options);
        
      case "close":
        return $this->close();
        
      case "submit":
        return $this->submit($options);
    }
  }
  
  private function open($options)
  {
    $uri    = (isset($options["uri"]))    ? $options["uri"]    : "";
    $method = (isset($options["method"])) ? $options["method"] : "post";
    
    $html = '<form action="' . uri($uri) . '" method="' . $method . '" ';
    $this->addIdAndClass($html);
    if ($this->name !== "") $html .= 'name="' . $this->name . '" ';
    
    return $html . ">\n<fieldset class=\"formField\">\n";
  }
  
  private function close()
  {
    return "</fieldset>\n</form>\n";
  }
  
  private function submit($options)
  {
    $text = (isset($options["text"])) ? $options["text"] : "submit";
    
    $html = '<input type="submit" ';
    $this->addIdAndClass($html);
    $html .= 'value="' . $text . '" />';
    
    return $html;
  }
}
