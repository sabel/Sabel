<?php

/**
 * Sabel_DB_Migration_Custom
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Custom
{
  private
    $isUpgrade    = false,
    $temporaryDir = "",
    $restorePath  = "";

  public function __construct()
  {
    $directory = Sabel_DB_Migration_Manager::getDirectory();
    $this->temporaryDir = $dir = $directory . DS . "temporary";
    if (!is_dir($dir)) mkdir($dir);
  }

  public function execute($migClassName, $version, $file)
  {
    if (Sabel_DB_Migration_Manager::isUpgrade()) {
      $this->prepareUpgrade($file);
      $this->doUpgrade($migClassName, $version);
    } else {
      $this->prepareDowngrade($file);
      $this->doDowngrade($migClassName);
    }
  }

  public function prepareUpgrade($customFile)
  {
    $this->isUpgrade = true;

    $fp = fopen($customFile, "r");
    $this->splitFiles($fp);
    fclose($fp);

    return $this->temporaryDir;
  }

  public function doUpgrade($migClassName, $version)
  {
    $upgradeFiles = array();
    $temporaryDir = $this->temporaryDir;
    $restoreDir   = $this->getTemporaryRestoresDir();
    $directory    = Sabel_DB_Migration_Manager::setDirectory($temporaryDir);
    $files        = Sabel_DB_Migration_Manager::getFiles($temporaryDir);

    foreach ($files as $file) {
      list ($num) = explode("_", $file);
      $upgradeFiles[$num] = $file;

      file_put_contents($restoreDir . DS . "restore_{$num}" . PHP_SUFFIX, PHP_EOL);

      $path = $temporaryDir . DS . $file;
      $ins = new $migClassName($path, "upgrade");
      $ins->execute();

      unlink($path);
    }

    Sabel_DB_Migration_Manager::setDirectory($directory);
    $this->createCustomRestoreFile($upgradeFiles, $version);
  }

  public function prepareDowngrade($restoreFile)
  {
    $this->isUpgrade = false;

    $fp = fopen($restoreFile, "r");
    $this->splitFiles($fp);
    fclose($fp);

    $this->isUpgrade = true;

    $fileName  = basename($restoreFile);
    $version   = $this->getVersionFromRestoreFileName($fileName);
    $directory = Sabel_DB_Migration_Manager::getDirectory();

    $fp = fopen($directory . DS . "{$version}_mix" . PHP_SUFFIX, "r");
    $this->splitFiles($fp);
    fclose($fp);

    return $this->temporaryDir;
  }

  public function doDowngrade($migClassName)
  {
    $temporaryDir = $this->temporaryDir;

    $directory = Sabel_DB_Migration_Manager::setDirectory($temporaryDir);
    $files     = Sabel_DB_Migration_Manager::getFiles();
    $files     = array_reverse($files);
    $fileNum   = count($files) + 1;
    $prefix    = $temporaryDir . DS;

    for ($i = 1; $i < $fileNum; $i++) {
      $file = $files[$i - 1];
      $exp  = explode("_", $file);
      $exp[0] = $i;

      $path = $prefix . implode("_", $exp);
      rename($prefix . $file, $path);

      $ins = new $migClassName($path, "downgrade", $temporaryDir);
      $ins->execute();

      unlink($path);
    }

    Sabel_DB_Migration_Manager::setDirectory($directory);
  }

  private function splitFiles($fp)
  {
    $num   = 1;
    $lines = array();
    $fName = null;

    if (!is_resource($fp)) return;

    while (!feof($fp)) {
      $line = trim(fgets($fp, 256));

      if ($line === "<?php") {
        $lines = array();
        continue;
      }

      if (empty($lines) && $line === "") continue;

      if (substr($line, 0, 3) === "###") {
        if (isset($fName)) {
          $this->writeTemporaryFile($lines, $num, $fName);
          $lines = array();
          $num++;
        }

        $fName = trim(str_replace("#", "", $line));
        continue;
      }

      $lines[] = $line;
    }

    $this->writeTemporaryFile($lines, $num, $fName);
  }

  private function writeTemporaryFile($lines, $num, $fName)
  {
    $tmpDir = $this->temporaryDir;

    if ($this->isUpgrade) {
      $fileName = $tmpDir . DS . "{$num}_{$fName}" . PHP_SUFFIX;
    } else {
      $dir = $this->getTemporaryRestoresDir();
      $fileName = $dir . DS . "restore_" . $num . PHP_SUFFIX;
    }

    $content = "<?php" . PHP_EOL . PHP_EOL . implode(PHP_EOL, $lines);
    file_put_contents($fileName, $content);
  }

  private function createCustomRestoreFile($upgradeFiles, $version)
  {
    $restoreDir = $this->getTemporaryRestoresDir();

    $files = array();
    foreach (scandir($restoreDir) as $file) {
      if (preg_match("/\.+$/", $file)) continue;
      $index = $this->getVersionFromRestoreFileName($file);
      $files[$index] = $restoreDir . DS . $file;
    }

    $files = array_reverse($files);
    $directory = Sabel_DB_Migration_Manager::getDirectory();
    $dir = $directory . DS . "restores";
    if (!is_dir($dir)) mkdir($dir);

    $restore = $dir . DS . "restore_{$version}" . PHP_SUFFIX;
    $rfp     = fopen($restore, "w");

    foreach ($files as $file) {
      $fp = fopen($file, "r");
      $version = $this->getVersionFromRestoreFileName(basename($file));
      list (, $mdlName, $command) = explode("_", $upgradeFiles[$version]);

      $command = $mdlName . "_" . $command;
      fwrite($rfp, "##### $command #####" . PHP_EOL);

      while (!feof($fp)) {
        $line = trim(fgets($fp, 256));
        if ($line === "<?php" || $line === "?>") continue;
        fwrite($rfp, $line . PHP_EOL);
      }

      fclose($fp);
      unlink($file);
    }

    fclose($rfp);
  }

  private function getVersionFromRestoreFileName($fileName)
  {
    $under   = strpos($fileName, "_");
    $dot     = strpos($fileName, ".");
    $version = substr($fileName, ++$under, $dot - $under);

    return $version;
  }

  private function getTemporaryRestoresDir()
  {
    $dir = $this->temporaryDir . DS . "restores";
    if (!is_dir($dir)) mkdir($dir);

    return $dir;
  }
}
