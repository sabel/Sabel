<?php

/**
 * Sabel_Response
 *
 * @interface
 * @category   Response
 * @package    org.sabel.response
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Response
{
  public function getStatus();
  
  public function success();
  public function isSuccess();
  public function badRequest();
  public function isBadRequest();
  public function forbidden();
  public function isForbidden();
  public function notFound();
  public function isNotFound();
  public function serverError();
  public function isServerError();
  
  public function isFailure();
  public function isRedirected();
  
  public function getLocation();
  public function getLocationUri();
  public function location($host, $to);
  
  public function setResponse($name, $value);
  public function getResponse($name);
  public function setResponses(array $responses);
  public function getResponses();
  
  public function setHeader($message, $value);
  public function getHeaders();
  public function hasHeaders();
  
  public function outputHeader();
}
