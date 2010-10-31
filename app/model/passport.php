<?php
namespace Model;

use Lysine\HttpError;
use Lysine\ORM\DataMapper\DBData;

/**
 * Passport
 *
 * @uses DBData
 * @author yangyi <yangyi.cn.gz@gmail.com>
 * @collection passport.entity
 */
class Passport extends DBData {
    /**
     * 编号
     *
     * @var uuid
     * @access protected
     * @primary_key true
     * @allow_null false
     */
    protected $sn;

    /**
     * Email
     *
     * @var string
     * @access protected
     * @allow_null false
     * @refuse_update true
     */
    protected $email;

    /**
     * 密码
     *
     * @var string
     * @access protected
     * @allow_null false
     */
    protected $passwd;

    /**
     * 注册时间
     *
     * @var datetime
     * @access protected
     * @refuse_update true
     */
    protected $create_time;

    /**
     * 最后更新时间
     *
     * @var datetime
     * @access protected
     */
    protected $update_time;

    protected function __before_save() {
        $props = array(
            'update_time' => date('Y-m-d H:i:sP'),
        );

        if (!$this->sn) $props['sn'] = uuid();
        if (!$this->create_time) $props['create_time'] = $props['update_time'];

        $this->setProp($props);
    }

    // 根据email查询
    static public function findByEmail($email) {
        return static::select()->where('email = ?', $email)->get(1);
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
