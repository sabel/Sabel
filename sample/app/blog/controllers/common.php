<?php

class Blog_Common extends Sabel_Controller_Page
{
  public function show()
  {
    for($i; $i < 800; $i++);
    print "show blogs \n";
  }
  
  public function showByDate()
  {
    print "show by date. \n";
  }
  
  public function entry()
  {
    $this->response->id = $this->request->getByName('id');
  }
}