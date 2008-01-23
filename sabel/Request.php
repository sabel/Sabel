<?php

/**
 * Sabel_Request
 *
 * @interface
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Request
{
  const GET    = "GET";
  const POST   = "POST";
  const PUT    = "PUT";
  const DELETE = "DELETE";
  
  public function getUri();
  
  public function get($uri);
  public function post($uri);
  public function put($uri);
  public function delete($uri);
  
  public function isGet();
  public function isPost();
  public function isPut();
  public function isDelete();
  
  public function setGetValue($name, $value);
  public function fetchGetValue($name);
  
  public function setPostValue($name, $value);
  public function fetchPostValue($name);
  
  public function setParameterValue($name, $value);
  public function fetchParameterValue($name);
}
