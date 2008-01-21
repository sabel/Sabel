<?php

/**
 * Default Request Builder
 *
 * @category   Request
 * @package    org.sabel.request
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
  
  protected function setGetValues($request)
  {
    $request->setGetValues($_GET);
  }
  
  protected function setPostValues($request)
  {
    $request->setPostValues($_POST);
  }
  
  protected function setHeaders($request)
  {
    $headers = array();
    foreach ($_SERVER as $key => $value) {
      if (strpos($key, "HTTP_") !== false) {
        $exp = explode("_", substr($key, 5));
        if (count($exp) === 1) {
          $headers[ucfirst(strtolower($exp[0]))] = $value;
        } else {
          $name = array();
          foreach ($exp as $part) {
            $name[] = ucfirst(strtolower($part));
          }
          $headers[implode("-", $name)] = $value;
        }
      }
    }
    
    $request->setHeaders($headers);
  }
}
