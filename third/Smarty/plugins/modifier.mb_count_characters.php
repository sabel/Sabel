<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty mb_count_characters modifier plugin
 *
 * @author   Hamanakaka
 * @param string
 * @param boolean include whitespace in the character count
 * @return integer
 */
function smarty_modifier_mb_count_characters($string, $include_spaces = false)
{
  if (! $include_spaces)
    $string = preg_replace('/[\s　]/', '', $string);

  return(mb_strlen($string, 'EUC-JP'));
}

?>
