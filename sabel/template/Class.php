<?php

class Sabel_Template_Class
{
  const COMPILE_DIR = 'data/compiled/';

  protected $vars = array();
  protected $path = '';
  protected $name = '';

  public function __construct($path = '', $name = '')
  {
    $this->path = $path;
    $this->name = $name;
  }

  public function assign($key, $val)
  {
    $this->params[$key] = $val;
  }

  public function setPath($path)
  {
    $this->templatePath = $path;
  }

  public function setName($name)
  {
    $this->templateName = $name;
  }

  public function retrieve($pageCache = false)
  {
    $contents = '';

    $filepath = $this->getTemplateFilePath();
    $cpath    = $this->getCompileFilePath();

    if (is_file($filepath) && (!is_readable($cpath) || filemtime($filepath) > filemtime($cpath))) {
      $contents = file_get_contents($filepath);
      $contents = str_replace('<?',  '<?php',      $contents);
      $contents = str_replace('<?php= ', '<?php echo', $contents);
      $contents = str_replace('{',    '<?php echo ', $contents);
      $contents = str_replace('}',    ' ?>',         $contents);
      $contents = preg_replace('/<\?phph (\$[\w]+) \?>/', '<?php echo htmlspecialchars($1) ?>', $contents);

      $this->saveCompileFile($contents);
    }

    extract($this->params);
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

  private function getTemplateFilePath()
  {
    return $this->templatePath . $this->templateName;
  }

  private function getCompileFilePath()
  {
    return RUN_BASE . self::COMPILE_DIR . $this->templateName;
  }

  public function display($pageCache = false)
  {
    echo $this->retrieve($pageCache);
  }
}
