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
  protected static $datetimeRegex = '/^[12]\d{3}-(0?[1-9]|1[0-2])-(0?[1-9]|[12]\d|3[01]) ((0?|1)[\d]|2[0-3]):(0?[\d]|[1-5][\d]):(0?[\d]|[1-5][\d])$/';
  protected static $dateRegex = '/^[12]\d{3}-(0?[1-9]|1[0-2])-(0?[1-9]|[12]\d|3[01])$/';
  
  protected static $messages  = array("maxlength" => "%NAME% should be %MAX% characters or less.",
                                      "minlength" => "%NAME% should be %MIN% characters or more.",
                                      "maximum"   => "%NAME% should be %MAX% or less.",
                                      "minimum"   => "%NAME% should be %MIN% or more.",
                                      "nullable"  => "should input the %NAME%.",
                                      "numeric"   => "%NAME% should be a numeric.",
                                      "type"      => "wrong %NAME% format.",
                                      "unique"    => "'%VALUE%'(%NAME%) is unavailable.");

  protected static $customValidators = array();

  public static function getConfigs()
  {
    return array("messages"      => self::$messages,
                 "datetimeRegex" => self::$datetimeRegex,
                 "dateRegex"     => self::$dateRegex);
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

  public static function setDateRegex($regex)
  {
    self::$dateRegex = $regex;
  }

  public static function getDateRegex()
  {
    return self::$dateRegex;
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
    }

    foreach ($models as $mdlName) {
      if ($arguments) {
        $cvs[$mdlName][$colName][] = array($custom["function"], $arguments);
      } else {
        $cvs[$mdlName][$colName][] = $custom["function"];
      }
    }
  }

  public static function getValidators()
  {
    return self::$customValidators;
  }

  public static function clearValidators()
  {
    $validators = self::$customValidators;
    self::$customValidators = array();

    return $validators;
  }
}
