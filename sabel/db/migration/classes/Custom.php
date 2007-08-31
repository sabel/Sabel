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
  private $isUpgrade    = false;
  private $temporaryDir = "";
  private $restorePath  = "";

  public function __construct()
  {
    $this->temporaryDir = $tp = MIG_DIR . DS . "temporary";
    if (!is_dir($tp)) mkdir($tp);
  }

  public function execute($migClassName, $version, $file)
  {
    $type = Sabel_DB_Migration_Manager::getApplyMode();

    if ($type === "upgrade") {
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

    foreach (getMigrationFiles($temporaryDir) as $file) {
      list ($num) = explode("_", $file);
      $upgradeFiles[$num] = $file;

      file_put_contents($restoreDir . DS . "restore_{$num}", "\n");

      $path = $temporaryDir . DS . $file;
      $ins = new $migClassName($path, "upgrade", $temporaryDir);
      $ins->execute();

      unlink($path);
    }

    $this->createCustomRestoreFile($upgradeFiles, $version);
  }

  public function prepareDowngrade($restoreFile)
  {
    $this->isUpgrade = false;

    $fp = fopen($restoreFile, "r");
    $this->splitFiles($fp);
    fclose($fp);

    $this->isUpgrade = true;
    list (, $num) = explode("_", getFileName($restoreFile));
    $fp = fopen(MIG_DIR . DS . "{$num}_mix.php", "r");
    $this->splitFiles($fp);
    fclose($fp);

    return $this->temporaryDir;
  }

  public function doDowngrade($migClassName)
  {
    $temporaryDir = $this->temporaryDir;
    $files = array_reverse(getMigrationFiles($temporaryDir));
    $fileNum = count($files) + 1;
    $prefix  = $temporaryDir . DS;

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
      $path = $tmpDir . DS . "{$num}_{$fName}";
    } else {
      $dir  = $this->getTemporaryRestoresDir();
      $path = $dir . DS . "restore_" . $num;
    }

    file_put_contents($path, implode($lines, "\n"));
  }

  private function createCustomRestoreFile($upgradeFiles, $version)
  {
    $restoreDir = $this->getTemporaryRestoresDir();

    $files = array();
    foreach (scandir($restoreDir) as $file) {
      if (preg_match("/\.+$/", $file)) continue;
      list (, $num) = explode("_", $file);

      if (is_numeric($num)) {
        $files[$num] = $restoreDir. DS . $file;
      }
    }

    $files = array_reverse($files);
    $dir   = MIG_DIR . DS . "restores";
    if (!is_dir($dir)) mkdir($dir);

    $restore = $dir . DS . "restore_{$version}";
    $rfp     = fopen($restore, "w");

    foreach ($files as $file) {
      $fp    = fopen($file, "r");
      $fName = getFileName($file);

      list (, $num) = explode("_", $fName);
      list (, $mdlName, $command) = explode("_", $upgradeFiles[$num]);

      $command = $mdlName . "_" . $command;
      fwrite($rfp, "### $command ###\n");

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

  private function getTemporaryRestoresDir()
  {
    $dir = $this->temporaryDir . DS . "restores";
    if (!is_dir($dir)) mkdir($dir);

    return $dir;
  }
}
