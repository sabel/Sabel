<?php ob_start(); ?>

<html>
<body>

<?php

$start = microtime();

$sabelfilepath = __FILE__;

define('SABEL_BASE', '/opt/local/lib/php/PEAR/');
require_once(SABEL_BASE.'Sabel/Sabel.php');
SabelContext::getController()->dispatch();

$end = microtime();

print "<br/>\n";
print $end - $start;

?>

</body>
</html>

<? ob_flush(); ?>