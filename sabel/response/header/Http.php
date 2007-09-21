<?php

class Sabel_Response_Header_Http
{
  public function output($response)
  {
    if ($response->hasContentType()) {
      header("Content-Type: " . $response->getContentType());
    }
    
    if ($response->hasHeaders()) {
      foreach ($response->getHeaders() as $message => $value) {
        header(ucfirst($message) . ": " . $value);
      }
    }
    
    if ($response->getLocation()) {
      l("[Core] Header location: " . var_export($response->getLocation(), 1));
    }
    
    if ($response->isForbidden()) {
      header("HTTP/1.0 403 Forbidden");
    } elseif ($response->isNotFound()) {
      header("HTTP/1.0 404 Not Found");
    } elseif ($response->isServerError()) {
      header("HTTP/1.0 500 Internal Server Error");
    } elseif ($response->isRedirected()) {
      header("Location: " . $response->getLocation());
    } elseif ($response->isNotModified()) {
      header("HTTP/1.0 304 Not Modified");
      exit;
    }
  }
}
