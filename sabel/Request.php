<?php

/**
 * Sabel_Request
 *
 * @interface
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Request
{
  const GET    = "GET";
  const POST   = "POST";
  const PUT    = "PUT";
  const DELETE = "DELETE";
  
  public function setUri($uri);
  public function getUri();
  
  public function get($uri);
  public function post($uri);
  public function put($uri);
  public function delete($uri);
  
  public function isGet();
  public function isPost();
  public function isPut();
  public function isDelete();
  
  public function setGetValues(array $values);
  public function setPostValues(array $values);
  public function setHttpHeaders(array $headers);
  
  public function setGetValue($name, $value);
  public function setPostValue($name, $value);
  public function setParameterValue($name, $value);
  
  public function fetchGetValue($name);
  public function fetchPostValue($name);
  public function fetchParameterValue($name);
  
  public function getHttpHeader($name);
  public function getHttpHeaders();
}
