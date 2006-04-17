<?php

interface DirectoryList
{
  public function listing($path);
}

class DirectoryListImpl implements DirectoryList
{
  public function listing($path)
  {
    $result = array();
    foreach (new DirectoryIterator($path) as $k => $v) {
      if (!$v->isDot() && $v->isFile()) {
        $result[] = $v->getFilename();
      }
    }
    
    return $result;
  }
}

class ClassNameList implements DirectoryList
{
  protected $list = null;
  
  public function __construct(DirectoryList $list)
  {
    $this->list = $list;
  }
  
  public function listing($path)
  {
    $result = array();
    $l = $this->list->listing($path);
    foreach ($l as $k => $file) {
      $classname = explode('.', $file);
      if ($classname[0]) {
        $result[] = $classname[0];
      }
    }
    return $result;
  }
}

?>