<?php

/**
 * Sabel_DB_Migration_Util_Custom
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Util_Custom
{
  private $temporaryPath = "";
  private $restorePath   = "";

  public function prepareUpgrade($customFile)
  {
    $tmpDir = MIG_DIR . "/temporary";
    mkdir($tmpDir);

    $num = 1;
    $fileName = "";
    $files = array();
    $lines = array();

    $fp = fopen($customFile, "r");

    while (!feof($fp)) {
      $line = trim(fgets($fp, 256));
      if (empty($lines) && $line === "") continue;
      if (substr($line, 0, 3) === "###") {
        if (!empty($lines)) {
          $this->writeTemporaryFile($lines, "{$tmpDir}/{$num}_{$fileName}");
          $num++;
          $lines = array();
        }

        $fileName = trim(str_replace("#", "", $line));
        continue;
      }

      $lines[] = $line;
    }

    $this->writeTemporaryFile($lines, "{$tmpDir}/{$num}_{$fileName}");
    return $this->temporaryPath = $tmpDir;
  }

  public function createRestoreDir($filePath)
  {
    // @todo : is not necessary to create dir for restore files.
    $fileName = getFileName($filePath);
    list ($num) = explode("_", $fileName);

    $dirName = $num . "Mix";
    $restorePath = MIG_DIR . "/restores/" . $dirName;

    if (!is_dir($restorePath)) mkdir($restorePath);
    $this->restorePath = $restorePath;
  }

  public function createCustomRestoreFile()
  {
    $tmpRestorePath = $this->temporaryPath . "/restores";
    if (!is_dir($tmpRestorePath)) return;

    $handle = opendir($tmpRestorePath);

    $files = array();
    while (($file = readdir($handle)) !== false) {
      list (, $num) = explode("_", $file);
      if (is_numeric($num)) $files[$num] = $file;
    }

    $this->writeRestoreFile(array_reverse($files));
  }

  private function writeTemporaryFile($lines, $path)
  {
    $fp = fopen($path, "w");
    foreach ($lines as $line) fwrite($fp, $line. "\n", 256);
    fclose($fp);
  }

  // @todo
  private function writeRestoreFile($files)
  {
    $restore = $this->restorePath;
    foreach ($files as $file) {
      
    }
  }
}
