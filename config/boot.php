<?php
define('ROOT_DIR', realpath(__DIR__ .'/../'));

require_once ROOT_DIR .'/lib/lysine/core.php';

Lysine\Utils\Profiler::instance()->start('__MAIN__');
Lysine\Config::import(require_once ROOT_DIR .'/config/_config.php');

require_once ROOT_DIR .'/lib/functions.php';

app()->includePath(ROOT_DIR);

listen_event(app()->getRouter(), Lysine\MVC\BEFORE_DISPATCH_EVENT, function($url) {
    if (!preg_match('#^/passport#', $url)) return true;

    $allow_ip = cfg('allow_ip');
    $ip = req()->ip();

    if ($allow_ip == '*') return;
    if ($allow_ip == $ip) return;
    if (is_array($allow_ip) && in_array($ip, $allow_ip)) return;

    throw Lysine\HttpError::forbidden(array('ip' => $ip));
});
