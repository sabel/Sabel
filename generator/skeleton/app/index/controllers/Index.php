<?php

Sabel::using('Sabel_Controller_Page');

class Index_Controllers_Index extends Sabel_Controller_Page
{
  public function index()
  {
  }
  
  public function notfound()
  {
    echo 'page notfound';
  }
}