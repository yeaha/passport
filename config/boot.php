<?php
define('ROOT_DIR', realpath(__DIR__ .'/../'));

use Lysine\HttpError;

require_once ROOT_DIR .'/lib/lysine.phar';
Lysine\Config::import(require_once ROOT_DIR .'/config/_config.php');
Lysine\ORM\DataMapper\Meta::setCache('cache.orm.meta');

require_once ROOT_DIR .'/lib/functions.php';
set_exception_handler('__on_exception');
set_error_handler('__on_error');

app()->includePath(ROOT_DIR .'/app');

listen_event(app()->getRouter(), Lysine\MVC\BEFORE_DISPATCH_EVENT, function($url) {
    if (!preg_match('#^/passport#', $url)) return true;

    $allow_ip = cfg('allow_ip');
    $ip = req()->ip();

    if ($allow_ip == '*') return;
    if ($allow_ip == $ip) return;
    if (is_array($allow_ip) && in_array($ip, $allow_ip)) return;

    throw HttpError::not_acceptable(array('ip' => $ip));
});
