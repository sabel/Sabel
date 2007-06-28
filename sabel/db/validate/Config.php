<?php

/**
 * Sabel_DB_Validate_Config
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Validate_Config
{
  protected static $datetimeRegex = '/^[12]\d{3}(-|\/)(0?[1-9]|1[0-2])(-|\/)(0?[1-9]|[12]\d|3[01])(T| )((0?|1)[\d]|2[0-3]):(0?[\d]|[1-5][\d]):(0?[\d]|[1-5][\d])( )?((\-|\+)([01][\d]|2[0-3])(:)?(0?[\d]|[1-5][\d])?)?$/';

  protected static $messages = array("length"   => "%s is too long.",
                                     "maximum"  => "%s is too large.",
                                     "nullable" => "please enter a %s.",
                                     "type"     => "wrong %s format.",
                                     "unique"   => "%s: '%s' is unavailable.");

  protected static $localizedName = array();
  protected static $customValidators = array();

  public static function getConfigs()
  {
    return array("messages"      => self::$messages,
                 "localizedName" => self::$localizedName,
                 "datetimeRegex" => self::$datetimeRegex);
  }

  public static function setMessages($messages)
  {
    self::$messages = $messages;
  }

  public static function getMessages()
  {
    return self::$messages;
  }

  public static function setDatetimeRegex($regex)
  {
    self::$datetimeRegex = $regex;
  }

  public static function getDatetimeRegex()
  {
    return self::$datetimeRegex;
  }

  public static function addValidator($custom)
  {
    $cvs =& self::$customValidators;

    $colName = $custom["column"];
    $models  = $custom["model"];
    if (is_string($models)) $models = (array)$models;

    $arguments = null;
    if (isset($custom["arguments"])) {
      $arguments = $custom["arguments"];
      if (!is_array($arguments)) $arguments = (array)$arguments;

      if (count($arguments) !== count($models)) {
        throw new Exception("invalid parameter count.");
      }
    }

    foreach ($models as $i => $mdlName) {
      if ($arguments) {
        $cvs[$mdlName][$colName][] = array($custom["function"], $arguments[$i]);
      } else {
        $cvs[$mdlName][$colName][] = $custom["function"];
      }
    }
  }

  public static function getCustomValidators()
  {
    return self::$customValidators;
  }

  public static function setLocalizedName($mdlName, $names)
  {
    self::$localizedName[$mdlName] = $names;
  }

  public static function getLocalizedName()
  {
    return self::$localizedName;
  }

  public static function clearCustomValidators()
  {
    $validators = self::$customValidators;
    self::$customValidators = array();

    return $validators;
  }
}
