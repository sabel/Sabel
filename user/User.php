<?php

class User
{
  protected $attributes = array();

  public function __construct($uniqueKey = null)
  {
    $storage = Storage::create('SessionStorage');
    $this->attributes = $storage->read('Community.UserAttributes' . $uniqueKey);
    if ($this->attributes == null) {
      $this->attributes = null;
    }
  }

  public function __destruct()
  {
    $storage = Storage::create('SessionStorage');
    $storage->write('Community.UserAttributes' . $this->uniqueKey, $this->attributes);
  }

  public function addAttribute($key, $value)
  {
    $this->attributes[$key] = $value;
  }

  public function removeAttribute($key)
  {
    unset($this->attributes[$key]);
  }

  public function getAttribute($key)
  {
    return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
  }
}

class SecurityUser extends User
{
  private static $instance;

  protected $credentials = array();
  protected $authorized = false;
  protected $uniqueKey = null;

  const AUTHORIZE_NAMESPACE = 'Community.AuthorizeFlag';

  public function __construct($uniqueKey)
  {
    $this->uniqueKey = $uniqueKey;
    parent::__construct($this->uniqueKey);

    $storage = Storage::create('SessionStorage');
    $this->authorized = $storage->read(self::AUTHORIZE_NAMESPACE . $uniqueKey);

    if ($this->authorized == null) {
      $this->authorized = false;
    }
  }

  public function __destruct()
  {
    parent::__destruct();
    $storage = Storage::create('SessionStorage');
    $storage->write(self::AUTHORIZE_NAMESPACE . $this->uniqueKey, $this->authorized);
  }

  public static function create($uniqueKey = null)
  {

    if (!isset(self::$instance)) {
      self::$instance = new self(($uniqueKey != null) ? $uniqueKey : null);
    }
    return self::$instance;
  }

  public function authorize()
  {
    $this->authorized = true;
  }

  public function unAuthorize()
  {
    $this->authorized = false;
  }

  public function isAuthorized()
  {
    return $this->authorized;
  }
}

?>