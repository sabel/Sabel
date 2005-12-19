<?php

//@todo independent Propel Classes from Controller class.
set_include_path(get_include_path() . ':app/commons/models');

require_once('propel/Propel.php');
require_once('blog/Blogs.php');
require_once('blog/BlogsPeer.php');
require_once('blog/Users.php');

Propel::init('app/configs/conf/blog-conf.php');
// here

class Show_blog extends SabelPageController
{
  public function defaults()
  {
    print 'do something';
    $this->forward('/Show/blog/show');
  }
  
  public function show()
  {
    $c = new Criteria();
    $c->setLimit(10);
    $c->addAscendingOrderByColumn(BlogsPeer::ID);
    // $c->addDescendingOrderByColumn(BlogsPeer::ID);
    $r = BlogsPeer::doSelect($c);
    $this->te->assign('blogs', $r);
  }

  public function showFromID()
  {
    if ($this->checkReferer(array('/Show/blog/show'))) {
      print "good";
    } else {
      $this->redirect('/Show/blog/show');
    }
  }

  public function delete()
  {
    $blog = BlogsPeer::retrieveByPK($this->param);
    $blog->delete();

    $this->redirect('/Show/blog/show');
  }

  public function update()
  {
    $blog = BlogsPeer::retrieveByPK($this->param);
    $blog->setBody($this->request->get('body'));
    $blog->save();
    
    $this->redirect('/Show/blog/show');
  }

  public function postWrite()
  {
    $user = new Users();
    $user->setMail('mail');
    $user->setPass('pass');
    $user->save();

    $blog = new Blogs();
    $blog->setSubject($this->request->get('subject'));
    $blog->setBody($this->request->get('body'));
    $blog->setUserID(1);
    $blog->save();

    $this->redirect('/Show/blog/show');
  }
}

?>