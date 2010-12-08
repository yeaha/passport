<?php
namespace Controller\Passport;

use Model\Passport;
use Model\PassportError;

class Auth {
    public function __after_run(&$response) {
        if (isset($response['passwd'])) unset($response['passwd']);

        if (in_array('application/json', req()->acceptTypes())) {
            $response = json_encode($response);
        } else {
            $response = var_export($response, true);
        }
    }

    public function POST() {
        if (!$email = post('email'))
            throw HttpError::bad_request(array('require_param' => 'email'));

        if (!$passwd = post('passwd'))
            throw HttpError::bad_request(array('require_param' => 'passwd'));

        if (!$passport = Passport::findByEmail($email))
            throw PassportError::not_found($email);

        if ($passport->passwd != md5($passwd))
            throw PassportError::unauthorized(array('email' => $email, 'message' => 'Wrong password'));

        return $passport->toArray();
    }
}
