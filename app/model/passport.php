<?php
namespace Model;

use Lysine\HttpError;
use Lysine\ORM\DataMapper\DBData;

/**
 * Passport
 *
 * @uses DBData
 * @author yangyi <yangyi.cn.gz@gmail.com>
 */
class Passport extends DBData {
    static protected $collection = 'passport.entity';
    static protected $props_meta = array(
        'sn' => array('type' => 'uuid', 'primary_key' => true),
        'email' => array('type' => 'string', 'refuse_update' => true),
        'passwd' => array('type' => 'string'),
        'create_time' => array('type' => 'datetime', 'refuse_update' => true),
        'update_time' => array('type' => 'datetime'),
    );

    protected function __before_save() {
        $props = array(
            'update_time' => date('Y-m-d H:i:sP'),
        );

        if (!$this->sn) $props['sn'] = uuid();
        if (!$this->create_time) $props['create_time'] = $props['update_time'];

        $this->setProp($props);
    }

    protected function formatProp($prop, $val, array $prop_meta) {
        $val = parent::formatProp($prop, $val, $prop_meta);
        if ($prop == 'email' && $val) return strtolower($val);
        return $val;
    }

    // 根据email查询
    static public function findByEmail($email) {
        return static::select()->where('email = ?', strtolower($email))->get(1);
    }
}

class PassportError extends HttpError {
    static public function not_found($token) {
        return new static("Passport {$token} not found", 404, null, array('token' => $token));
    }

    static public function duplicate_email($email) {
        return static::conflict(array('email' => $email));
    }
}
