<#php

Sabel::using('Sabel_Controller_Page');

class <?php echo ucfirst($module) ?>_Controllers_<?php echo ucfirst($controllerName) ?> extends Sabel_Controller_Page
{
  public function index()
  {
    echo "welcome to <?php echo ucfirst($controllerName) ?> controller. \n";
  }
}