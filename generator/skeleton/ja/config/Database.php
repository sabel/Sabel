<?php

class Config_Database implements Sabel_Config
{
  public function configure()
  {
    switch (ENVIRONMENT) {
      case PRODUCTION:
        $params = array("default" => array(
                          "package"  => "sabel.db.*",
                          "host"     => "localhost",
                          "database" => "dbname",
                          "user"     => "user",
                          "password" => "password")
                       );
        break;
        
      case TEST:
        $params = array("default" => array(
                          "package"  => "sabel.db.*",
                          "host"     => "localhost",
                          "database" => "dbname",
                          "user"     => "user",
                          "password" => "password")
                       );
        break;
        
      case DEVELOPMENT:
        $params = array("default" => array(
                          "package"  => "sabel.db.*",
                          "host"     => "localhost",
                          "database" => "dbname",
                          "user"     => "user",
                          "password" => "password")
                       );
        break;
    }
    
    return $params;
  }
}

Sabel_Db_Validate_Config::setMessages(
  array("maxlength" => "%NAME%は%MAX%文字以下で入力してください",
        "minlength" => "%NAME%は%MIN%文字以上で入力してください",
        "maximum"   => "%NAME%は%MAX%以上を入力してください",
        "minimum"   => "%NAME%は%MIN%以下を入力してください",
        "nullable"  => "%NAME%を入力してください",
        "numeric"   => "%NAME%は数値で入力してください",
        "type"      => "%NAME%の形式が不正です",
        "unique"    => "%NAME%'%VALUE%'は既に使用されています")
);
