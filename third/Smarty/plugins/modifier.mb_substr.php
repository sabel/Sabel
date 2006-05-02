<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty mb_substr modifier plugin
 *
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @param boolean
 * @return string
 */
function smarty_modifier_mb_substr($string, $length = 80, $postfix = '...')
{
  if ($length == 0)
    return '';

  if (mb_strlen($string, 'EUC-JP') > $length) {
    return mb_substr($string, 0, $length, 'EUC-JP').$postfix;
  } else {
    return $string;
  }
}

?>
