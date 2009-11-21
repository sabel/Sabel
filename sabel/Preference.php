<?php

/**
 * Preference
 *
 * @abstract
 * @category   Preference
 * @package    org.sabel.preference
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Preference
{
  private $backend = null;

  public static function create(Sabel_Config $config = null)
  {
    if (!$config instanceof Sabel_Config) {
      return new self();
    }

    $arrayConfig = $config->configure();

    if (!is_array($arrayConfig)) {
      $arrayConfig = array();
    }

    if (isset($arrayConfig["backend"])) {
      $backendClass = $arrayConfig["backend"];

      if (!class_exists($backendClass)) {
        $msg = "specified backend class " . $backendClass . " is not found in any classpath";
        throw new Sabel_Exception_ClassNotFound($msg);
      }

      $backend = new $backendClass($arrayConfig);

      return new self($backend);
    }
  }

  public function __construct($backend = null)
  {
    if ($backend == null) {
      $backend = new Sabel_Preference_Xml();
    }

    $this->backend = $backend;
  }

  public function setInt($key, $value)
  {
    if (!is_int($value)) {
      $value = (int) $value;
    }

    $this->backend->set($key, (int) $value);
  }

  public function getInt($key, $default = null)
  {
    if ($default !== null && !is_int($default)) {
      $default = (int) $default;
    }

    $result = $this->get($key, $default);

    if (!is_int($result)) {
      return (int) $result;
    }

    return $result;
  }

  public function setString($key, $value)
  {
    if (!is_string($value)) {
      $value = (string) $value;
    }

    $this->backend->set($key, $value);
  }

  public function getString($key, $default = null)
  {
    if ($default !== null && !is_string($default)) {
      $default = (string) $default;
    }

    $result = $this->get($key, $default);

    if (!is_string($result)) {
      return (string) $result;
    }

    return $result;
  }

  private function get($key, $default)
  {
    if ($default !== null) {
      $this->backend->set($key, $default);
      return $default;
    }

    if (!$this->backend->has($key) && $default === null) {
      throw new Sabel_Exception_Runtime("preference ${key} not found");
    }

    return $this->backend->get($key);
  }

  /**
   * delete preference
   *
   * @param string $key
   * @return mixed
   */
  public function delete($key)
  {
    if ($this->backend->has($key)) {
      $removedValue = $this->backend->get($key);

      $this->backend->delete($key);

      return $removedValue;
    }
  }
}
