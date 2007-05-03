<?php

/**
 * partial action executer plugin
 *
 * @category   Plugin
 * @package    org.sabel.controller.plugin
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Plugin_Partial extends Sabel_Controller_Page_Plugin
{
  /**
   * partial plugin method.
   * this method to execute partial action of same context
   *
   * @param string $action
   * @param array $assigns
   * @return string result of render template
   */
  public function partial($action, $assigns = null)
  {
    $controller = $this->controllerInstance;
    
    if ($action !== null) {
      $result = $controller->execute($action);
      
      if ($assigns) {
        $assign = array("assign" => array_merge($assigns, $controller->getAssignments()));
      } else {
        $assign = array("assign" => $controller->getAssignments());
      }
      
      return Sabel_View::render($action, $assign);
    }
  }
}
