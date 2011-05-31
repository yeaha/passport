<?php
namespace Controller;

use Model\PassportError;
use Lysine\HttpError;

class Passport {
    public function __after_run(&$response) {
        if (isset($response['passwd'])) unset($response['passwd']);

        if (in_array('application/json', req()->acceptTypes())) {
            $response = json_encode($response);
        } else {
            $response = var_export($response, true);
        }
    }

    protected function getPassport($token) {
        return is_uuid($token)
             ? \Model\Passport::find($token)
             : \Model\Passport::findByEmail($token);
    }

    public function GET($token) {
        if (!$passport = $this->getPassport($token))
            throw PassportError::not_found($token);
        return $passport->toArray();
    }

    public function POST() {
        if (!$email = post('email'))
            throw HttpError::bad_request(array('require_param' => 'email'));

        if (!$passwd = post('passwd'))
            throw HttpError::bad_request(array('require_param' => 'passwd'));

        if ($passport = $this->getPassport($email))
            throw PassportError::duplicate_email($email);

        $passport = new \Model\Passport;
        $passport->setProp(array(
            'email' => $email,
            'passwd' => $passwd
        ))->save();

        resp()->setCode(201);
        return \Model\Passport::find($passport->sn)->toArray();
    }

    public function PUT($token) {
        if (!$passport = $this->getPassport($token))
            throw PassportError::not_found($token);

        if ($passwd = put('passwd'))
            $passport->setProp('passwd', $passwd)->save();

        return $passport->toArray();
    }
}
