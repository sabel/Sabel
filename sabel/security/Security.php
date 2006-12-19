<?php

class Sabel_Security_Security
{
  protected $storatge = null;
  protected $authorizer = null;
  protected static $instance = null;
  
  private function __construct()
  {
    $this->storage = Sabel_Storage_Session::create();
  }
  
  public static function create()
  {
    if (self::$instance === null) self::$instance = new self();
    return self::$instance;
  }
  
  public function registAuthorizer($authorizer)
  {
    $this->authorizer = $authorizer;
  }
  
  public function getAuthorizer()
  {
    return $this->authorizer;
  }
  
  public function getIdentity()
  {
    if ($this->isAuthorized()) {
      return $this->storage->read('SABEL_AUTH_IDENTITY');
    } else {
      return false;
    }
  }
  
  public function overwriteIdentity($identity)
  {
    $this->storage->write('SABEL_AUTH_IDENTITY', $identity);
  }
  
  public function authorize($identity, $password)
  {
    $result = $this->authorizer->authorize($identity, $password);
    dump($result);
    if ($result === true) {
      $this->storage->write('SABEL_AUTHORIZED', 'true');
      $this->storage->write('SABEL_AUTH_IDENTITY', $identity);
      return true;
    } else {
      return false;
    }
  }
  
  public function unauthorize()
  {
    $this->storage->delete('SABEL_AUTHORIZED');
  }
  
  public function isAuthorized()
  {
    if ($this->storage->read('SABEL_AUTHORIZED') === 'true') {
      return true;
    } else {
      return false;
    }
  }
}
