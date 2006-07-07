<?php

uses('sabel.request.parser.Common');

/**
 * 
 *
 */
class Sabel_Request_Parser_Web extends Sabel_Request_Parser_Common
{
  public static function create()
  {
    if (!self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }
  
  public function parse($request = null, $pair = null, $pat = null)
  {
    if (is_not_null($pat) && is_null($pair))
      throw new Sabel_Exception_Runtime('pair is null.');
      
    if (empty($request)) return null;
    
    if (is_not_null($pat) && count($pat) > 0) {
      $this->parseWithPattern($request, $pair, $pat);
    } else if (is_null($pair)) {
      $this->parseDefault($request);
    } else {
      $this->parseDefault($request, $pair);
    }
    
    return $this;
  }
  
  protected function parseDefault($request, $pair = null)
  {
    $pair = (is_null($pair)) ? 'module/controller/action' : $pair;
    
    $request  = explode('?', $request);
    $this->parameters = $request[1];
    $requests = explode('/', $request[0]);
    $pairs    = explode('/', $pair);
    
    for ($i = 0; $i < count($pairs); $i++) {
      $this->attributes[$pairs[$i]] = $requests[$i];
    }
  }
  
  protected function parseWithPattern($request, $pair, $pat)
  {
    $request  = explode('?', $request);
    $this->parameters = $request[1];
    $requests = explode('/', $request[0]);
    $pairs    = explode('/', $pair);
    
    for ($i = 0; $i < count($pat); $i++) {
      $p = '%^'.$pat[$i].'$%';
      if (preg_match($p, $requests[$i], $match)) {
        $this->attributes[$pairs[$i]] = $match[1];
      } else {
        $this->attributes[$pairs[$i]] = null;
      }
    }
    
    return true;
  }
}

?>