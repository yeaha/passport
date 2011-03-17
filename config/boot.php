<?php
define('ROOT_DIR', realpath(__DIR__ .'/../'));

require_once ROOT_DIR .'/lib/lysine/core.php';

use Lysine\Error;
use Lysine\HttpError;

Lysine\Utils\Profiler::instance()->start('__MAIN__');
Lysine\Config::import(require_once ROOT_DIR .'/config/_config.php');

require_once ROOT_DIR .'/lib/functions.php';

set_exception_handler(function($exception) {
    if (PHP_SAPI == 'cli') die( (string)$exception );  // run in shell

    list($code, $header) = \Lysine\__on_exception($exception, $terminate = false);

    if (!headers_sent())
        foreach ($header as $h) header($h);

    if (in_array('application/json', req()->acceptTypes())) {
        $response = $exception instanceof Error
                  ? $exception->toArray()
                  : array(
                        'code' => $exception->getCode(),
                        'message' => $exception->getMessage(),
                    );
        echo json_encode($response);
    } else {
        ob_start();
        require ROOT_DIR .'/public/_error/500.php';
        echo ob_get_clean();
    }
    die(1);
});

app()->includePath(ROOT_DIR .'/app');

listen_event(app()->getRouter(), Lysine\MVC\BEFORE_DISPATCH_EVENT, function($url) {
    if (!preg_match('#^/passport#', $url)) return true;

    $allow_ip = cfg('allow_ip');
    $ip = req()->ip();

    if ($allow_ip == '*') return;
    if ($allow_ip == $ip) return;
    if (is_array($allow_ip) && in_array($ip, $allow_ip)) return;

    throw HttpError::forbidden(array('ip' => $ip));
});
