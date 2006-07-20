<?php
/**
 * Sabel_Controller_Map_Uri
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Map_Uri
{
  protected $uri;
  
  public function __construct($uri)
  {
    $this->uri = $uri;
  }
  
  public function getString()
  {
    return (string) $this->uri;
  }
  
  public function count()
  {
    return count(explode('/', $this->uri));
  }
  
  public function getElement($position)
  {
    $elements = $this->getElements();
    if ($position < count($elements)) {
      return $elements[$position];
    } else {
      return false;
    }
  }
  
  public function getElements()
  {
    return $elements = explode('/', $this->uri);
  }
}

?>