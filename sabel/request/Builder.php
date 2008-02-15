<?php

/**
 * Default Request Builder
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Builder extends Sabel_Request_AbstractBuilder
{
  protected function setUri(Sabel_Request $request, $uri)
  {
    $request->to($uri);
  }
  
  protected function setGetValues(Sabel_Request $request)
  {
    $request->setGetValues($_GET);
  }
  
  protected function setPostValues(Sabel_Request $request)
  {
    $request->setPostValues($_POST);
  }
}
