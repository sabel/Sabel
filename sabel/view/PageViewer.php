<?php

/**
 * Sabel_View_PageViewer
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_View_PageViewer implements Iterator
{
  const PRIORITY_PREVIOUS = 0;
  const PRIORITY_NEXT     = 1;
  
  protected $pager    = null;
  protected $current  = 1;
  protected $window   = 10;
  protected $priority = self::PRIORITY_PREVIOUS;
  protected $igEmpty  = true;
  protected $isStatic = false;
  
  protected $start = 0;
  protected $end   = 0;
  
  protected $position = null;
  
  public function __construct($pager)
  {
    if (!$pager instanceof Sabel_View_Pager)
      throw new Sabel_Exception_Runtime('pager is not instaceof sabel_view_pager.');
    $this->pager   = clone $pager;
    $this->current =(int) $pager->pageNumber;
  }
  
  public function getCurrent()
  {
    return $this->current;
  }
  
  public function getNext()
  {
    return (int) min($this->getLast(), $this->current + 1);
  }
  
  public function getPrevious()
  {
    return (int) max(1, $this->current - 1);
  }
  
  public function getFirst()
  {
    return 1;
  }
  
  public function getLast()
  {
    return $this->pager->getTotalPageNumber();
  }
  
  public function getPage($offset) { return $offset;
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
    return ($this->getCurrent() === $this->pager->getTotalPageNumber());
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
  
  public function current()
  {
    return $this->position;
  }
  
  public function key()
  {
  }
  
  public function next()
  {
    $this->position->current++;
  }
  
  public function rewind()
  {
    $this->position = clone $this;
    $plus = ($this->priority === self::PRIORITY_PREVIOUS) ? 0 : 1;
    $this->start =(int) $this->current - floor(($this->window - $plus) / 2);
    $this->end   =(int) $this->start + $this->window;
    if (!$this->igEmpty) {
      if ($this->start < 1) $this->start = 1;
      if (($start = $this->getLast() - $this->end + 1) < 0) {
        $this->start =(int) $this->start + $start;
      }
      $this->end   =(int) $this->start + $this->window;
    }
    $this->position->current =(int) max(1, $this->start);
  }
  
  public function valid()
  {
    $endPageNum =(int) min($this->getLast() + 1, $this->end);
    return ($this->position->current < $endPageNum);
  }
  
  /*
  protected function getStartPageNumber()
  {
    if ($this->priority === self::PRIORITY_PREVIOUS) {
      $startPageNum = $this->pager->pageNumber - (($this->window) / 2);
    } else {
      $startPageNum = $this->pager->pageNumber - floor((($this->window - 1)) / 2);
    }
    return (int) floor($startPageNum);
  }
  
  protected function getEndPageNumber()
  {
    if ($this->priority === self::PRIORITY_PREVIOUS) {
      $endPageNum = $this->pager->pageNumber + floor((($this->window - 1) / 2));
    } else {
      $endPageNum = $this->pager->pageNumber + (($this->window) / 2);
    }
    return (int) $endPageNum;
  }
   */
}
