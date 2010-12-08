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
        $token = strtolower($token);
        return is_uuid($token)
             ? \Model\Passport::find($token)
             : \Model\Passport::findByEmail($token);
    }

    public function GET($token) {
        $passport = $this->getPassport($token);
        if (!$passport)
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
            'email' => strtolower($email),
            'passwd' => md5($passwd)
        ))->save();

        return \Model\Passport::find($passport->sn)->toArray();
    }

    public function PUT($token) {
        if (!$passport = $this->getPassport($token)) {
            $passport = new \Model\Passport;
            $prop = is_uuid($token) ? 'sn' : 'email';
            $passport->setProp($prop, strtolower($token));
        }

        if ($put = put()) {
            if (isset($put['passwd'])) $put['passwd'] = md5($put['passwd']);
            $passport->setProp($put)->save();
        }

        return $passport->toArray();
    }
}
