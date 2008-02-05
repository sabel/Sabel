<?php

/**
 * Sabel_DB_Validate_Config
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Validate_Config
{
  /**
   * @var array
   */
  protected static $messages  = array("maxlength" => "%NAME% must be %MAX% characters or less.",
                                      "minlength" => "%NAME% must be %MIN% characters or more.",
                                      "maximum"   => "%NAME% must be %MAX% or less.",
                                      "minimum"   => "%NAME% must be %MIN% or more.",
                                      "nullable"  => "%NAME% is required.",
                                      "numeric"   => "%NAME% must be a numeric.",
                                      "type"      => "wrong %NAME% format.",
                                      "unique"    => "'%VALUE%'(%NAME%) is unavailable.");
  
  /**
   * @var array
   */
  protected static $customValidators = array();
  
  /**
   * @param array $messages
   *
   * @return void
   */
  public static function setMessages(array $messages)
  {
    self::$messages = $messages;
  }
  
  /**
   * @return array
   */
  public static function getMessages()
  {
    return self::$messages;
  }
  
  /**
   * @param array $setting
   *
   * @return void
   */
  public static function addValidator($setting)
  {
    $cvs =& self::$customValidators;
    
    $colName = $setting["column"];
    $models  = $setting["model"];
    if (is_string($models)) $models = (array)$models;
    
    $arguments = null;
    if (isset($setting["arguments"])) {
      $arguments = $setting["arguments"];
    }
    
    foreach ($models as $mdlName) {
      if ($arguments) {
        $cvs[$mdlName][$colName][] = array($setting["function"], $arguments);
      } else {
        $cvs[$mdlName][$colName][] = $setting["function"];
      }
    }
  }
  
  /**
   * @return array
   */
  public static function getValidators()
  {
    return self::$customValidators;
  }
  
  /**
   * @return array
   */
  public static function clearValidators()
  {
    $validators = self::$customValidators;
    self::$customValidators = array();
    
    return $validators;
  }
}
