<?php

class Admin_Controllers_Main extends Sabel_Controller_Page
{
  public function index()
  {
    $this->tables = Sabel_DB_Driver::createSchema()->getTableList();
  }
}
