<?php

class User_Users extends SabelPageController
{
  public function listing()
  {
    print 'this is admin module your input value is ';
    print $this->test;
  }

  public function add()
  {
    print "user added";
  }

  public function view()
  {
    print "this is view";
  }
}

?>