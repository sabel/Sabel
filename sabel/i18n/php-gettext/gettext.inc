<?php

/*
  Copyright (c) 2005 Steven Armstrong <sa at c-area dot ch>

  Drop in replacement for native gettext.

  This file is part of PHP-gettext.

  PHP-gettext is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  PHP-gettext is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with PHP-gettext; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
  LC_CTYPE    : 0
  LC_NUMERIC  : 1
  LC_TIME     : 2
  LC_COLLATE  : 3
  LC_MONETARY : 4
  LC_MESSAGES : 5
  LC_ALL      : 6
*/

Sabel::fileUsing(dirname(__FILE__) . DS . "streams.php");
Sabel::fileUsing(dirname(__FILE__) . DS . "gettext.php");

global $text_domains, $default_domain, $LC_CATEGORIES, $EMULATEGETTEXT, $CURRENTLOCALE;

$text_domains   = array();
$default_domain = "messages";
$EMULATEGETTEXT = 0;
$CURRENTLOCALE  = "";
$LC_CATEGORIES  = array("LC_CTYPE",
                        "LC_NUMERIC",
                        "LC_TIME",
                        "LC_COLLATE",
                        "LC_MONETARY",
                        "LC_MESSAGES",
                        "LC_ALL");

function _($msgid)
{
  return _encode(_get_reader()->translate($msgid));
}

function gettext($msgid)
{
  return _($msgid);
}

function __($msgid)
{
  return _($msgid);
}

function _gettext($msgid)
{
  return _($msgid);
}

/**
 * Utility function to get a StreamReader for the given text domain.
 */
function _get_reader($domain = null, $category = 5, $enable_cache = true) {
  global $text_domains, $default_domain;
  
  if ($domain === null) $domain = $default_domain;
  if (!isset($text_domains[$domain]->l10n)) {
    $locale = Sabel_I18n_Gettext::getInstance()->getBrowser()->getLocale();
    
    $path = $text_domains[$domain]->path;
    $filePath = $path . $locale . DS . "LC_MESSAGES" . DS . "{$domain}.mo";
    
    if (!is_file($filePath) && !IS_WIN && strpos($locale, "_") !== false) {
      list ($lang) = explode("_", $locale);
      $filePath = $path . $lang . DS . "LC_MESSAGES" . DS . "{$domain}.mo";
    }
    
    $input = (is_file($filePath)) ? new FileReader($filePath) : null;
    $text_domains[$domain]->l10n = new gettext_reader($input, $enable_cache);
  }
  
  return $text_domains[$domain]->l10n;
}

/**
 * Returns whether we are using our emulated gettext API or PHP built-in one.
 */
function locale_emulation()
{
  global $EMULATEGETTEXT;
  return $EMULATEGETTEXT;
}

/**
 * Convert the given string to the encoding set by bind_textdomain_codeset.
 */
function _encode($text)
{
  global $text_domains, $default_domain;
  
  if (isset($text_domains[$default_domain]->codeset)) {
    $source_encoding = _get_internal_encoding();
    $target_encoding = $text_domains[$default_domain]->codeset;
    return mb_convert_encoding($text, $target_encoding, $source_encoding);
  } else {
    return $text;
  }
}

function _get_internal_encoding()
{
  static $encoding = null;
  
  if ($encoding === null) {
    return $encoding = ini_get("mbstring.internal_encoding");
  } else {
    return $encoding;
  }
}

/**
 * Sets the path for a domain.
 */
function _bindtextdomain($domain, $path)
{
  global $text_domains;
  
  if (substr($path, -1, 1) !== DS) $path .= DS;
  
  if (isset($text_domains[$domain]) && is_object($text_domains[$domain])) {
    $text_domains[$domain]->path = $path;
    unset($text_domains[$domain]->l10n);
  } else {
    $object = new stdClass();
    $object->path = $path;
    $text_domains[$domain] = $object;
  }
}

/**
 * Specify the character encoding in which the messages
 * from the DOMAIN message catalog will be returned.
 */
function _bind_textdomain_codeset($domain, $codeset)
{
  global $text_domains;
  
  if (isset($text_domains[$domain]) && is_object($text_domains[$domain])) {
    $text_domains[$domain]->codeset = $codeset;
  } else {
    $object = new stdClass();
    $object->codeset = $codeset;
    $text_domains[$domain] = $object;
  }
}

/**
 * Sets the default domain.
 */
function _textdomain($domain) {
  global $default_domain;
  $default_domain = $domain;
}

function bindtextdomain($domain, $path)
{
  return _bindtextdomain($domain, $path);
}

function bind_textdomain_codeset($domain, $codeset)
{
  return _bind_textdomain_codeset($domain, $codeset);
}

function textdomain($domain)
{
  return _textdomain($domain);
}

function ngettext($single, $plural, $number)
{
  return _ngettext($single, $plural, $number);
}

function dgettext($domain, $msgid)
{
  return _dgettext($domain, $msgid);
}

function dngettext($domain, $single, $plural, $number)
{
  return _dngettext($domain, $single, $plural, $number);
}

function dcgettext($domain, $msgid, $category)
{
  return _dcgettext($domain, $msgid, $category);
}

function dcngettext($domain, $single, $plural, $number, $category)
{
  return _dcngettext($domain, $single, $plural, $number, $category);
}

/**
 * Plural version of gettext.
 */
function _ngettext($single, $plural, $number)
{
  return _encode(_get_reader()->ngettext($single, $plural, $number));
}

/**
 * Override the current domain.
 */
function _dgettext($domain, $msgid)
{
  return _get_reader($domain)->translate($msgid);
}

/**
 * Plural version of dgettext.
 */
function _dngettext($domain, $single, $plural, $number)
{
  return _get_reader($domain)->ngettext($single, $plural, $number);
}

/**
 * Overrides the domain and category for a single lookup.
 */
function _dcgettext($domain, $msgid, $category)
{
  return _get_reader($domain, $category)->translate($msgid);
}

/**
 * Plural version of dcgettext.
 */
function _dcngettext($domain, $single, $plural, $number, $category)
{
  return _get_reader($domain, $category)->ngettext($single, $plural, $number);
}
