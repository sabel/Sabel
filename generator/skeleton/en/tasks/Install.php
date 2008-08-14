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
  /**
   * @var Sabel_Http_Request
   */
  protected $client = null;
  
  /**
   * @var Sabel_Util_FileSystem
   */
  protected $fs = null;
  
  /**
   * @var float
   */
  protected $version = null;
  
  /**
   * @var array
   */
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
    
    if (Sabel_Console::hasOption("v", $args)) {
      $this->version = Sabel_Console::getOption("v", $args);
    }
    
    if (Sabel_Console::hasOption("a", $args)) {
      $this->installAddon(Sabel_Console::getOption("a", $args));
    } elseif (Sabel_Console::hasOption("l", $args)) {  // library
      
    } elseif (Sabel_Console::hasOption("p", $args)) {  // processor
      
    } else {
      
    }
  }
  
  protected function installAddon($addon, $repository = "")
  {
    $addon = lcfirst($addon);
    foreach ($this->addonRepositories as $repo) {
      $url = $repo . "?name={$addon}&type=xml&version={$this->version}";
      
      try {
        $this->client->setUri($url);
        $response = @$this->client->request();
        $this->_installAddon($addon, $response->getContent());
      } catch (Exception $e) {
        $this->warning($e->getMessage() . " '{$repo}'");
      }
    }
  }
  
  protected function _installAddon($name, $xml)
  {
    $doc  = new Sabel_Xml_Document();
    $root = $doc->loadXML($xml);
    
    if ($error = $root->getChild("error")) {
      $this->error($error->getNodeValue());
      $this->error("install failed.");
      return;
    }
    
    $version   = $root->getChild("version")->getNodeValue();
    $addonName = ucfirst($name);
    $className = $addonName . "_Addon";
    
    if (class_exists($className, true)) {
      eval ('$v = ' . $className . '::VERSION;');
      if ($v === (float)$version) {
        $this->message("{$addonName}_{$version} already installed.");
        return;
      } elseif ((float)$version > $v) {
        $_v = (strpos($v, ".") === false) ? "{$v}.0" : $v;
        $this->message("upgrade " . $addonName . " from {$_v} to {$version}.");
      } else {
        $this->message("nothing to install.");
        return;
      }
    }
    
    $files = $root->getChildren("file");
    foreach ($files as $i => $file) {
      $path = $file->getChild("path")->getNodeValue();
      $path = str_replace(":", DS, $path);
      $source = $file->getChild("source")->getNodeValue();
      
      if (!$this->fs->isFile($path)) {
        $this->fs->mkfile($path)->write($source)->save();
      } else {
        $this->fs->getFile($path)->write($source)->save();
      }
      
      $this->success($path);
    }
    
    $this->success("install ok: {$addonName}_{$version}");
  }
}
