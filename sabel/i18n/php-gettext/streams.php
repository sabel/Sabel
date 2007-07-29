<?php

/*
  Copyright (c) 2003, 2005 Danilo Segan <danilo@kvota.net>.

  This file is part of PHP-gettext.

  PHP-gettext is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  PHP-gettext is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with PHP-gettext; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class StreamReader
{
  public function read($bytes)
  {
    return false;
  }

  public function seekto($position)
  {
    return false;
  }

  public function currentpos()
  {
    return false;
  }

  public function length()
  {
    return false;
  }
}

class StringReader
{
  private $_pos;
  private $_str;

  public function StringReader($str = "")
  {
    $this->_str = $str;
    $this->_pos = 0;
  }

  public function read($bytes)
  {
    $data = substr($this->_str, $this->_pos, $bytes);
    $this->_pos += $bytes;

    if (strlen($this->_str) < $this->_pos) {
      $this->_pos = strlen($this->_str);
    }

    return $data;
  }

  public function seekto($pos)
  {
    $this->_pos = $pos;
    if (strlen($this->_str) < $this->_pos) {
      $this->_pos = strlen($this->_str);
    }

    return $this->_pos;
  }

  public function currentpos()
  {
    return $this->_pos;
  }

  public function length()
  {
    return strlen($this->_str);
  }
}


class FileReader
{
  private $_pos;
  private $_fd;
  private $_length;

  public function FileReader($filePath)
  {
    if (is_readable($filePath)) {
      $this->_pos    = 0;
      $this->_length = filesize($filePath);
      $this->_fd     = fopen($filePath, "rb");
    } else {
      throw new Sabel_Exception_Runtime("File not readable. '{$filePath}'");
    }
  }

  public function read($bytes)
  {
    if ($bytes) {
      $data = "";
      fseek($this->_fd, $this->_pos);

      // PHP 5.1.1 does not read more than 8192 bytes in one fread()
      // the discussions at PHP Bugs suggest it's the intended behaviour
      while ($bytes > 0) {
        $chunk  = fread($this->_fd, $bytes);
        $data  .= $chunk;
        $bytes -= strlen($chunk);
      }

      $this->_pos = ftell($this->_fd);

      return $data;
    } else {
      return "";
    }
  }

  public function seekto($pos)
  {
    fseek($this->_fd, $pos);
    $this->_pos = ftell($this->_fd);
    return $this->_pos;
  }

  public function currentpos()
  {
    return $this->_pos;
  }

  public function length()
  {
    return $this->_length;
  }

  public function close()
  {
    fclose($this->_fd);
  }
}

class CachedFileReader extends StringReader
{
  public function CachedFileReader($filePath)
  {
    if (is_readable($filePath)) {
      $length = filesize($filePath);
      $fd = fopen($filePath, "rb");
      $this->_str = fread($fd, $length);
      fclose($fd);
    } else {
      throw new Sabel_Exception_Runtime("File not readable. '{$filePath}'");
    }
  }
}
