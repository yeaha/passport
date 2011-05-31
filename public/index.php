<?php
require_once __DIR__ .'/../config/boot.php';

set_exception_handler(function($exception) {
    if (PHP_SAPI == 'cli') die( (string)$exception );  // run in shell

    list($code, $header) = \Lysine\__on_exception($exception, $terminate = false);

    if (!headers_sent())
        foreach ($header as $h) header($h);

    if (in_array('application/json', req()->acceptTypes())) {
        $response = $exception instanceof Lsyine\Error
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

$resp = app()->run();

$profiler = Lysine\Utils\Profiler::instance();
$profiler->end(true);

$resp->setHeader('X-Runtime: '. round($profiler->getRuntime('__MAIN__') ?: 0, 6))
     ->sendHeader();
echo $resp;
