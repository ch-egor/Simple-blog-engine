<?php

define('DEFAULT_TEMPLATES_LOCATION', 'templates');
define('DB_HOST', 'localhost');
define('DB_USER', 'messages');
define('DB_PASSWORD', 'msgspswd');
define('DB_DATABASE', 'messages');
define('MESSAGES_PER_PAGE', 20);
define('POSTS_PER_PAGE', 10);
define('MINIFY_HTML', false);

function __autoload($className) {
    $filename = "classes/{$className}.class.php";
    if (file_exists($filename))
        require_once $filename;
}

$app = new MyApplication();
$app->init();
$app->run();
