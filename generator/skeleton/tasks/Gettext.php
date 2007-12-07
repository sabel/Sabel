<?php

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
    $dirs[] = MODULES_DIR_PATH;
    $dirs[] = CONFIG_DIR_PATH;
    $dirs[] = RUN_BASE . DS . LIB_DIR_NAME;
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
      preg_match_all("/_\((.*)\)/", $contents, $matches);
      if (!empty($matches[1])) {
        $messages = array_merge($messages, $this->trims($matches[1]));
      }
    }
    
    $filePath = DS . "LC_MESSAGES" . DS . $this->domain . PHP_SUFFIX;
    
    foreach ($locales as $locale) {
      if (!empty($this->locales) && !in_array($locale, $this->locales)) continue;
      $isDefaultLocale = ($locale === $this->defaultLocale);
      
      $path = $dir . $locale . $filePath;
      if (is_file($path)) {
        $this->marge($path, $messages, $isDefaultLocale);
        continue;
      }
      
      $code = array("<?php" . PHP_EOL . PHP_EOL);
      $code[] = '$messages = array(' . PHP_EOL;
      
      foreach ($messages as $message) {
        if ($isDefaultLocale) {
          $code[] = '"' . $message . '" => "' . $message . '",' . PHP_EOL;
        } else {
          $code[] = '"' . $message . '" => "",' . PHP_EOL;
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
    
    $code = array("<?php" . PHP_EOL . PHP_EOL);
    $code[] = '$messages = array(' . PHP_EOL;
    
    foreach ($messages as $msgid => $message) {
      $code[] = '"' . $msgid . '" => "' . $message . '",' . PHP_EOL;
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
  
  private function trims($messages)
  {
    foreach ($messages as &$message) {
      $message = substr($message, 1, -1);
    }
    
    return $messages;
  }
}
