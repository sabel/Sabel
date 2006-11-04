<?php

/**
 * Sabel_View_PageViewer
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_View_PageViewer
{
  const PRIORITY_PREVIOUS = 0;
  const PRIORITY_NEXT     = 1;
  
  private $pager    = null;
  private $window   = 10;
  private $priority = null;
  
  public function __construct($pager)
  {
    if (!$pager instanceof Sabel_View_Pager)
      throw new Sabel_Exception_Runtime('pager is not instaceof sabel_view_pager.');
    $this->pager = clone $pager;
  }
  
  public function getNext()
  {
    return ++$this->pager->pageNumber;
  }
  
  public function getPrevious()
  {
    return --$this->pager->pageNumber;
  }
  
  public function getFirst()
  {
    return 1;
  }
  
  public function getLast()
  {
  }
  
  public function getPage($offset)
  {
  }
  
  public function isCurrent()
  {
  }
  
  public function isFirst()
  {
  }
  
  public function isLast()
  {
  }
  
  public function setWindow($size)
  {
    $this->window =(int) $size;
  }
  
  public function setPriorityPrevious()
  {
    $this->priority = self::PRIORITY_PREVIOUS;
  }
  
  public function setPriorityNext()
  {
    $this->priority = self::PRIORITY_NEXT;
  }
  
  public function setIgnoreEmpty($flag)
  {
  }
  
  public function setStatic($flag)
  {
  }
}
