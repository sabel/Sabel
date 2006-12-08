<?php

Sabel::using('Sabel_Controller_Page');

class Index_Controllers_Index extends Sabel_Controller_Page
{
  public function index()
  {
    echo "welcome to Sabel have fun!\n";
  }
  
  public function notfound()
  {
    echo 'page notfound';
  }
}
