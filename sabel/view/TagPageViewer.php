<?php

/**
 * Sabel_View_TagPageViewer
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_View_TagPageViewer extends Sabel_View_PageViewer
{
  protected $tag = '';
  
  public function __construct($pager)
  {
    parent::__construct($pager);

    if (isset($_SERVER['HTTP_HOST'])) {
      $this->tag = '<a href="' . uri('') . '">%d</a>';
    } else {
      $this->tag = '<a href="' . uri('', false) . '">%d</a>';
    }
  }
  
  public function getCurrent()
  {
    return sprintf($this->tag, $this->current);
  }
  
  public function getNext()
  {
    $next = (int) min($this->pager->getTotalPageNumber(), $this->current + 1);
    return sprintf($this->tag, $next);
  }
  
  public function getPrevious()
  {
    $previous = (int) max(1, $this->current - 1);
    return sprintf($this->tag, $previous);
  }
  
  public function getFirst()
  {
    return sprintf($this->tag, 1);
  }
  
  public function getLast()
  {
    return sprintf($this->tag, $this->pager->getTotalPageNumber());
  }
  
  public function getPage($offset)
  {
    return sprintf($this->tag, $offset);
  }
}
