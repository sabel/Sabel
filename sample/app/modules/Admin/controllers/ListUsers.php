<?php

class Admin_ListUsers extends SabelPageController
{
  public function listing()
  {
    // interacting with model classes.
    if ($this->param == 100) {
      $this->users = array('reo', 'mori');
    } else if ($this->param == 110) {
      $this->users = array('hama', 'hosokawa');
    } else {
      $this->users = array(null, null);
    }

    $this->te->assign('value', $this->param + 10);
    $this->te->assign('users', $this->users);
  }

  public function posted()
  {
    print_r($_POST['test']);
    print "<form action='/Admin/ListUsers/posted' method='post'><input type='submit'/>";
    print "<input type='hidden' name='test' value='111'/>";
    print "</form>";
    print "<a href='/Admin/ListUsers/view/12'>link</a>";
  }

  public function view()
  {
    print "show users <br/>\n";
    if (!is_null($this->users)) {
      foreach ($this->users as $k => $v) {
	print $v . "<br/>\n";
      }
    } else {
      print ($this->param);
    }
    print "<form action='/Admin/ListUsers/posted' method='post'><input type='submit'/>";
    print "<input type='text' name='test' value='111'/>";
    print "</form>";
    print "<a href='/Admin/ListUsers/view/12'>link</a>";
  }

  public function viewtpl()
  {
    $this->te->assign('title', "assign by model");
    $this->te->assign('testval', "test");
    $this->te->assign('value', $this->param);
  }
}

?>
