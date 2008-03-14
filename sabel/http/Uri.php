<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Uri
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Http.php 8064 2008-02-16 10:58:39Z thomas $
 */

/**
 * @category   Http
 * @package    org.sabel.http
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Sabel_Http_Uri  /* Zend_Uri_Http */
{
    /**
     * Scheme of this URI (http, ftp, etc.)
     * @var string
     */
    protected $_scheme = '';

    /**
     * URI parts are divided among these instance variables
     */
    protected $_username    = '';
    protected $_password    = '';
    protected $_host        = '';
    protected $_port        = '';
    protected $_path        = '';
    protected $_query       = '';
    protected $_fragment    = '';

    /**
     * Regular expression grammar rules for validation; values added by constructor
     */
    protected $_regex = array();

    /**
     * Constructor accepts a string $scheme (e.g., http, https) and a scheme-specific part of the URI
     * (e.g., example.com/path/to/resource?query=param#fragment)
     *
     * @param string $scheme
     * @param string $schemeSpecific
     * @throws Sabel_Exception_Runtime
     * @return void
     */
    public function __construct($uri)
    {
        /**
         * Separate the scheme from the scheme-specific parts
         * @link http://www.faqs.org/rfcs/rfc2396.html
         */
        $uri = explode(":", $uri, 2);
        $scheme = strtolower($uri[0]);
        $schemeSpecific = isset($uri[1]) ? $uri[1] : '';

        if (!strlen($scheme)) {
            throw new Sabel_Exception_Runtime('An empty string was supplied for the scheme');
        }

        // Security check: $scheme is used to load a class file, so only alphanumerics are allowed.
        if (!ctype_alnum($scheme)) {
            throw new Sabel_Exception_Runtime('Illegal scheme supplied, only alphanumeric characters are permitted');
        }

        if ($scheme !== "http" && $scheme !== "https") {
            throw new Sabel_Exception_Runtime("Scheme \"$scheme\" is not supported");
        }

        // Set the scheme
        $this->_scheme = $scheme;

        // Set up grammar rules for validation via regular expressions. These
        // are to be used with slash-delimited regular expression strings.
        $this->_regex['alphanum']   = '[^\W_]';
        $this->_regex['escaped']    = '(?:%[\da-fA-F]{2})';
        $this->_regex['mark']       = '[-_.!~*\'()\[\]]';
        $this->_regex['reserved']   = '[;\/?:@&=+$,]';
        $this->_regex['unreserved'] = '(?:' . $this->_regex['alphanum'] . '|' . $this->_regex['mark'] . ')';
        $this->_regex['segment']    = '(?:(?:' . $this->_regex['unreserved'] . '|' . $this->_regex['escaped']
                                    . '|[:@&=+$,;])*)';
        $this->_regex['path']       = '(?:\/' . $this->_regex['segment'] . '?)+';
        $this->_regex['uric']       = '(?:' . $this->_regex['reserved'] . '|' . $this->_regex['unreserved'] . '|'
                                    . $this->_regex['escaped'] . ')';

        // If no scheme-specific part was supplied, the user intends to create
        // a new URI with this object.  No further parsing is required.
        if (strlen($schemeSpecific) == 0) {
            return;
        }

        // Parse the scheme-specific URI parts into the instance variables.
        $this->_parseUri($schemeSpecific);

        // Validate the URI
        if (!$this->valid()) {
            throw new Sabel_Exception_Runtime('Invalid URI supplied');
        }
    }

    /**
     * Return a string representation of this URI.
     *
     * @see     getUri()
     * @return  string
     */
    public function __toString()
    {
        return $this->getUri();
    }

    /**
     * Convenience function, checks that a $uri string is well-formed
     * by validating it but not returning an object.  Returns TRUE if
     * $uri is a well-formed URI, or FALSE otherwise.
     *
     * @param string $uri
     * @return boolean
     */
    public static function check($uri)
    {
        try {
            $uri = new self($uri);
        } catch (Exception $e) {
            return false;
        }

        return $uri->valid();
    }

    /**
     * Get the URI's scheme
     *
     * @return string|false Scheme or false if no scheme is set.
     */
    public function getScheme()
    {
        if (!empty($this->_scheme)) {
            return $this->_scheme;
        } else {
            return false;
        }
    }

    /**
     * Parse the scheme-specific portion of the URI and place its parts into instance variables.
     *
     * @param string $schemeSpecific
     * @throws Sabel_Exception_Runtime
     * @return void
     */
    protected function _parseUri($schemeSpecific)
    {
        // High-level decomposition parser
        $pattern = '~^((//)([^/?#]*))([^?#]*)(\?([^#]*))?(#(.*))?$~';
        $status = @preg_match($pattern, $schemeSpecific, $matches);
        if ($status === false) {
            throw new Sabel_Exception_Runtime('Internal error: scheme-specific decomposition failed');
        }

        // Failed decomposition; no further processing needed
        if (!$status) {
            return;
        }

        // Save URI components that need no further decomposition
        $this->_path     = isset($matches[4]) ? $matches[4] : '';
        $this->_query    = isset($matches[6]) ? $matches[6] : '';
        $this->_fragment = isset($matches[8]) ? $matches[8] : '';

        // Additional decomposition to get username, password, host, and port
        $combo = isset($matches[3]) ? $matches[3] : '';
        $pattern = '~^(([^:@]*)(:([^@]*))?@)?([^:]+)(:(.*))?$~';
        $status = @preg_match($pattern, $combo, $matches);
        if ($status === false) {
            throw new Sabel_Exception_Runtime('Internal error: authority decomposition failed');
        }

        // Failed decomposition; no further processing needed
        if (!$status) {
            return;
        }

        // Save remaining URI components
        $this->_username = isset($matches[2]) ? $matches[2] : '';
        $this->_password = isset($matches[4]) ? $matches[4] : '';
        $this->_host     = isset($matches[5]) ? $matches[5] : '';
        $this->_port     = isset($matches[7]) ? $matches[7] : '';

    }

    /**
     * Returns a URI based on current values of the instance variables. If any
     * part of the URI does not pass validation, then an exception is thrown.
     *
     * @throws Sabel_Exception_Runtime
     * @return string
     */
    public function getUri()
    {
        if (!$this->valid()) {
            throw new Sabel_Exception_Runtime('One or more parts of the URI are invalid');
        }
        $password = strlen($this->_password) ? ":$this->_password" : '';
        $auth = strlen($this->_username) ? "$this->_username$password@" : '';
        $port = strlen($this->_port) ? ":$this->_port" : '';
        $query = strlen($this->_query) ? "?$this->_query" : '';
        $fragment = strlen($this->_fragment) ? "#$this->_fragment" : '';
        return "$this->_scheme://$auth$this->_host$port$this->_path$query$fragment";
    }

    /**
     * Validate the current URI from the instance variables. Returns true if and only if all
     * parts pass validation.
     *
     * @return boolean
     */
    public function valid()
    {
        /**
         * Return true if and only if all parts of the URI have passed validation
         */
        return $this->validateUsername()
            && $this->validatePassword()
            && $this->validatePort()
            && $this->validatePath()
            && $this->validateQuery()
            && $this->validateFragment();
    }

    /**
     * Returns the username portion of the URL, or FALSE if none.
     *
     * @return string
     */
    public function getUsername()
    {
        return strlen($this->_username) ? $this->_username : false;
    }

    /**
     * Returns true if and only if the username passes validation. If no username is passed,
     * then the username contained in the instance variable is used.
     *
     * @param string $username
     * @throws Sabel_Exception_Runtime
     * @return boolean
     */
    public function validateUsername($username = null)
    {
        if ($username === null) {
            $username = $this->_username;
        }

        // If the username is empty, then it is considered valid
        if (strlen($username) == 0) {
            return true;
        }
        /**
         * Check the username against the allowed values
         *
         * @link http://www.faqs.org/rfcs/rfc2396.html
         */
        $status = @preg_match('/^(' . $this->_regex['alphanum']  . '|' . $this->_regex['mark'] . '|'
                            . $this->_regex['escaped'] . '|[;:&=+$,])+$/', $username);
        if ($status === false) {
            throw new Sabel_Exception_Runtime('Internal error: username validation failed');
        }

        return $status == 1;
    }

    /**
     * Sets the username for the current URI, and returns the old username
     *
     * @param string $username
     * @throws Sabel_Exception_Runtime
     * @return string
     */
    public function setUsername($username)
    {
        if (!$this->validateUsername($username)) {
            throw new Sabel_Exception_Runtime("Username \"$username\" is not a valid HTTP username");
        }
        $oldUsername = $this->_username;
        $this->_username = $username;
        return $oldUsername;
    }

    /**
     * Returns the password portion of the URL, or FALSE if none.
     *
     * @return string
     */
    public function getPassword()
    {
        return strlen($this->_password) ? $this->_password : false;
    }

    /**
     * Returns true if and only if the password passes validation. If no password is passed,
     * then the password contained in the instance variable is used.
     *
     * @param string $password
     * @throws Sabel_Exception_Runtime
     * @return boolean
     */
    public function validatePassword($password = null)
    {
        if ($password === null) {
            $password = $this->_password;
        }

        // If the password is empty, then it is considered valid
        if (strlen($password) == 0) {
            return true;
        }

        // If the password is nonempty, but there is no username, then it is considered invalid
        if (strlen($password) > 0 && strlen($this->_username) == 0) {
            return false;
        }

        /**
         * Check the password against the allowed values
         *
         * @link http://www.faqs.org/rfcs/rfc2396.html
         */
        $status = @preg_match('/^(' . $this->_regex['alphanum']  . '|' . $this->_regex['mark'] . '|'
                             . $this->_regex['escaped'] . '|[;:&=+$,])+$/', $password);
        if ($status === false) {
            throw new Sabel_Exception_Runtime('Internal error: password validation failed.');
        }
        return $status == 1;
    }

    /**
     * Sets the password for the current URI, and returns the old password
     *
     * @param string $password
     * @throws Sabel_Exception_Runtime
     * @return string
     */
    public function setPassword($password)
    {
        if (!$this->validatePassword($password)) {
            throw new Sabel_Exception_Runtime("Password \"$password\" is not a valid HTTP password.");
        }
        $oldPassword = $this->_password;
        $this->_password = $password;
        return $oldPassword;
    }

    /**
     * Returns the domain or host IP portion of the URL, or FALSE if none.
     *
     * @return string
     */
    public function getHost()
    {
        return strlen($this->_host) ? $this->_host : false;
    }

    /**
     * Sets the host for the current URI, and returns the old host
     *
     * @param string $host
     * @throws Sabel_Exception_Runtime
     * @return string
     */
    public function setHost($host)
    {
        $oldHost = $this->_host;
        $this->_host = $host;
        return $oldHost;
    }

    /**
     * Returns the TCP port, or FALSE if none.
     *
     * @return string
     */
    public function getPort()
    {
        return strlen($this->_port) ? $this->_port : false;
    }

    /**
     * Returns true if and only if the TCP port string passes validation. If no port is passed,
     * then the port contained in the instance variable is used.
     *
     * @param string $port
     * @return boolean
     */
    public function validatePort($port = null)
    {
        if ($port === null) {
            $port = $this->_port;
        }

        // If the port is empty, then it is considered valid
        if (!strlen($port)) {
            return true;
        }

        // Check the port against the allowed values
        return ctype_digit((string)$port) && 1 <= $port && $port <= 65535;
    }

    /**
     * Sets the port for the current URI, and returns the old port
     *
     * @param string $port
     * @throws Sabel_Exception_Runtime
     * @return string
     */
    public function setPort($port)
    {
        if (!$this->validatePort($port)) {
            throw new Sabel_Exception_Runtime("Port \"$port\" is not a valid HTTP port.");
        }
        $oldPort = $this->_port;
        $this->_port = $port;
        return $oldPort;
    }

    /**
     * Returns the path and filename portion of the URL, or FALSE if none.
     *
     * @return string
     */
    public function getPath()
    {
        return strlen($this->_path) ? $this->_path : '/';
    }

    /**
     * Returns true if and only if the path string passes validation. If no path is passed,
     * then the path contained in the instance variable is used.
     *
     * @param string $path
     * @throws Sabel_Exception_Runtime
     * @return boolean
     */
    public function validatePath($path = null)
    {
        if ($path === null) {
            $path = $this->_path;
        }
        /**
         * If the path is empty, then it is considered valid
         */
        if (strlen($path) == 0) {
            return true;
        }
        /**
         * Determine whether the path is well-formed
         */
        $pattern = '/^' . $this->_regex['path'] . '$/';
        $status = @preg_match($pattern, $path);
        if ($status === false) {
            throw new Sabel_Exception_Runtime('Internal error: path validation failed');
        }
        return (boolean) $status;
    }

    /**
     * Sets the path for the current URI, and returns the old path
     *
     * @param string $path
     * @throws Sabel_Exception_Runtime
     * @return string
     */
    public function setPath($path)
    {
        if (!$this->validatePath($path)) {
            throw new Sabel_Exception_Runtime("Path \"$path\" is not a valid HTTP path");
        }
        $oldPath = $this->_path;
        $this->_path = $path;
        return $oldPath;
    }

    /**
     * Returns the query portion of the URL (after ?), or FALSE if none.
     *
     * @return string
     */
    public function getQuery()
    {
        return strlen($this->_query) ? $this->_query : false;
    }

    /**
     * Returns true if and only if the query string passes validation. If no query is passed,
     * then the query string contained in the instance variable is used.
     *
     * @param string $query
     * @throws Sabel_Exception_Runtime
     * @return boolean
     */
    public function validateQuery($query = null)
    {
        if ($query === null) {
            $query = $this->_query;
        }

        // If query is empty, it is considered to be valid
        if (strlen($query) == 0) {
            return true;
        }

        /**
         * Determine whether the query is well-formed
         *
         * @link http://www.faqs.org/rfcs/rfc2396.html
         */
        $pattern = '/^' . $this->_regex['uric'] . '*$/';
        $status = @preg_match($pattern, $query);
        if ($status === false) {
            throw new Sabel_Exception_Runtime('Internal error: query validation failed');
        }

        return $status == 1;
    }

    /**
     * Set the query string for the current URI, and return the old query
     * string This method accepts both strings and arrays.
     *
     * @param  string|array $query The query string or array
     * @return string              Old query string
     */
    public function setQuery($query)
    {
        $oldQuery = $this->_query;
        
        // If query is empty, set an empty string
        if (empty($query)) {
            $this->_query = '';
            return $oldQuery;
        }

        // If query is an array, make a string out of it
        if (is_array($query)) {
            $query = http_build_query($query, '', '&');
        
        // If it is a string, make sure it is valid. If not parse and encode it
        } else {
            $query = (string) $query;
            if (! $this->validateQuery($query)) {
                parse_str($query, $query_array);
                $query = http_build_query($query_array, '', '&');   
            }
        }

        // Make sure the query is valid, and set it
        if (! $this->validateQuery($query)) {
            throw new Sabel_Exception_Runtime("'$query' is not a valid query string");
        }
        
        $this->_query = $query;
        
        return $oldQuery;
    }

    /**
     * Returns the fragment portion of the URL (after #), or FALSE if none.
     *
     * @return string|false
     */
    public function getFragment()
    {
        return strlen($this->_fragment) ? $this->_fragment : false;
    }

    /**
     * Returns true if and only if the fragment passes validation. If no fragment is passed,
     * then the fragment contained in the instance variable is used.
     *
     * @param string $fragment
     * @throws Sabel_Exception_Runtime
     * @return boolean
     */
    public function validateFragment($fragment = null)
    {
        if ($fragment === null) {
            $fragment = $this->_fragment;
        }

        // If fragment is empty, it is considered to be valid
        if (strlen($fragment) == 0) {
            return true;
        }

        /**
         * Determine whether the fragment is well-formed
         *
         * @link http://www.faqs.org/rfcs/rfc2396.html
         */
        $pattern = '/^' . $this->_regex['uric'] . '*$/';
        $status = @preg_match($pattern, $fragment);
        if ($status === false) {
            throw new Sabel_Exception_Runtime('Internal error: fragment validation failed');
        }

        return (boolean) $status;
    }

    /**
     * Sets the fragment for the current URI, and returns the old fragment
     *
     * @param string $fragment
     * @throws Sabel_Exception_Runtime
     * @return string
     */
    public function setFragment($fragment)
    {
        if (!$this->validateFragment($fragment)) {
            throw new Sabel_Exception_Runtime("Fragment \"$fragment\" is not a valid HTTP fragment");
        }
        $oldFragment = $this->_fragment;
        $this->_fragment = $fragment;
        return $oldFragment;
    }
}

