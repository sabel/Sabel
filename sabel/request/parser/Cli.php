<?php

uses('sabel.request.parser.Common');

class Sabel_Request_Parser_Cli extends Sabel_Request_Parser_Common
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
    $this->parseWithPattern($request, $pair, $pat);
    
    
    return $this;
  }
  
  public function parseDefault($request, $pair = null)
  {
    
  }
  
  public function parseWithPattern($request, $pair, $pat)
  {
    $this->parameters = $request[count($pat)];
    $pairs = explode('/', $pair);
    
    for ($i = 0; $i < count($pat); $i++) {
      $p = '%^'.$pat[$i].'$%';
      if (preg_match($p, $request[$i], $match)) {
        $this->attributes[$pairs[$i]] = $match[1];
      } else {
        $this->attributes[$pairs[$i]] = null;
      }
    }

    return true;
  }
}

?>