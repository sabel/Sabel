<?php

/**
 * Sabel_View_Repository
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Repository extends Sabel_Object
{
  protected
    $template = null,
    $tplName  = null;
    
  public function __construct(Sabel_View_Template $template)
  {
    $this->template = $template;
  }
  
  public function setTemplateName($tplName)
  {
    $this->tplName = $tplName;
  }
  
  public function getTemplateName()
  {
    return $this->tplName;
  }
  
  public function find($tplPath = null)
  {
    if ($tplPath === null && $this->tplName === null) {
      throw new Sabel_Exception_Runtime("template name is null.");
    } elseif ($tplPath === null) {
      return $this->template->find($this->tplName);
    } else {
      return $this->template->find($tplPath);
    }
  }
  
  public function create($locationName, $tplPath, $body = "")
  {
    return $this->template->create($locationName, $tplPath, $body);
  }
  
  public function delete($locationName, $tplPath)
  {
    return $this->template->delete($locationName, $tplPath);
  }
  
  public function isValid($locationName, $tplPath)
  {
    return $this->template->isValid($locationName, $tplPath);
  }
}
