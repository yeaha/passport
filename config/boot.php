<?php
use Lysine\Error;
use Lysine\HttpError;

define('ROOT_DIR', realpath(__DIR__ .'/../'));
define('DEBUG', false);

require_once ROOT_DIR .'/lib/lysine/core.php';

Lysine\Utils\Profiler::instance()->start('__MAIN__');
Lysine\Config::import(require_once ROOT_DIR .'/config/_config.php');
Lysine\ORM\DataMapper\Meta::setCache('cache.orm.meta');

require_once ROOT_DIR .'/lib/functions.php';

set_exception_handler(function($exception) {
    $code = \Lysine\__on_exception($exception);
    if (in_array('application/json', req()->acceptTypes())) {
        echo json_encode($exception->toArray());
    } else {
        require_once ROOT_DIR .'/public/_error/500.php';
    }

    die(1);
});

set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
    throw new Error($errstr, $errno, null, array(
        'file' => $errfile,
        'line' => $errline,
    ));
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
