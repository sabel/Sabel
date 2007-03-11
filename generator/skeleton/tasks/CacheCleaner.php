<?php

class CacheCleaner extends Sakle
{
  public function execute()
  {
    if (ini_get('apc.enable_cli') === '0') {
      $this->printMessage('not apc.enabled_cli=1', self::MSG_ERR);
      $this->stop();
    }
    
    $apc = new Sabel_Cache_Apc();
    if ($apc->delete('readables')) {
      $this->printMessage('delete cache');
    } else {
      $this->printMessage('not cached', self::MSG_WARN);
    }
  }
}