<#php

class <?php echo $controllerName ?> extends Sabel_Controller_Page
{
  public function upload()
  {
    $this->uploadId = md5hash();
  }
  
  public function fetchStatus()
  {
    $status = apc_fetch("<?php echo $rfc1867_prefix ?>" . $this->request->fetchGetValue("uploadId"));
    
    echo json_encode($status);
    exit;
  }
  
  public function uploaded()
  {
    $file = $_FILES["upfile"];
    $path = RUN_BASE . DS . "data" . DS . basename($file["name"]);
    if (!move_uploaded_file($file["tmp_name"], $path)) {
      // upload error.
    }
    
    echo "uploaded";
    exit;
  }
}
