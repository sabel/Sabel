<?php

class User
{
  protected $attributes = array();

  public function __construct()
  {
    $storage = Storage::create('SessionStorage');
    $this->attributes = $storage->read('Community.UserAttributes');
    if ($this->attributes == null) {
      $this->attributes = null;
    }
  }

  public function __destruct()
  {
    $storage = Storage::create('SessionStorage');
    $storage->write('Community.UserAttributes', $this->attributes);
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
  protected $credentials = array();
  protected $authorized = false;
  private static $instance;

  const AUTHORIZE_NAMESPACE = 'Community.AuthorizeFlag';

  public function __construct()
  {
    parent::__construct();
    $storage = Storage::create('SessionStorage');
    $this->authorized = $storage->read(self::AUTHORIZE_NAMESPACE);

    if ($this->authorized == null) {
      $this->authorized = false;
    }
  }

  public function __destruct()
  {
    parent::__destruct();
    $storage = Storage::create('SessionStorage');
    $storage->write(self::AUTHORIZE_NAMESPACE, $this->authorized);
  }

  public static function create()
  {
    if (!isset(self::$instance)) {
      self::$instance = new self();
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