<?php

require_once 'PEAR/PackageFileManager2.php';

PEAR::setErrorHandling(PEAR_ERROR_DIE);

$packagexml = new PEAR_PackageFileManager2();
$packagexml->setOptions(
  array('filelistgenerator' => 'file',
        'packagedirectory'  => dirname(__FILE__),
        'baseinstalldir'    => '/',
        'simpleoutput'      => true));
$packagexml->setPackageType('php');
$packagexml->addRelease();
$packagexml->setPackage('alphatmp');
$packagexml->setChannel('pear.sabel.jp');
$packagexml->setReleaseVersion('0.1.2');
$packagexml->setAPIVersion('0.1.0');
$packagexml->setReleaseStability('alpha');
$packagexml->setAPIStability('alpha');
$packagexml->setSummary('web application development framework.');
$packagexml->setDescription('web application development framework.');
$packagexml->setNotes('Initial release');
$packagexml->setPhpDep('5.1.0');
$packagexml->setPearinstallerDep('1.4.0');
//$packagexml->addPackageDepWithChannel('required', 'PEAR_PackageFileManager', 'pear.php.net', '1.5.1');
$packagexml->addMaintainer('lead', 'morireo', 'Mori Reo', 'mori.reo@sabel.jp');
$packagexml->addMaintainer('developer', 'hamanaka', 'Hamanaka Kazuhiro', 'hamanaka.kazuhiro@sabel.jp');
$packagexml->addMaintainer('developer', 'ebine', 'Ebine Yutaka', 'ebine.yutaka@sabel.jp');
$packagexml->setLicense('BSD License', 'http://www.opensource.org/licenses/bsd-license.php');
$packagexml->addReplacement('bin/sabel', 'pear-config', '@PHP-BIN@', 'php_bin');
$packagexml->addInstallAs('bin/sabel', '/usr/local/bin/sabel');
//$packagexml->addGlobalReplacement('package-info', '@PEAR-VER@', 'version');
//$packagexml->addGlobalReplacement('pear-config', '@PHP-BIN@', 'php_bin');
$packagexml->generateContents();
$packagexml->writePackageFile();