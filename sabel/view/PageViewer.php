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
  private $current  = 1;
  private $window   = 10;
  private $priority = null;
  private $igEmpty  = false;
  private $isStatic = false;
  
  public function __construct($pager)
  {
    if (!$pager instanceof Sabel_View_Pager)
      throw new Sabel_Exception_Runtime('pager is not instaceof sabel_view_pager.');
    $this->pager = clone $pager;
    $this->currentPageNumber = $pager->pageNumber;
  }
  
  public function getCurrent()
  {
    return $this->pager->pageNumber;
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
    return $this->pager->getTotalPageNumber();
  }
  
  public function getPage($offset)
  {
    return $offset;
  }
  
  public function isCurrent()
  {
    return ($this->getCurrent() === $this->current);
  }
  
  public function isFirst()
  {
    return ($this->getCurrent() === 1);
  }
  
  public function isLast()
  {
    return ($this->pager->pageNumber === $this->pager->getTotalPageNumber());
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
    $this->igEmpty = $flag;
  }
  
  public function setStatic($flag)
  {
    $this->isStatic = $flag;
  }
}
