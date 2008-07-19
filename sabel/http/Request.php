<?php

/**
 * Sabel_Http_Request
 *
 * @category   Mail
 * @package    org.sabel.http
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
  
  protected $cookies = array();
  
  protected $getValues = array();
  protected $postValues = array();
  
  protected $config = array(
    "maxRedirects" => 5,
    "timeout"      => 10,
    "useCookie"    => true,
    "useProxy"     => false,
    "keepAlive"    => false,
    "httpVersion"  => "1.0",  // @todo 1.0 -> 1.1
  );
  
  protected $proxyConfig = array(
    "host"      => "",
    "port"      => 8080,
    "transport" => "tcp",
    "user"      => "",
    "password"  => ""
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
  
  public function setUri($uri)
  {
    $this->uri = $uri;
    
    return $this;
  }
  
  public function setMethod($method)
  {
    $this->method = $method;
    
    return $this;
  }
  
  public function setConfig(array $config)
  {
    $this->config = array_merge($this->config, $config);
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
    
    $this->files[] = array("name"        => $name,
                           "formName"    => $formName,
                           "contentType" => $contentType,
                           "data"        => $data);
  }
  
  public function cookie($key, $value, $expire = null, $path = "/", $domain = null, $secure = false)
  {
    $cookie = array("name" => $key, "value" => $value, "path" => $path, "secure" => $secure);
    if ($expire !== null) $cookie["expires"] = $expire;
    
    if ($domain === null) {
      $parsed = parse_url($this->uri);
      $cookie["domain"] = $parsed["host"];
    } else {
      $cookie["domain"] = $domain;
    }
    
    $this->cookies[] = $cookie;
    
    return $this;
  }
  
  public function deleteCookie($name, $path = "/", $domain = null)
  {
    if ($domain === null) {
      $parsed = parse_url($this->uri);
      $domain = $parsed["host"];
    }
    
    foreach ($this->cookies as $i => $cookie) {
      if ($cookie["name"]   === $name   &&
          $cookie["domain"] === $domain &&
          $cookie["path"]   === $path) {
        unset($this->cookies[$i]);
      }
    }
  }
  
  public function setAuth($user, $password)
  {
    if ($user === false) {
      $this->auth = null;
    } else {
      $this->auth = array("user" => $user, "password" => $password);
    }
  }
  
  public function setProxy($config)
  {
    if ($config === false) {
      $this->proxyConfig = array("host"      => "",
                                 "port"      => 8080,
                                 "transport" => "tcp",
                                 "user"      => "",
                                 "password"  => "");
      
      $this->config["useProxy"] = false;
    } elseif (is_array($config)) {
      $this->proxyConfig = array_merge($this->proxyConfig, $config);
      $this->config["useProxy"] = true;
    } else {
      $message = __METHOD__ . "() argument must be an array or false.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function connect(Sabel_Http_Uri $uri)
  {
    if (!$this->socket) {
      if ($this->config["useProxy"]) {
        $conf = $this->proxyConfig;
        $host = $conf["transport"] . "://" . $conf["host"];
        $this->socket = fsockopen($host, $conf["port"], $errno, $errstr, $this->config["timeout"]);
      } else {
        $host = $uri->transport . "://" . $uri->host;
        $this->socket = fsockopen($host, $uri->port, $errno, $errstr, $this->config["timeout"]);
      }
      
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
    $uri = new Sabel_Http_Uri($this->uri);
    $response = $this->_request($uri);
    
    if ($this->config["maxRedirects"] > 0 && (int)floor($response->getStatusCode() / 100) === 3) {
      // @todo RFC2616
      
      $this->method = "GET";
      
      for ($i = 0; $i < $this->config["maxRedirects"]; $i++) {
        $location = $response->getHeader("Location");
        if (preg_match("@^(https?|ftp)://@", $location) === 1) {
          $parsed = parse_url($location);
          if ($uri->host !== $parsed["host"] || $uri->scheme !== $parsed["scheme"]) {
            $this->disconnect();
          }
          
          $uri = new Sabel_Http_Uri($location);
        } elseif (strpos($location, "/") === 0) {
          $uri->setPath($location);
        } else {
          $exp = explode("/", $uri->path);
          array_pop($exp);
          $exp[] = $location;
          $uri->setPath(implode("/", $exp));
        }
        
        $response = $this->_request($uri);
        if ((int)floor($response->getStatusCode() / 100) !== 3) break;
      }
    }
    
    return $response;
  }
  
  protected function buildBody()
  {
    $body = "";
    
    if ($this->method === "GET") {
      unset($this->headers["Content-Type"]);
      unset($this->headers["Content-Length"]);
      return $body;
    }
    
    $hasValues = !empty($this->postValues);
    $hasFiles  = !empty($this->files);
    
    if ($hasValues || $hasFiles) {
      $body = array();
      $boundary = md5hash();
      
      if ($hasValues) {
        foreach ($this->postValues as $name => $value) {
          $body[] = "--" . $boundary;
          $body[] = "Content-Disposition: form-data; name=\"{$name}\"\r\n";
          $body[] = $value;
        }
      }
      
      if ($hasFiles) {
        foreach ($this->files as $name => $file) {
          $name   = $file["name"];
          $input  = $file["formName"];
          $body[] = "--" . $boundary;
          $body[] = "Content-Disposition: form-data; name=\"{$input}\"; filename=\"{$name}\"";
          $body[] = "Content-Type: " . $file["contentType"] . "\r\n";
          $body[] = $file["data"];
        }
      }
      
      $body[] = "--{$boundary}--";
      $body = implode("\r\n", $body);
      $this->headers["Content-Type"] = "multipart/form-data; boundary=\"{$boundary}\"";
      $this->headers["Content-Length"] = strlen($body);
    } elseif ($hasValues) {
      $body = http_build_query($this->postValues, "", "&");
      $this->headers["Content-Type"] = "application/x-www-form-urlencoded";
      $this->headers["Content-Length"] = strlen($body);
    }
    
    return $body;
  }
  
  protected function _request(Sabel_Http_Uri $uri)
  {
    if ($uri->scheme === "ftp") {  // ftp
      $responseText = file_get_contents("ftp://{$uri->host}{$uri->path}");
    } else {  // http | https
      $socket = $this->connect($uri);
      if (fwrite($socket, $this->prepareRequest($uri)) === false) {
        $message = __METHOD__ . "() request failed.";
        throw new Sabel_Exception_Runtime($message);
      }
      
      $texts = array();
      while (true) {
        if (($content = fread($socket, 8192)) === "") break;
        $texts[] = $content;
      }
      
      $this->getValues = $this->postValues = array();
      $responseText = implode("", $texts);
    }
    
    $response = new Sabel_Http_Response($responseText);
    $response->setUri($uri);
    
    if ($this->config["useCookie"]) {
      $this->_setCookie($response, $uri->host);
    }
    
    if (strtolower($response->getHeader("Connection")) === "close") {
      $this->disconnect();
    }
    
    return $response;
  }
  
  protected function prepareRequest(Sabel_Http_Uri $uri)
  {
    $path  = $uri->path;
    $query = $uri->query;
    
    if ($query) $path .= "?" . $query;
    
    if (!empty($this->getValues)) {
      $uriQuery = http_build_query($this->getValues, "", "&");
      if ($query) {
        $path .= "&" . $uriQuery;
      } else {
        $path .= "?" . $uriQuery;
      }
    }
    
    $httpVer = $this->config["httpVersion"];
    $request = array();
    
    if ($this->config["useProxy"]) {
      $request[] = strtoupper($this->method)
                 . " {$uri->scheme}://{$uri->host}:{$uri->port}{$path} "
                 . "HTTP/{$httpVer}";
      
      $request[] = "Host: " . $uri->host;
    } else {
      $request[] = strtoupper($this->method) . " {$path} HTTP/{$httpVer}";
      $request[] = "Host: " . $uri->host;
    }
    
    $body = $this->buildBody();
    
    if ($this->config["keepAlive"]) {
      $this->headers["Connection"] = "keep-alive";
    } else {
      unset($this->headers["Keep-Alive"]);
    }
    
    foreach ($this->headers as $key => $value) {
      $request[] = $key . ": " . $value;
    }
    
    if ($this->config["useCookie"]) {
      if (($cookie = $this->createCookieHeader($uri)) !== null) {
        $request[] = $cookie;
      }
    }
    
    if ($this->auth !== null) {
      $auth = $this->auth["user"] . ":" . $this->auth["password"];
      $request[] = "Authorization: Basic " . base64_encode($auth);
    }
    
    $request = implode("\r\n", $request) . "\r\n\r\n";
    if ($body !== "") $request .= $body;
    
    return $request;
  }
  
  protected function createCookieHeader(Sabel_Http_Uri $uri)
  {
    if (empty($this->cookies)) return null;
    
    $cookies = array();
    $host = $uri->host;
    $path = $uri->path;
    
    foreach ($this->cookies as $cookie) {
      $domain = $cookie["domain"];
      if ($domain{0} === ".") {
        $regex = ".+" . str_replace(".", "\\.", $domain);
        if (preg_match("/{$regex}/", $host) === 0) continue;
      } else {
        if ($domain !== $host) continue;
      }
      
      if (strpos($path, $cookie["path"]) !== 0) continue;
      if (isset($cookie["expires"]) && $cookie["expires"] < time()) continue;
      
      $cookies[] = $cookie["name"] . "=" . urlencode($cookie["value"]);
    }
    
    if (empty($cookies)) {
      return null;
    } else {
      return "Cookie: " . implode("; ", $cookies);
    }
  }
  
  protected function _setCookie(Sabel_Http_Response $response, $host)
  {
    $cookies = $response->getHeader("Set-Cookie");
    if ($cookies === "") return;
    
    foreach ((is_string($cookies)) ? array($cookies) : $cookies as $cookie) {
      $parts = array_map("trim", explode(";", $cookie));
      $first = array_shift($parts);
      list ($key, $value) = explode("=", $first);
      $values = array("name" => $key, "value" => urldecode($value));
      
      foreach ($parts as $part) {
        if ($part === "secure") {
          $values["secure"] = true;
        } else {
          list ($name, $val) = explode("=", $part);
          if ($name === "expires") {
            $values[$name] = strtotime($val);
          } else {
            $values[$name] = $val;
          }
        }
      }
      
      if (!isset($values["domain"])) $values["domain"] = $host;
      if (!isset($values["path"]))   $values["path"]   = "/";
      
      if (isset($values["expires"]) && $values["expires"] < time()) {
        $this->deleteCookie($values["name"], $values["path"], $values["domain"]);
      } else {
        $this->cookies[] = $values;
      }
    }
  }
}
