<?php

define('APP_PATH', dirname(__DIR__));
define('YAF', 1);
// define('STARTTIME', microtime(true));

try {
    $hostArr = explode('.', $_SERVER['HTTP_HOST']);
    switch ($hostArr[1]) {
        case 'sh-local':
            $brand = 'angel';
            break;
        case 'bj-local':
            $brand = 'comfos';
            break;
        default:
            throw new DomainException('Invalid HTTP_HOST', 1);
    }
    define('CRM_BRAND', $brand);
    $app = new Yaf\Application(APP_PATH.'/conf/application.ini', CRM_BRAND);
    $app->bootstrap() //call bootstrap methods defined in Bootstrap.php
        ->run();
    // echo '<!--' . round((microtime(true) - STARTTIME) * 1000) . 'ms-->';
} catch (\Throwable $th) {
    echo nl2br($th);
}
