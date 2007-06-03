<?php

function a($param, $anchor)
{
  $aCreator = new Sabel_View_Uri();
  return $aCreator->hyperlink($param, $anchor);
}

function ah($param, $anchor)
{
  return a($param, htmlspecialchars($anchor));
}

function uri($param)
{
  $aCreator = new Sabel_View_Uri();
  return $aCreator->uri($param);
}
