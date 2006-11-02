<?php

/**
 * Sabel_View_PageViewer
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_View_PageViewer
{
  private $pager = null;
  
  public function __construct($pager)
  {
    if (!($pager instanceof Sabel_View_Pager))
      throw new Sabel_Exception_Runtime('pager is not instaceof sabel_view_pager.');
    $this->pager = $pager;
  }
}
