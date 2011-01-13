<?php
define('ROOT_DIR', realpath(__DIR__ .'/../'));

require_once ROOT_DIR .'/lib/lysine/core.php';

use Lysine\Error;
use Lysine\HttpError;

Lysine\Utils\Profiler::instance()->start('__MAIN__');
Lysine\Config::import(require_once ROOT_DIR .'/config/_config.php');
Lysine\ORM\DataMapper\Meta::setCache('cache.orm.meta');

require_once ROOT_DIR .'/lib/functions.php';

set_exception_handler(function($exception) {
    global $argc;

    if (isset($argc)) {  // run in shell
        echo $exception;
    } else {
        list($code, $header) = \Lysine\__on_exception($exception);

        if (in_array('application/json', req()->acceptTypes())) {
            !headers_sent() and header('Content-Type: application/json');
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
