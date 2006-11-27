<?php

class Sabel_Http_Response
{
  protected $header = null;
  protected $contents = '';
  
  public function setHeader($header)
  {
    $this->header = $header;
  }
  
  public function getHeader()
  {
    return $this->header;
  }
  
  public function setContents($contents)
  {
    $this->contents = $contents;
  }
  
  public function getContents()
  {
    return $this->contents;
  }
}