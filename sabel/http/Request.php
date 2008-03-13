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
  protected $responseHeader = null;
  
  protected $requester = null;
  protected $userAgent = "";
  
  public function __construct($userAgent = "sabel", $requester = null)
  {
    $this->userAgent = $userAgent;
    
    if ($requester === null) {
      $this->requester = new Sabel_Http_Requester_Stream();
    } elseif ($requester instanceof Sabel_Http_Requester_Interface){
      $this->requester = $requester;
    }
  }
  
  /**
   * do request
   *
   */
  public function request($host, $path, $param = array(), $method = "post", $port = 80)
  {
    if (is_array($param)) {
      $request = array();
      foreach ($param as $k => $v) $request[] = $k . "=" . urlencode($v);
      $request = implode("&", $request);
    } else {
      $request = $param;
    }
    
    $headers       = array();
    $contentLength = strlen($request);
    $httpVersion   = "1.0";
    
    switch (strtolower($method)) {
      case "post":
        $headers[] = "POST {$path} HTTP/{$httpVersion}";
        $headers[] = "Host: $host";
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        $headers[] = "Content-length: {$contentLength}";
        if ($request !== "") $headers[] = "\r\n" . $request;
        break;
      case "get":
        $headers[] = "GET {$path}?{$request} HTTP/{$httpVersion}";
        $headers[] = "Host: $host";
        $headers[] = "Content-length: {$contentLength}";
        break;
      case "put":
        $headers[] = "PUT {$path} HTTP/{$httpVersion}";
        $headers[] = "Host: $host";
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        $headers[] = "Content-length: {$contentLength}";
        if ($request !== "") $headers[] = "\r\n" . $request;
        break;
      case "delete":
        $headers[] = "DELETE {$path}?{$request} HTTP/{$httpVersion}";
        $headers[] = "Host: $host";
        $headers[] = "Contents-length: {$contentLength}";
        break;
      default:
        $headers[] = "{$method} {$path} HTTP/{$httpVersion}";
        break;
    }
    
    //$headers[] = "Connection: close";
    $headers[] = "X-Path: {$path}";
    
    if ($this->userAgent !== "") {
      $headers[] = "User-Agent: {$this->userAgent}";
    }
    
    $this->requester->connect($host, $port);
    $data = join("\r\n", $headers) . "\r\n";
    $result = $this->requester->send($data);
    
    $responseHeader = new Sabel_Http_Header();
    foreach ($result["header"] as $header) {
      $responseHeader->add($header);
    }
    
    $this->responseHeader = $responseHeader;
    
    $response = new Sabel_Http_Response();
    $response->setHeader($responseHeader);
    $response->setContents($result["contents"]);
    
    return $response;
  }
}
