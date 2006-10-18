<?php

class Sabel_Auth_Security extends Sabel_Auth_User
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