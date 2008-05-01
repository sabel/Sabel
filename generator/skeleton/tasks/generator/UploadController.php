<#php

class <?php echo $controllerName ?> extends Sabel_Controller_Page
{
  public function initialize()
  {
    $this->layout = false;
  }
  
  public function upload()
  {
    $this->layout   = DEFAULT_LAYOUT_NAME;
    $this->uploadId = md5hash();
  }
  
  public function iframe()
  {
    $this->uploadId = $this->request->fetchGetValue("uploadId");
  }
  
  public function fetchStatus()
  {
    $status = apc_fetch("upload_" . $this->request->fetchGetValue("uploadId"));
    
    $this->renderText = true;
    $this->contents = json_encode($status);
  }
}
