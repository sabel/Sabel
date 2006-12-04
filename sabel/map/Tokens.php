<?php

// リクエストされたURIをトークンにしたもの
class Sabel_Map_Tokens implements Iterator
{
  protected $tokens = array();
  protected $position = 0;
  protected $size = 0;
  
  public function __construct($uriQueryString)
  {
    $this->tokens = explode('/', $uriQueryString);
    $this->size   = count($this->tokens);
  }
  
  public function get($position)
  {
    if (isset($this->tokens[$position]))
      return $this->tokens[$position];
  }
  
  public function current()
  {
    if ($this->valid()) {
      return $this->tokens[$this->position];
    } else {
      return false;
    }
  }
  
  public function key()
  {
    return $this->position;
  }
  
  public function valid()
  {
    return ($this->position < $this->size);
  }
  
  public function next()
  {
    $this->position++;
  }
  
  public function rewind()
  {
    $this->position = 0;
    $this->size = count($this->tokens);
  }
}
