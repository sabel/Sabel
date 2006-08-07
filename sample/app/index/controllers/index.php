<?php

require_once('Sabel/third/Smarty/Smarty.class.php');

/**
 * index
 *
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Index_Index extends Sabel_Controller_Page
{
  public function index()
  {
    $this->response->test = 'アサインテスト';
    $this->response->title = 'テスト';
    $this->response->ar    = array('1', '2', '3', '4', '5', '日本語テスト', '英語');
  }
}
