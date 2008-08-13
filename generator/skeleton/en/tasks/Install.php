<?php

/**
 * Install
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Install extends Sabel_Sakle_Task
{
  protected $client = null;
  
  protected $fs = null;
  
  protected $addonRepositories = array(
    "http://www.sabel.jp/archives/addon",
  );
  
  public function initialize()
  {
    $this->client = new Sabel_Http_Request("");
    $this->fs = new Sabel_Util_FileSystem(RUN_BASE);
  }
  
  public function run()
  {
    $args = $this->arguments;
    
    if (Sabel_Console::hasOption("a", $args)) {
      $this->installAddon(Sabel_Console::getOption("a", $args));
    } elseif (Sabel_Console::hasOption("l", $args)) {  // library
      
    } elseif (Sabel_Console::hasOption("p", $args)) {  // processor
      
    } else {
      
    }
  }
  
  protected function installAddon($addon, $repository = "")
  {
    foreach ($this->addonRepositories as $repo) {
      $url = $repo . "/{$addon}?type=xml&version=";
      
      try {
        $this->client->setUri($url);
        $response = @$this->client->request();
        
        if (($status = $response->getStatusCode()) === Sabel_Response::OK) {
          $stobj = new Sabel_Response_Status($status);
          $this->success($repo . ' : "' . $stobj . '"');
          $this->_install($response->getContent());
        } else {
          $stobj = new Sabel_Response_Status($status);
          $this->warning($repo . ' : "' . $stobj . '"');
        }
      } catch (Exception $e) {
        $this->warning($e->getMessage() . " '{$repo}'");
      }
    }
  }
  
  protected function _install($xml)
  {
    $doc   = new Sabel_Xml_Document();
    $root  = $doc->loadXML($xml);
    $files = $root->getChildren("file");
    
    foreach ($files as $file) {
      $path = $file->getChild("path")->getNodeValue();
      $path = str_replace(":", DS, $path);
      $source = $file->getChild("source")->getNodeValue();
      
      if (!$this->fs->isFile($path)) {
        $this->fs->mkfile($path)->write($source)->save();
      } else {
        $this->fs->getFile($path)->write($source)->save();
      }
      
      $this->success("Install '{$path}'");
    }
  }
}
