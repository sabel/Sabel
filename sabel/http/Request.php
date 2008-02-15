<?php

/**
 * HTTP Request
 *
 * @category   Http
 * @package    org.sabel.http
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Http_Request extends Sabel_Object
{
  protected $requestHeader  = null;
  protected $responseHeader = null;
  
  protected $requester = null;
  protected $userAgent = '';
  
  public function __construct($userAgent = 'sabel', $requester = null)
  {
    $this->userAgent = $userAgent;
    if ($requester === null) {
      $this->requester = new Sabel_Http_Requester_Stream();
    } else if ($requester instanceof Sabel_Http_Requestable){
      $this->requester = $requester;
    }
  }
  
  /**
   * do request
   *
   */
  public function request($host, $path, $param = '', $method = 'post', $port = 80)
  {
    if (is_array($param)) {
      $request = array();
      foreach ($param as $key => $val) {
        if (is_array($val)) {
          foreach ($val as $k => $v) $request[] = "{$key}[{$k}]=" . urlencode($v);
        } else {
          $request[] = $key . "=" . urlencode($val);
        }
      }
      $request = join('&', $request);
    } else {
      $request = $param;
    }
    $request_length = strlen($request);
    
    $headers = array();
    
    switch (strtolower($method)) {
      case 'post':
        $headers[] = "POST {$path} HTTP/1.0";
        $headers[] = "Content-length: {$request_length}";
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        break;
      case 'get':
        $headers[] = "GET {$path}?{$request} HTTP/1.0";
        $headers[] = "Content-length: {$request_length}";
        break;
      case 'put':
        $headers[] = "PUT {$path} HTTP/1.0";
        $headers[] = "Content-length: {$request_length}";
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        break;
      case 'delete':
        $headers[] = "DELETE {$path}?{$request} HTTP/1.0";
        $headers[] = "Contents-length: {$request_length}";
        break;
      default:
        $headers[] = "{$method} {$path} HTTP/1.0";
        break;
    }
    
    $headers[] = "Host: $host";
    $headers[] = "Connection: close";
    
    $headers[] = "X-Path: {$path}";
    if($this->userAgent) $headers[] = "User-Agent: {$this->userAgent}";
    
    $this->requester->connect($host, $port);
    $data = join("\r\n", $headers) . "\r\n\r\n" . $request . "\r\n";
    $result = $this->requester->send($data);
    
    $response = new Sabel_Http_Response();
    $responseHeader = new Sabel_Http_Header();
    $response->setHeader($responseHeader);
    
    $this->responseHeader = $responseHeader;
    $this->requestHeader  = new Sabel_Http_Header($headers);
    
    foreach ($result["header"] as $header) $responseHeader->add($header);
    $response->setContents($result["contents"]);
    return $response;
  }
  
  public function getRequestHeader()
  {
    return $this->requestHeader;
  }
  
  public function getResponseHeader()
  {
    return $this->responseHeader;
  }
}
