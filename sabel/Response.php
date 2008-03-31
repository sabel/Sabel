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
  const SUCCESS      = 200;
  const REDIRECTED   = 300;
  const NOT_MODIFIED = 304;
  const BAD_REQUEST  = 400;
  const FORBIDDEN    = 403;
  const NOT_FOUND    = 404;
  const SERVER_ERROR = 500;
  
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
