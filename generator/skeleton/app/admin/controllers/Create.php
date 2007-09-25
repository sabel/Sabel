<?php

/**
 * @executer flow
 */
class Admin_Controllers_Create extends Sabel_Controller_Page
{
  /**
   * @flow start
   * @next addConstraints
   */
  public function table()
  {
    
  }
  
  /**
   * @next table doCreate
   */
  public function addConstraints()
  {
    dump($this->request->fetchPostValues());
  }
  
  public function doCreate()
  {
    
  }
}
