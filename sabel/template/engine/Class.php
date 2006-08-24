<?php

class Sabel_Template_Engine_Class extends Sabel_Template_Engine
{
  const COMPILE_DIR = '/data/compiled/';
  
  public function configuration()
  {
  }

  public function assign($key, $val)
  {
    $this->attributes[$key] = $val;
  }

  public function retrieve()
  {
    $contents = '';

    $filepath = $this->getTemplateFullPath();
    $cpath    = $this->getCompileFilePath();

    if (is_file($filepath) && (!is_readable($cpath) || filemtime($filepath) > filemtime($cpath))) {
      $contents = file_get_contents($filepath);
      $contents = str_replace('<?',     '<?php',       $contents);
      $contents = str_replace('<?php=', '<?php echo',  $contents);
      $contents = preg_replace('/<\?phph[[:blank:]]*([^? ]+)[[:blank:]]*\?>/', '<?php echo htmlspecialchars($1) ?>', $contents);

      $this->saveCompileFile($contents);
    }

    if (count($this->attributes) != 0) extract($this->attributes, EXTR_SKIP);
    extract(Re::get(), EXTR_SKIP);
    ob_start();
    include($cpath);
    $contents = ob_get_clean();
    if ($pageCache) $this->saveCompileFile($contents);

    return $contents;
  }

  private function saveCompileFile($contents)
  {
    $cpath = $this->getCompileFilePath();
    $fp = fopen($cpath, 'w');
    fwrite($fp, $contents);
    fclose($fp);
  }

  private function getCompileFilePath()
  {
    return RUN_BASE . self::COMPILE_DIR . md5($this->tplpath) . $this->tplname;
  }
}
