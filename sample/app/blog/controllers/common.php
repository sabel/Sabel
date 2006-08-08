<?php

uses('sabel.edo.RecordObject');

class Blog_Common extends Sabel_Controller_Page
{
  public function show()
  {
    $articles = new Sabel_Edo_CommonRecord('article');
    print '<pre>';
    $article = $articles->selectOne(1);
    print_r($article);
    print '</pre>';
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