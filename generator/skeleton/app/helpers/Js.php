<?php

class Helpers_Js
{
  public static function ajaxPager($replaceId, $pagerClass = "sbl_pager")
  {
    $buf   = array();
    $buf[] = '<script type="text/javascript" src="';
    $buf[] = linkTo("js/AjaxPager.js");
    $buf[] = '"></script>';
    
    $buf[] = "\n";
    
    $buf[] = '<script type="text/javascript">';
    //$buf[] = sprintf('window.onload = function() { new Sabel.PHP.AjaxPager("%s", "%s"); };', $replaceId, $pagerClass);
    $buf[] = sprintf('new Sabel.Event(window, "load", function() { new Sabel.PHP.AjaxPager("%s", "%s"); });', $replaceId, $pagerClass);
    $buf[] = '</script>';
    
    return join($buf, "")."\n";
  }
  
  public static function formValidator($formObj, $errBox = "sbl_errmsg")
  {
    $model   = $formObj->getModel();
    $columns = $model->getColumns();
    $errMsgs = Sabel_DB_Validate_Config::getMessages();
    $lNames  = Sabel_DB_Model_Localize::getColumnNames($model->getName());
    
    $data = array("data" => array(), "errors" => $errMsgs);
    foreach ($columns as $c) {
      $name = $c->name;
      $c->name = $lNames[$c->name];
      $data["data"][$name] = array_change_key_case((array) $c, CASE_UPPER);
    }
    
    $buf = array();
    $buf[] = '<script type="text/javascript" src="';
    $buf[] = linkTo("js/FormValidator.js");
    $buf[] = '"></script>';
    
    $buf[] = "\n";
    
    $buf[] = '<script type="text/javascript">';
    $buf[] = 'new Sabel.PHP.FormValidator('.json_encode($data).');';
    $buf[] = '</script>';
    
    return join($buf, "")."\n";
  }
}
