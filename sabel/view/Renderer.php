<?php

/**
 * Sabel_View_Renderer
 *
 * @category   Template
 * @package    org.sabel.template
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Renderer extends Sabel_Object
{
  // @todo
  protected $trim = true;
  
  protected $preprocessor = null;
  
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
  
  public function rendering($_tpl_contents, $_tpl_values, $_tpl_path = null)
  {
    if ($_tpl_path === null) {
      $hash = $this->createHash($_tpl_contents);
      $_tpl_path = COMPILE_DIR_PATH . DS . $hash;
      file_put_contents($_tpl_path, $_tpl_contents);
    }

    extract($_tpl_values, EXTR_OVERWRITE);
    ob_start();
    include ($_tpl_path);
    return ob_get_clean();
  }
  
  public function partial($name, $assign = array())
  {
    $bus = Sabel_Context::getContext()->getBus();
    $repository = $bus->get("repository");
    
    if (($template = $repository->getValidTemplate($name)) !== null) {
      $responses = array_merge($bus->get("response")->getResponses(), $assign);
      $contents  = $template->getContents();
      return $this->rendering($contents, $responses, $template->getPath());
    } else {
      throw new Sabel_Exception_Runtime("template is not found.");
    }
  }
  
  protected function createHash($template)
  {
    return md5($template);
  }
}
