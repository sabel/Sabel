<?php

/**
 * Default Request Builder
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Builder extends Sabel_Request_AbstractBuilder
{
  protected function setUri($request, $uri)
  {
    $request->to($uri);
  }
  
  protected function setParameters($request, $parameters)
  {
    $request->parameter($parameters);
  }
  
  protected function setGetValues($request)
  {
    $request->setGetValues($_GET);
  }
  
  protected function setPostValues($request)
  {
    $request->setPostValues($_POST);
  }
  
  protected function setParameterValues($request)
  {
  }
}
