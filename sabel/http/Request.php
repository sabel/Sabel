<?php

/**
 * Sabel_Http_Request
 *
 * @category   Mail
 * @package    org.sabel.request
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Http_Request extends Sabel_Object
{
  protected $uri = "";
  protected $method = "GET";
  protected $auth = null;
  protected $socket = null;
  
  protected $getValues = array();
  protected $postValues = array();
  
  protected $config = array(
    "maxRedirects" => 5,
    "timeout"      => 10,
    "keepAlive"    => false,
    "httpVersion"  => "1.0",  // @todo 1.0 -> 1.1
  );
  
  protected $headers = array(
    "User-Agent"      => "Sabel/1.1 Sabel_Http_Request",
    "Accept"          => "text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,*/*;q=0.5",
    "Accept-Language" => "en-us,en;q=0.5",
    "Accept-Encoding" => "gzip,deflate",
    "Accept-Charset"  => "ISO-8859-1,utf-8;q=0.7,*;q=0.7",
    "Keep-Alive"      => 300,
    "Connection"      => "close"
  );
  
  public function __construct($uri, $method = "GET")
  {
    $this->uri    = $uri;
    $this->method = $method;
  }
  
  public function setConfig(array $config)
  {
    $this->config = array_merge($this->config, $config);
  }
  
  public function setMethod($method)
  {
    $this->method = $method;
  }
  
  public function setHeader($name, $value)
  {
    $this->headers[$name] = $value;
  }
  
  public function setHeaders(array $headers)
  {
    $this->headers = array_merge($this->headers, $headers);
  }
  
  public function value($key, $value)
  {
    switch ($this->method) {
      case "GET":
        $this->getValues[$key] = $value;
        break;
      case "POST":
        $this->postValues[$key] = $value;
        break;
    }
    
    return $this;
  }
  
  public function file($formName, $name, $contentType = "application/octet-stream", $data = null)
  {
    if ($data === null) {
      if (is_file($name)) {
        $data = file_get_contents($name);
        $name = basename($name);
      } else {
        $message = __METHOD__ . "() File Not Found. ({$name})";
        throw new Sabel_Exception_Runtime($message);
      }
    }
    
    $this->files[$name] = array("formName" => $formName, "contentType" => $contentType, "data" => $data);
  }
  
  public function setAuth($user, $password)
  {
    if ($user === false) {
      $this->auth = null;
    } else {
      $this->auth = array("user" => $user, "password" => $password);
    }
  }
  
  public function connect($host, $port, $transport = "tcp")
  {
    if ($this->socket === null) {
      $host = $transport . "://{$host}";
      $this->socket = fsockopen($host, $port, $errno, $errstr, $this->config["timeout"]);
      
      if (!$this->socket) {
        $message = __METHOD__ . "() {$errno}: {$errstr}";
        throw new Sabel_Exception_Runtime($message);
      }
    }
    
    return $this->socket;
  }
  
  public function disconnect()
  {
    if (is_resource($this->socket)) {
      fclose($this->socket);
      $this->socket = null;
    }
  }
  
  public function request()
  {
    list ($host, $port, $path, $transport) = $this->getRequestInfo($this->uri);
    $response = $this->_request($host, $port, $path, $transport);
    
    if ($this->config["maxRedirects"] > 0 && (int)floor($response->getStatusCode() / 100) === 3) {
      // @todo RFC2616
      
      $this->method = "GET";
      
      for ($i = 0; $i < $this->config["maxRedirects"]; $i++) {
        $location = $response->getHeader("Location");
        if (preg_match("@^https?://@", $location) === 1) {
          list ($host, $port, $path, $transport) = $this->getRequestInfo($location);
        } elseif (strpos($location, "/") === 0) {
          $path = $location;
        } else {
          $exp = explode("/", $path);
          array_pop($exp);
          $exp[] = $location;
          $path = implode("/", $exp);
        }
        
        $response = $this->_request($host, $port, $path, $transport);
        if ((int)floor($response->getStatusCode() / 100) !== 3) break;
      }
    }
    
    return $response;
  }
  
  protected function prepareRequest($host, $path)
  {
    if (!empty($this->getValues)) {
      $path .= "?" . http_build_query($this->getValues, "", "&");
    }
    
    $httpVer   = $this->config["httpVersion"];
    $request   = array();
    $request[] = strtoupper($this->method) . " {$path} HTTP/{$httpVer}";
    $request[] = "Host: $host";
    
    if ($this->config["keepAlive"]) {
      $this->headers["Connection"] = "keep-alive";
    } else {
      unset($this->headers["Keep-Alive"]);
    }
    
    $body = $this->buildBody();
    
    foreach ($this->headers as $key => $value) {
      $request[] = $key . ": " . $value;
    }
    
    if ($this->auth !== null) {
      $auth = $this->auth["user"] . ":" . $this->auth["password"];
      $request[] = "Authorization: Basic " . base64_encode($auth);
    }
    
    $request = implode("\r\n", $request) . "\r\n\r\n";
    if ($body !== "") $request .= $body;
    
    return $request;
  }
  
  protected function buildBody()
  {
    $body = "";
    
    if ($this->method !== "POST") {
      return $body;
    }
    
    if (!empty($this->files) && !empty($this->postValues)) {
      $body = array();
      $boundary = md5hash();
      
      foreach ($this->postValues as $name => $value) {
        $body[] = "--" . $boundary;
        $body[] = "Content-Disposition: form-data; name=\"{$name}\"\r\n";
        $body[] = $value;
      }
      
      foreach ($this->files as $name => $file) {
        $body[] = "--" . $boundary;
        $body[] = "Content-Disposition: form-data; name=\"{$file["formName"]}\"; filename=\"{$name}\"";
        $body[] = "Content-Type: " . $file["contentType"] . "\r\n";
        $body[] = $file["data"];
      }
      
      $body[] = "--{$boundary}--";
      $body = implode("\r\n", $body);
      $this->headers["Content-Type"] = "multipart/form-data; boundary=\"{$boundary}\"";
      $this->headers["Content-Length"] = strlen($body);
    } elseif (!empty($this->postValues)) {
      $body = http_build_query($this->postValues, "", "&");
      $this->headers["Content-Type"] = "application/x-www-form-urlencoded";
      $this->headers["Content-Length"] = strlen($body);
    }
    
    return $body;
  }
  
  protected function _request($host, $port, $path, $transport = "tcp")
  {
    $request = $this->prepareRequest($host, $path);
    $socket  = $this->connect($host, $port, $transport);
    
    if (fwrite($socket, $request) === false) {
      $message = __METHOD__ . "() request failed.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    $responseText = array();
    
    while (true) {
      if (($content = fread($socket, 8192)) === "") break;
      $responseText[] = $content;
    }
    
    $response = new Sabel_Http_Response(implode("", $responseText));
    if ($response->getHeader("Connection") === "close") {
      $this->disconnect();
    }
    
    return $response;
  }
  
  protected function getRequestInfo($uri)
  {
    $transport = "tcp";
    $parsed    = parse_url($uri);
    $host      = $parsed["host"];
    $path      = $parsed["path"];
    
    if (isset($parsed["query"])) {
      $path .= "?" . $parsed["query"];
    }
    
    if ($parsed["scheme"] === "http") {
      $port = (isset($parsed["port"])) ? $parsed["port"] : "80";
    } elseif ($parsed["scheme"] === "https") {
      $transport = "ssl";
      $port = (isset($parsed["port"])) ? $parsed["port"] : "443";
    }
    
    return array($host, $port, $path, $transport);
  }
}
