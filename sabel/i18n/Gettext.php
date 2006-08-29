<?php

/**
 * class for internationalization.
 *
 * @package org.sabel.I18n
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_I18n_Gettext
{
  public static function init()
  {
    if (!function_exists('gettext')) return false;
    
    $language = 'en';
    if (stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'ja') !== false) {
      $language = 'ja_JP';
    }
    
    putenv("LANGUAGE={$language}");
    $domain = 'messages';
    $locales = RUN_BASE . '/locale';
    bindtextdomain($domain, $locales);
    textdomain($domain);
  }
}