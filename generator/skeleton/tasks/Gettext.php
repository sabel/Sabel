<?php

if (!defined("RUN_BASE")) define("RUN_BASE", getcwd());

Sabel::fileUsing("config" . DS . "INIT.php", true);

/**
 * Gettext
 *
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Gettext extends Sabel_Sakle_Task
{
  private $files  = array();
  private $domain = "messages";
  private $defaultLocale = "en";
  private $locales = array();
  
  public function run($arguments)
  {
    $dirs   = array();
    $dirs[] = RUN_BASE . DS . "app";
    $dirs[] = RUN_BASE . DS . "config";
    $dirs[] = RUN_BASE . DS . "lib";
    $dirs[] = RUN_BASE . DS . "public";
    
    foreach ($dirs as $dir) {
      $this->addFiles($dir);
    }
    
    $this->createOptions($arguments);
    $this->createMessageFiles();
  }

  private function addFiles($dir)
  {
    foreach (scandir($dir) as $item) {
      if ($item === "." || $item === "..") continue;
      $item = $dir . DS . $item;
      if (is_dir($item)) {
        $this->addFiles($item);
      } else {
        $this->files[] = $item;
      }
    }
  }
  
  private function createMessageFiles()
  {
    $dir = RUN_BASE . DS . "locale" . DS;
    $locales = array();
    
    foreach (scandir($dir) as $item) {
      if ($item === "." || $item === "..") continue;
      if (is_dir($dir . $item)) $locales[] = $item;
    }
    
    if (empty($locales)) exit;
    if (empty($this->files)) exit;
    
    $messages = array();
    foreach ($this->files as $file) {
      $contents = file_get_contents($file);
      preg_match_all("/_\(('|\")(?>[^'\"]+)\\1\)/", $contents, $matches);
      if (!empty($matches[2])) {
        $messages = array_merge($messages, $matches[2]);
      }
    }
    
    $filePath = DS . "LC_MESSAGES" . DS . $this->domain . ".php";
    
    foreach ($locales as $locale) {
      if (!empty($this->locales) && !in_array($locale, $this->locales)) continue;
      $isDefaultLocale = ($locale === $this->defaultLocale);
      
      $path = $dir . $locale . $filePath;
      if (is_file($path)) {
        $this->marge($path, $messages, $isDefaultLocale);
        continue;
      }
      
      $code = array("<?php\n\n");
      $code[] = '$messages = array(' . "\n";
      
      foreach ($messages as $message) {
        if ($isDefaultLocale) {
          $code[] = '"' . $message . '" => "' . $message . '",' . "\n";
        } else {
          $code[] = '"' . $message . '" => "",' . "\n";
        }
      }
      
      $code[] = ");";
      file_put_contents($path, implode("", $code));
    }
  }
  
  private function marge($filePath, $newMessages, $isDefaultLocale)
  {
    include ($filePath);
    
    foreach ($newMessages as $msgid) {
      if (!isset($messages[$msgid])) {
        if ($isDefaultLocale) {
          $messages[$msgid] = $msgid;
        } else {
          $messages[$msgid] = "";
        }
      }
    }
    
    $code = array("<?php\n\n");
    $code[] = '$messages = array(' . "\n";
    
    foreach ($messages as $msgid => $message) {
      $code[] = '"' . $msgid . '" => "' . $message . '",' . "\n";
    }
    
    $code[] = ");";
    file_put_contents($filePath, implode("", $code));
  }
  
  private function createOptions($arguments)
  {
    if (in_array("-d", $arguments)) {
      $index = array_search("-d", $arguments) + 1;
      if (isset($arguments[$index])) {
        $this->domain = $arguments[$index];
      }
    }
    
    if (in_array("-dl", $arguments)) {
      $index = array_search("-dl", $arguments) + 1;
      if (isset($arguments[$index])) {
        $this->defaultLocale = $arguments[$index];
      }
    }
    
    if (in_array("-l", $arguments)) {
      $index = array_search("-l", $arguments) + 1;
      $size  = count($arguments);
      for ($i = $index; $i < $size; $i++) {
        if (isset($arguments[$i])) {
          $locale = $arguments[$i];
          if ($locale{0} !== "-") {
            $this->locales[] = $arguments[$i];
          }
        }
      }
    }
  }
}
