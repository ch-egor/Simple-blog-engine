<?php

/**
 * 
 */
trait Routing {
    /**
     * Tests whether the request was performed with one of given methods.
     * If the argument is not provided, simply returns a request method string.
     * @param string|array [$method] - Specifies a method or method to check against
     * @return string|boolean The method string or if the request is of a specified method
     */
    public function method($method = null) {
        if (is_string($method))
            return (strcasecmp($_SERVER['REQUEST_METHOD'], $method) == 0);
        if (is_array($method)) {
            foreach ($method as $value) {
                if (strcasecmp($_SERVER['REQUEST_METHOD'], $value) == 0)
                    return true;
            }
            return false;
        }
        return $_SERVER['REQUEST_METHOD'];
    }

    public function get($name) {
        if (isset($_GET[$name]))
            return $_GET[$name];
        return null;
    }
    
    public function post($name) {
        if (isset($_POST[$name]))
            return $_POST[$name];
        return null;
    }
    
    public function session($name) {
        if (isset($_SESSION[$name]))
            return $_SESSION[$name];
        return null;
    }
    
    public function cookie($name) {
        if (isset($_COOKIE[$name]))
            return $_COOKIE[$name];
        return null;
    }
    
    /**
     * Returns the URI to the application controller
     * @return string The URI
     */
    public function base() {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $nameParts = explode('/', $scriptName, -1);
        $base = implode('/', $nameParts);
        return $base;
    }
    
    /**
     * Returns the requested URI in relation to the app controller
     * @return string The URI
     */
    public function uri() {
        if (isset($_SERVER['PATH_INFO']))
            $uri = $_SERVER['PATH_INFO'];
        else {
            $requestUri = $_SERVER['REQUEST_URI'];
            $rawUri = substr($requestUri, strlen($this->base()));
        }
        $uri = preg_replace('/\?.*$/', '', $rawUri);
        return $uri;
    }
    
    /**
     * Returns the path to a static resource (e.g. an image, stylesheet or script)
     * @param string $filename /- A filename
     * @return string - The absolute URI to a resource (without the domain part)
     */
    public function asset($filename) {
        if (!is_string($filename))
            return null;
        $asset = "{$this->base()}/{$filename}";
        return $asset;
    }
    
    /**
     * Returns the path to a dinamically generated page (or a file)
     * @param type $path - The route relative to an application controller
     * @return string - The absolute URI to a resource (without the domain part)
     */
    public function route($path) {
        if (!is_string($path))
            return null;
        $requestUri = $_SERVER['REQUEST_URI'];
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $pattern = '/^' . preg_quote($scriptName, '/') . '/';
        if (preg_match($pattern, $requestUri))
            $route = $scriptName;
        else
            $route = $this->base();
        $uri = $route . $path;
        return $uri;
    }
}
