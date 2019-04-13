<?php

class User
{
    static $id = 0;
    static $role = '';
    static protected $datas = ['id', 'role', 'avatar', 'name', 'email'];
    static $data = NULL;
    static $autoupdate = true;
    static $avatar = '';
    static $name = '';
    static $email = '';

    static function Start($auth = [])
    {
        if (!count($auth) && isset($_COOKIE['autologinid'], $_COOKIE['autologinhash'])) {
            $auth = new \User\Authorization;
            if (!$auth->authByHash($_COOKIE['autologinid'], $_COOKIE['autologinhash'])) {
                \Authorization::logout();
                redirect('/');
            }
            $auth = ['id' => (int)$_SESSION['user']['id']];
        }

        if (!empty($auth)) {
            $where = [];
            foreach ($auth as $k => $v) {
                $where[] = "`" . es($k) . "` = '" . es($v) . "'";
            }
            $row = q("
                        SELECT `status`" . (count(self::$datas) ? ',`' . implode('`,`', self::$datas) . '`' : '') . "
                        FROM `fw_users`
                        WHERE " . implode(" AND ", $where) . "
                    ")->fetch_assoc();

            if (empty($row) || $row['status'] !== 'active') {
                \Authorization::logout();
                if ($row['status'] !== 'active') $_SESSION['error'] = 'no-access';
                redirect();
            }

            self::setDatas($row);
        }

        if (!empty(self::$data['id']) && !empty(self::$autoupdate)) {
            q("
                UPDATE `fw_users` SET
                `browser` = '" . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') . "',
                `ip` = '" . es($_SERVER['REMOTE_ADDR']) . "'
                WHERE `id` = " . (int)self::$data['id'] . "
            ");
        }
    }

    static function setDatas($row) {
        if (empty($row) || empty($row['id'])) return;

        $_SESSION['user']['id'] = $row['id'];
        self::$data = $row;
        foreach (self::$datas as $k => $v) {
            self::$$v = $row[$v];
        }
    }

    static function getDatas()
    {
        return self::$datas;
    }
}
