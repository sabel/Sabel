<?php

/**
 * Sakle Gettext
 *
 * @abstract
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Gettext extends Sabel_Sakle_Task
{
  private $files    = array();
  private $fileName = "";
  private $defaultLocale = "en";
  private $locales = array();
  
  public function initialize()
  {
    $this->fileName = "messages" . PHP_SUFFIX;
  }
  
  public function run()
  {
    $dirs   = array();
    $dirs[] = MODULES_DIR_PATH;
    $dirs[] = RUN_BASE . DS . LIB_DIR_NAME;
    $dirs[] = RUN_BASE . DS . "public";
    
    foreach ($dirs as $dir) {
      $this->addFiles($dir);
    }
    
    $this->createOptions();
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
      $regex = '/_\(("(.+[^\\\\])"|\'(.+[^\\\\])\')\)/U';
      preg_match_all($regex, $contents, $matches);
      if (!empty($matches[1])) {
        $temp = array();
        
        if (!empty($matches[2])) {
          $temp = $matches[2];
        }
        
        if (!empty($matches[3])) {
          foreach ($matches[3] as $k => $v) {
            $v = str_replace("\\'", "'", $v);
            if ($v !== "") $temp[$k] = $v;
          }
        }
        
        $messages = array_merge($messages, $temp);
      }
    }
    
    $messages = array_unique($messages);
    $filePath = DS . $this->fileName;
    
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
  
  private function createOptions()
  {
    $arguments = $this->arguments;
    
    if (in_array("-f", $arguments)) {
      $index = array_search("-f", $arguments) + 1;
      if (isset($arguments[$index])) {
        $this->fileName = $arguments[$index];
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
