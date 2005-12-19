<?php
//header('Content-type: text/plain');
ini_set('error_reporting', E_ALL|E_STRICT);
ini_set('display_errors', true);
ini_set('html_errors', false);

function dump(&$var, $label = null) 
{
	if ($label) {
		echo $label . " ";
	}
	ob_start();
	var_dump($var);
	$output = ob_get_clean();
	$output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
	echo $output;
}

// add to the include_path
$add = realpath(dirname(__FILE__) . '/../../');
ini_set('include_path', ini_get('include_path') . ":$add");

// make sure we have Savant ;-)
require_once 'Savant3.php';
?>