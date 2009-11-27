<?php

class Cache
{
  public static function create($backend)
  {
    if ((ENVIRONMENT & PRODUCTION) > 0) {
      switch ($backend) {
      case "file":
        $storage = Sabel_Cache_File::create(CACHE_DIR_PATH . DS . "data");
        break;
      case "apc":
        $storage = Sabel_Cache_Apc::create();
        break;
      case "memcache":
        $storage = Sabel_Cache_Memcache::create(/* $host = "localhost", $port = 11211 */);
        // $storage->addServer(/* $host, $port = 11211, $weight = 1 */);
        break;
      default:
        $message = __METHOD__ . "() invalid cache backend.";
        throw new Exception($message);
      }
      
      return $storage;
    } else {
      return Sabel_Cache_Null::create();
    }
  }
}
