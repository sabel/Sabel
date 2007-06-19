<?php

/**
 * Sabel_DB_Migration_Classes_Custom
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Classes_Custom
{
  private $isUpgrade     = false;
  private $temporaryPath = "";
  private $restorePath   = "";

  public function __construct()
  {
    $this->temporaryPath = $tp = MIG_DIR . DIR_DIVIDER . "temporary";
    if (!is_dir($tp)) mkdir($tp);
  }

  public function prepareUpgrade($customFile)
  {
    $this->isUpgrade = true;

    $fp = fopen($customFile, "r");
    $this->splitFiles($fp);
    fclose($fp);

    return $this->temporaryPath;
  }

  public function doUpgrade($migClassName, $version)
  {
    $temporaryDir = $this->temporaryPath;
    $upgradeFiles = array();
    $files = getMigrationFiles($temporaryDir);

    foreach ($files as $file) {
      $path = $temporaryDir . DIR_DIVIDER . $file;
      $ins = new $migClassName($path, "upgrade", $temporaryDir);
      $ins->execute();

      list ($num) = explode("_", $file);
      $upgradeFiles[$num] = $file;
      unlink($path);
    }

    $this->createCustomRestoreFile($upgradeFiles, $version);
  }

  public function prepareDowngrade($restoreFile)
  {
    $this->isUpgrade = false;

    $restoresDir = $this->temporaryPath . DIR_DIVIDER . "restores";
    if (!is_dir($restoresDir)) mkdir($restoresDir);

    $fp = fopen($restoreFile, "r");
    $this->splitFiles($fp);
    fclose($fp);

    $this->isUpgrade = true;
    list (, $num) = explode("_", $restoreFile);

    $fp = fopen(MIG_DIR . DIR_DIVIDER . "{$num}_Mix.php", "r");
    $this->splitFiles($fp);
    fclose($fp);

    return $this->temporaryPath;
  }

  public function doDowngrade($migClassName)
  {
    $temporaryDir = $this->temporaryPath;
    $files = array_reverse(getMigrationFiles($temporaryDir));
    $fileNum = count($files) + 1;
    $prefix  = $temporaryDir . DIR_DIVIDER;

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
  }

  private function splitFiles($fp)
  {
    $num      = 1;
    $lines    = array();
    $fileName = "";

    while (!feof($fp)) {
      $line = trim(fgets($fp, 256));

      if ($line === "<?php") {
        $lines = array();
        continue;
      }

      if (empty($lines) && $line === "") continue;

      if (substr($line, 0, 3) === "###") {
        if (!empty($lines)) {
          $this->writeTemporaryFile($lines, $num, $fileName);
          $num++;
          $lines = array();
        }

        $fileName = trim(str_replace("#", "", $line));
        continue;
      }

      $lines[] = $line;
    }

    $this->writeTemporaryFile($lines, $num, $fileName);
  }

  private function writeTemporaryFile($lines, $num, $fileName)
  {
    $tmpDir = $this->temporaryPath;

    if ($this->isUpgrade) {
      $path = $tmpDir . DIR_DIVIDER . "{$num}_{$fileName}";
    } else {
      $path = $tmpDir . DIR_DIVIDER . "restores" . DIR_DIVIDER . "restore_" . $num;
    }

    $fp = fopen($path, "w");
    foreach ($lines as $line) {
      fwrite($fp, $line. "\n", 256);
    }

    fclose($fp);
  }

  private function createCustomRestoreFile($upgradeFiles, $version)
  {
    $tmpRestorePath = $this->temporaryPath . DIR_DIVIDER . "restores";
    if (!is_dir($tmpRestorePath)) return;

    $handle = opendir($tmpRestorePath);

    $files = array();
    while (($file = readdir($handle)) !== false) {
      if ($file === "." || $file === "..") continue;
      list (, $num) = explode("_", $file);

      if (is_numeric($num)) {
        $files[$num] = $tmpRestorePath . DIR_DIVIDER . $file;
      }
    }

    $files   = array_reverse($files);
    $restore = MIG_DIR . DIR_DIVIDER . "restores" . DIR_DIVIDER . "restore_{$version}";
    $rfp     = fopen($restore, "w");

    foreach ($files as $file) {
      $fp = fopen($file, "r");
      $fName = getFileName($file);
      list (, $num) = explode("_", $fName);

      $upFile = $upgradeFiles[$num];
      list (, $mdlName, $command) = explode("_", $upFile);
      $command = $mdlName . "_" . $command;
      fwrite($rfp, "########## $command ##########\n");

      while (!feof($fp)) {
        $line = trim(fgets($fp, 256));
        if ($line === "<?php" || $line === "?>") continue;
        fwrite($rfp, $line . "\n");
      }

      fclose($fp);
      unlink($file);
    }

    fclose($rfp);
  }
}

