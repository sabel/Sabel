<?php

candidate("default",
          ":module/:controller/:action/:id",
          array("default" => array(":module"     => "index",
                                   ":controller" => "index",
                                   ":action"     => "index",
                                   ":id"         => null)));