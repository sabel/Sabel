<?php

class Admin_Controllers_Index extends Sabel_Controller_Page
{
  private $configFile = "";
  
  public function initialize()
  {
    $this->configFile = RUN_BASE . DS . "config" . DS . "connection.php";
  }
  
  public function index()
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
    $content = file_get_contents($this->configFile);
    $writeable = is_writeable($this->configFile);
    echo json_encode(array("writeable" => $writeable, "content" => $content));
  }
  
  public function saveConfigFile()
  {
    $content = str_replace(array("\r\n", "\r"), PHP_EOL, $this->content);
    file_put_contents($this->configFile, $content);
    $this->redirect->to("a: index");
  }
  
  public function show()
  {
    $accessor = new Sabel_DB_Schema_Accessor($this->db);
    $this->tables = $accessor->getTableList();
    $this->setAttribute("db", $this->db);
  }
  
  public function convertToText()
  {
    $brs = array("<br />", "<br>", "<br/>");
    echo str_replace($brs, PHP_EOL, rawurldecode($this->content));
  }
  
  public function convertToHtml()
  {
    echo nl2br(htmlspecialchars($this->content));
  }
}
