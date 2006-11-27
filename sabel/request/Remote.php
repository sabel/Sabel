<?php

class Sabel_Request_Remote
{
  protected $requestHeaders  = array();
  protected $responseHeaders = array();
  
  public function remoteRequest($host, $path, $param = '', $method = 'post', $port = 80, $ua = 'sabel')
  {
    if (is_array($param)) {
      $request = array();
      foreach ($param as $key => $val) $request[] = $key . "=" . urlencode($val);
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
        $headers[] = "POST {$path} HTTP/1.0";
        $headers[] = "Contents-length: {$request_length}";
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
    if($ua) $headers[] = "User-Agent: $ua";
    
    $sock = stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 1, STREAM_CLIENT_CONNECT);
    stream_set_timeout($sock, 15);
    stream_set_blocking($sock, 1);
    
    if (!$sock) throw new Sabel_Exception_Runtime("can't connect to remote server.");
    
    stream_socket_sendto($sock, join("\r\n", $headers) . "\r\n\r\n" . $request . "\r\n");
    
    $this->requestHeaders = $headers;
    
    $response = new Sabel_Http_Response();
    $responseHeader = new Sabel_Http_Header();
    
    $headerFlag = true;
    while (!feof($sock)) {
      $line = stream_get_line($sock, 8192, "\n");
      if (!$headerFlag) {
        $response->setContents($line);
      } else {
        $responseHeader->add($line);
      }
      
      if ($headerFlag && trim($line) === "") {
        $headerFlag = false;
      } else {
        continue;
      }
    }
    
    $response->setHeader($responseHeader);
    return $response;
  }
  
  public function getRequestHeaders()
  {
    return $this->requestHeaders;
  }
  
  public function getResponseHeaders()
  {
    return $this->responseHeaders;
  }
}