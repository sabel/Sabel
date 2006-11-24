<?php

class Sabel_Request_Remote
{
  public static function remoteRequest($host, $path, $param = '', $method = 'post', $port = 80, $ua = 'sabel')
  {
    if (is_array($param)) {
      $request = array();
      foreach ($param as $key => $val) $request[] = $key . "=" . urlencode($val);
      $request = join('&', $request);
    } else {
      $request = $param;
    }
    $request_length = strlen($request);
    
    switch (strtolower($method)) {
      case 'post':
        $header  = "POST $path HTTP/1.0\r\n";
        $header .= "Host: $host\r\n";
        if($ua) $header .= "User-Agent: $ua\r\n";
        $header .= "Content-type:  application/x-www-form-urlencoded\r\n";
        $header .= "Content-length: $request_length\r\n";
        $header .= "\r\n";
        break;
      case 'get':
        $header  = "GET $path?$request HTTP/1.1\r\n";
        $header .= "Host: $host\r\n";
        if ($ua) $header .= "User-Agent: $ua\r\n";
        $header .= "Content-type:  application/x-www-form-urlencoded\r\n";
        $header .= "Content-length: $request_length\r\n";
        $header .= "\r\n";
        break;
      default:
        $header  = "$method $path HTTP/1.0\r\n";
        $header .= "Host: $host\r\n";
        if ($ua) $header .= "User-Agent: $ua\r\n";
        break;
    }
    
    $sock = stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 1, STREAM_CLIENT_CONNECT);
    stream_set_blocking($sock, 1);
    
    if (!$sock) { 
      throw new Sabel_Exception_Runtime("can't connect to remote server.");
    }
    
    stream_socket_sendto($sock, $header.$request);
    $response = '';
    
    $headerFlag = true;
    while (!feof($sock)) {
      $line = stream_get_line($sock, 8192, "\n");
      if (!$headerFlag) $response = $line;
      
      if ($headerFlag && trim($line) === "") {
        $headerFlag = false;
      } else {
        continue;
      }
    }
    
    return $response;
  }
}