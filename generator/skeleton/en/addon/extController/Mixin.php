<?php

/**
 * ExtController_Mixin
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class ExtController_Mixin extends Sabel_Object
{
  /**
   * @var Sabel_Controller_Page
   */
  protected $controller = null;
  
  public function __construct(Sabel_Controller_Page $controller)
  {
    $this->controller = $controller;
  }
  
  public function badRequest()
  {
    $this->getResponseStatus()->setCode(Sabel_Response::BAD_REQUEST);
  }
  
  public function notFound()
  {
    $this->getResponseStatus()->setCode(Sabel_Response::NOT_FOUND);
  }
  
  public function forbidden()
  {
    $this->getResponseStatus()->setCode(Sabel_Response::FORBIDDEN);
  }
  
  public function serverError()
  {
    $this->getResponseStatus()->setCode(Sabel_Response::INTERNAL_SERVER_ERROR);
  }
  
  protected function getResponseStatus()
  {
    return $this->controller->getResponse()->getStatus();
  }
}
