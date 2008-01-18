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
abstract class Sabel_View_Renderer extends Sabel_Object
{
  // @todo
  protected $trim = true;
  
  protected $preprocessor = null;
  
  abstract public function rendering($_tpl_string, $_tpl_values, $_tpl_path = null);
  
  public function setPreprocessor(Sabel_View_Preprocessor_Interface $p)
  {
    $this->preprocessor = $p;
  }
  
  public function preprocess($contents)
  {
    if (is_object($this->preprocessor)) {
      return $this->preprocessor->execute($contents);
    } else {
      return $contents;
    }
  }
  
  public function partial($name, $assign = array())
  {
    $bus = Sabel_Context::getContext()->getBus();
    
    $repository = $bus->get("repository");
    $renderer = $bus->get("renderer");
    
    if (is_object($renderer)) {
      $resource  = $repository->find($name);
      $responses = $bus->get("response")->getResponses();
      return $renderer->rendering($resource->fetch(), array_merge($responses, $assign));
    } else {
      throw new Sabel_Exception_Runtime("renderer object is not found.");
    }
  }
  
  protected function createHash($template)
  {
    return md5($template);
  }
}
