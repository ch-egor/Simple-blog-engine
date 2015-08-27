<?php

/**
 * Represents an application controller, performs basic routing tasks
 */
class Application {

    use Routing;
    
    private $view;

    private $_routes;
    private $_defaultAction;

    /**
     * Creates an application
     */
    public function __construct() {
        $this->view = new View();
        $this->_routes = array();
        $this->_defaultAction = 'notFound';
    }

    /**
     * Starts up the application
     */
    public function run() {
        if (!$this->findRoute(false) && !$this->findRoute(true))
            $this->performAction($this->_defaultAction);
    }

    /**
     * Includes a URI into the routing table.
     * Use {argument_name} in the URI to send an argument to the action method.
     * For example, "/{user}/blogs/{post}/".
     * @param type $uri - A URI
     * @param string|function $action - A function or a function name
     * @return boolean Whether the route was successfully registered
     */
    public function match($uri, $action) {
        if (!is_string($uri) || !$this->checkAction($action))
            return false;
        $route['uri'] = $uri;
        $route['action'] = $action;
        $this->_routes[] = $route;
        return true;
    }

    /**
     * Specifies the action performed if no matching route was found
     * @param string|callable $action A function or a function name
     */
    public function nomatch($action) {
        if (!$this->checkAction($action))
            return false;
        $this->_defaultAction = $action;
        return true;
    }
    
    public function render($template, $args = array(), $minify = false) {
        $this->view->render($template, $args, $minify);
    }
    
    public function escape($string, $lang = 'html') {
        return $this->view->escape($string, $lang);
    }
    
    public function redirect($uri) {
        header('Location: ' . $this->route($uri));
        exit();
    }

    public function notFoundAction() {
        header("HTTP/1.1 404 Not Found");
        echo "<!DOCTYPE html>\n" . '<html><head><meta charset="utf-8">' .
        '<title>Not Found</title></head>' .
        '<body><h1>Not Found</h1></body></html>';
    }

    private function checkAction($action) {
        if (is_callable($action))
            return true;
        if (is_string($action)) {
            $actionMethod = "{$action}Action";
            if (method_exists($this, $actionMethod))
                return true;
        }
        return false;
    }

    private function performAction($action, $args = array()) {
        if (is_callable($action))
            $function = $action;
        else {
            $actionName = "{$action}Action";
            $function = array($this, $actionName);
        }
        return call_user_func_array($function, $args);
    }
    
    private function findRoute($ignoreTrailingSlash) {
        foreach ($this->_routes as $route) {
            $args = $this->matchUri($route['uri'], $ignoreTrailingSlash);
            if (!is_array($args))
                continue;
            if (!$ignoreTrailingSlash) {
                $action = $route['action'];
                $this->performAction($action, $args);
            }
            else {
                $uri = $this->uri();
                if (preg_match('/\/$/', $uri))
                    $uri = preg_replace('/\/$/', '', $uri);
                else
                    $uri .= '/';
                header("Location: {$this->route($uri)}");
            }
            return true;
        }
        return false;
    }

    private function matchUri($uri, $ignoreTrailingSlash = true) {
        if (!is_string($uri))
            return false;
        $uriParts = $this->getUriParts($uri, $ignoreTrailingSlash);
        $currentUriParts = $this->getUriParts($this->uri(), $ignoreTrailingSlash);
        if (count($uriParts) != count($currentUriParts))
            return false;
        $args = array();
        for ($i = 0; $i < count($uriParts); $i++) {
            $part = $uriParts[$i];
            if (preg_match('/^{[a-zA-Z_][\w_]*}$/', $part))
                $args[] = $currentUriParts[$i];
            else if ($part != $currentUriParts[$i])
                return false;
        }
        return $args;
    }

    private function getUriParts($uri, $ignoreTrailingSlash = true) {
        $uri = preg_replace('/\?.*$/', '', $uri);
        $uri = preg_replace('/^\//', '', $uri);
        if ($ignoreTrailingSlash)
            $uri = preg_replace('/\/$/', '', $uri);
        $uriParts = explode('/', $uri);
        return $uriParts;
    }

}
