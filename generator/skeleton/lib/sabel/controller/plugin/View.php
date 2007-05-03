<?php

/**
 * view plugin
 *
 * @category   Controller
 * @package    org.sabel.controller.plugin
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Plugin_View extends Sabel_Controller_Page_Plugin
{
  public function onAfterAction($controller)
  {
    /*
    $view = Sabel_Context::getView();
    $view->assign("request", $controller->getRequest());
    $view->assignByArray(Sabel_Context::getCurrentCandidate()->getElementVariables());
    $view->assignByArray($controller->getRequests());
    $view->assignByArray($controller->getAttributes());
    $result = $controller->getResult();
    if (is_array($result)) $view->assignByArray($result);
    */
  }
}