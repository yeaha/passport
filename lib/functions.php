<?php
use Lysine\Error;
use Lysine\HttpError;
use Lysine\MVC\Response;

function uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function is_uuid($uuid) {
    return preg_match('/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/', $uuid);
}

function __on_exception($exception) {
    $code = $exception instanceof HttpError
          ? $exception->getCode()
          : 500;
    header(Response::httpStatus($code));

    if (in_array('application/json', req()->acceptTypes())) {
        $response = array(
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        );
        if ($exception instanceof Error) $response = array_merge($response, $exception->getMore());
        echo json_encode($response);
    } else {
        require_once ROOT_DIR .'/public/_error/500.php';
    }

    die($code);
}

function __on_error($errno, $errstr, $errfile, $errline, $errcontext) {
    throw new Error($errstr, $errno, null, array(
        'file' => $errfile,
        'line' => $errline,
        'context' => $errcontext,
    ));
}
