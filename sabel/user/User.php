<?php

class User
{
  protected $attributes = array();
  protected $uniqueKey  = null;

  public function __construct($uniqueKey = null)
  {
    $this->uniqueKey = $uniqueKey;

    $storage = Sabel_Storage_Session::create();
    $this->attributes = $storage->read('Community.UserAttributes' . $uniqueKey);
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
  protected $authorized  = false;

  const AUTHORIZE_NAMESPACE = 'Community.AuthorizeFlag';

  public function __construct($uniqueKey)
  {
    parent::__construct($uniqueKey);

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
      self::$instance = new self($uniqueKey);
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