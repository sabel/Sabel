<?php

class Admin_Controllers_Config extends Sabel_Controller_Page
{
  private $filePath = "";
  
  public function initialize()
  {
    $this->filePath = RUN_BASE . DS . "config" . DS . "connection.php";
  }
  
  public function file()
  {
    $configs = Sabel_DB_Config::get();
    
    foreach ($configs as $name => &$param) {
      if (($r = $this->connect($name)) === true) {
        $param["state"] = true;
      } else {
        $param["state"] = $r;
      }
    }
    
    $this->configs = $configs;
  }
  
  private function connect($name)
  {
    try {
      Sabel_DB_Connection::get($name);
      return true;
    } catch (Sabel_DB_Exception $e) {
      return $e->getMessage();
    }
  }
  
  public function openConfigFile()
  {
    $content = file_get_contents($this->filePath);
    $writeable = is_writeable($this->filePath);
    echo json_encode(array("writeable" => $writeable, "content" => $content));
  }
  
  public function saveConfigFile()
  {
    $content = str_replace(array("\r\n", "\r"), PHP_EOL, $this->content);
    file_put_contents($this->filePath, $content);
    $this->redirect->to("a: file");
  }
}
