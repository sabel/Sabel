<?php

uses('sabel.edo.RecordObject');

class Author extends Sabel_Edo_RecordObject
{
  public function __construct($table = null)
  {
    $this->setEDO('user', 'pdo');
    parent::__construct();
    
    if (!is_null($table)) $this->table = $table;
  }
}

class Blog_Common extends Sabel_Controller_Page
{
  public function show()
  {
    $articles = new Sabel_Edo_CommonRecord('article');
    $articles->setSelectType(Sabel_Edo_RecordObject::WITH_PARENT_OBJECT);
    
    $this->response->articles = $articles->select();
  }
  
  public function showByDate()
  {
    print "show by date. \n";
  }
  
  public function prepareEdit()
  {
  }
  
  public function doEdit()
  {
  }
  
  public function entry()
  {
    $id = $this->request->getByName('id');
    $this->response->id = $id;
    
    if ($id == 15) {
      $this->response->title = 'テスト';
      $this->response->body  = 'あたり本文でーす';
    } else {
      $this->response->title = 'テスト';
      $this->response->body  = '本文でーす';
    }
  }
}