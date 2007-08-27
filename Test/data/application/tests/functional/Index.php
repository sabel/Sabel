<?php

class Functional_Index extends Sabel_Test_Functional
{
  public function testRequest()
  {
    $request = new Sabel_Request_Object();
    $request->get("index/index")->value("value", "index");
    $response = $this->request($request);
    $this->eq("index", $response->getAttribute("value"));
  }
}
