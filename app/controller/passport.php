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

    protected function getPassport($token, $auto_create = false) {
        if (is_uuid($token)) {
            if ($passport = \Model\Passport::find($token))
                return $passport;
            if (!$passport && $auto_create)
                return new \Model\Passport(array('sn' => $token));
        } else {
            if ($passport = \Model\Passport::findByEmail($token))
                return $passport;
            if (!$passport && $auto_create)
                return new \Model\Passport(array('email' => $token));
        }
    }

    public function GET($token) {
        if (!$passport = $this->getPassport($token))
            throw PassportError::not_found($token);
        return $passport->toArray();
    }

    public function POST($token = null) {
        if (!$email = post('email'))
            throw HttpError::bad_request(array('require_param' => 'email'));

        if (!$passwd = post('passwd'))
            throw HttpError::bad_request(array('require_param' => 'passwd'));

        if ($passport = $this->getPassport($email))
            throw PassportError::duplicate_email($email);

        $passport = new \Model\Passport;
        $passport->setProp(array(
            'email' => $email,
            'passwd' => md5($passwd)
        ))->save();

        return \Model\Passport::find($passport->sn)->toArray();
    }

    public function PUT($token) {
        $passport = $this->getPassport($token, true);

        if ($put = put()) {
            if (isset($put['passwd'])) $put['passwd'] = md5($put['passwd']);
            $passport->setProp($put)->save();
        }

        return $passport->toArray();
    }
}
