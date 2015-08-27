<?php

/*
 * The templating engine
 */

class View {

    use Routing;

    private $_args;
    private $_parent;
    private $_bufferedProperty;

    public function __construct($args = array()) {
        $this->_args = $args;
        $this->_parent = null;
        $this->_bufferedProperty = null;
    }

    public function getParent() {
        return $this->_parent;
    }

    public function extend($template) {
        $this->_parent = $template;
    }

    public function has($property) {
        return array_key_exists($property, $this->_args);
    }

    public function set($property, $value) {
        $this->_args[$property] = $value;
    }

    public function value($property) {
        if (array_key_exists($property, $this->_args))
            return $this->_args[$property];
        return null;
    }

    public function start($property) {
        if (!empty($this->_bufferedProperty))
            return false;
        $this->_bufferedProperty = $property;
        ob_start();
        return true;
    }

    public function stop() {
        if (!is_string($this->_bufferedProperty))
            return false;
        $this->_args[$this->_bufferedProperty] = ob_get_clean();
        $this->_bufferedProperty = null;
        return true;
    }

    public function output($property, $defaultValue = '') {
        echo $this->has($property) ? $this->_args[$property] : $defaultValue;
    }

    public function escape($string, $lang = 'html') {
        if (!is_string($string))
            return null;
        if ($lang == 'html')
            return htmlspecialchars($string, ENT_QUOTES, "UTF-8");
        if ($lang == 'js') {
            $escapedString = json_encode($string);
            $length = strlen($escapedString);
            return substr($escapedString, 1, $length - 2);
        }
    }

    public function render($template, $args = array(), $minify = false) {
        if (!empty(DEFAULT_TEMPLATES_LOCATION))
            $template = DEFAULT_TEMPLATES_LOCATION . "/{$template}";
        if (!file_exists($template) || !is_array($args))
            return false;
        $view = new View($args);
        foreach ($args as $name => $value)
            $$name = $value;
        while (file_exists($template)) {
            ob_start();
            include $template;
            $output = ob_get_clean();
            $template = $view->getParent();
            $view->extend(null);
            $view->stop();
        }
        if (MINIFY_HTML == true || $minify == true)
            $output = preg_replace('/\s+/', ' ', $output);
        echo $output;
    }

}
