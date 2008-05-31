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
    
  }
}
