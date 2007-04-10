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

  protected static $messages = array("length"   => "Too long. '%s'",
                                     "maximum"  => "Too large. '%s'",
                                     "nullable" => "Should not null. '%s'",
                                     "type"     => "Wrong Format. '%s'");

  protected static $localizedName = array();
  protected static $postProcesses = array();
  protected static $customValidations = array();

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

  public static function registPostProcessing($process)
  {
    self::$postProcesses[] = $process;
  }

  public static function getPostProcesses()
  {
    return self::$postProcesses;
  }

  public static function registCustomValidation($custom)
  {
    self::$customValidations[] = $custom;
  }

  public static function getCustomValidations()
  {
    return self::$customValidations;
  }

  public static function setLocalizedName($mdlName, $names)
  {
    self::$localizedName[$mdlName] = $names;
  }

  public static function getLocalizedName()
  {
    return self::$localizedName;
  }

  public static function clearCustomValidations()
  {
    self::$customValidations = array();
  }
}
