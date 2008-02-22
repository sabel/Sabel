<?php

/**
 * Processor_Request
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Request extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    if (!$bus->has("request")) {
      $bus->set("request", $this->createRequestObject());
    }
  }
  
  protected function createRequestObject()
  {
    $request = new Sabel_Request_Object();
    
    $uri = Sabel_Environment::get("REQUEST_URI");
    $uri = trim(preg_replace("/\/{2,}/", "/", $uri), "/");
    $parsedUrl = parse_url("http://localhost/{$uri}");
    
    if (isset($parsedUrl["path"])) {
      $request->setUri(ltrim($parsedUrl["path"], "/"));
    }
    
    $request->setGetValues($_GET);
    $request->setPostValues($_POST);
    $request->method(Sabel_Environment::get("REQUEST_METHOD"));
    
    return $request;
  }
}
