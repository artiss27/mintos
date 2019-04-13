<?php

namespace User;

class Authorization
{
    public $browser = true;
    public $ip = false; // verification method of IP (1 or 2)
    public $errors = [];

    /**
     * by field authorization
     * @param array $data
     * @param bool $rememberme
     * @return bool
     */
    function authByField(array $data, bool $rememberme = false)
    {
        if (!count($data)) {
            $this->errors['data'] = 'Data is empty';
            return false;
        }
        if (isset($data['email']) && (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL))) {
            $this->errors['email'] = 'Wrong e-mail';
            return false;
        }
        if (isset($data['password'])) {
            if ($data['password'] === '') {
                $this->errors['password'] = 'Password can not be empty';
                return false;
            } elseif (mb_strlen($data['password']) < 7) {
                $this->errors['password'] = 'You have entered a short password. At least 7 characters';
                return false;
            }
            $password = $data['password'];
            unset($data['password']);
        }

        $where = [];
        foreach ($data as $k => $v) {
            $where[] = "`" . es($k) . "` = '" . es($v) . "'";
        }
        $row = q("
                    SELECT *
                    FROM `fw_users`
                    WHERE " . implode(' AND ', $where) . "
                    LIMIT 1
                ")->fetch_assoc();

        if (empty($row)) {
            $this->errors['data'] = 'Wrong data';
            return false;
        }

        if (isset($password) && !password_verify($password, $row['password'])) {
            $this->errors['password'] = 'Wrong password';
            return false;
        }

        if (!$this->verifyStatus($row['status'])) return false;
        if ($rememberme) $row['hash'] = $this->rememberMe($row['id']);
        \User::setDatas($row);

        return true;
    }

    /**
     * authorise user by hash
     * @param int $id
     * @param string $hash
     * @return array|bool|null
     */
    function authByHash(int $id, string $hash)
    {
        $row = q("
            SELECT *
            FROM `fw_users`
            WHERE `id` = " . (int)$id . "
              AND `hash` = '" . es($hash) . "'
            LIMIT 1
        ")->fetch_assoc();

        if (empty($row)) {
            $this->errors['data'] = 'No auto authorization hash';
            return false;
        }

        if (!$this->verifyStatus($row['status'])) return false;

        if ($this->browser) {
            if ($row['browser'] != $_SERVER['HTTP_USER_AGENT']) {
                $this->errors['data'] = 'Browser does not match';
                return false;
            }
        }

        if ($this->ip == 1) {
            if ($row['ip'] != $_SERVER['REMOTE_ADDR']) {
                $this->errors['data'] = 'IP does not match';
                return false;
            }
        } elseif ($this->ip == 2) {
            preg_match('#(^\d+\.\d+\.)#isuU', $row['ip'], $matches);
            if (isset($matches[1]))
                $ipbase = $matches[1];

            preg_match('#(^\d+\.\d+\.)#isuU', $row['REMOTE_ADDR'], $matches);
            if (isset($matches[1]))
                $ipnow = $matches[1];

            if (isset($ipbase, $ipnow) && $ipbase != $ipnow) {
                $this->errors['data'] = 'IP does not match';
                return false;
            }
        }
        $row['hash'] = $this->rememberMe($row['id']);
        \User::setDatas($row);

        return true;
    }

    /**
     * veryfi status user
     * @param string $status
     * @return bool
     */
    function verifyStatus(string $status) {
        if ($status !== 'active') {
            if ($status === 'email-verify') {
                $this->errors['email'] = 'You will be able to log in after confirmation of registration by mail';
            } else {
                $this->errors['data'] = 'You were blocked';
            }
            return false;
        }
        return true;
    }

    /**
     * save current data of user (hash, browser, ip) and set cookie (autologinid, autologinhash) and return new hash
     * @param int $id
     * @return string
     */
    function rememberMe(int $id)
    {
        $hash = md5($id . microtime(true) . rand(1, 1000000) . \Config::$DOMAIN);
        q("
            UPDATE `fw_users` SET
            `hash` = '" . es($hash) . "',
            `browser` = '" . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') . "',
            `ip` = '" . es($_SERVER['REMOTE_ADDR']) . "'
            WHERE `id` = " . (int)$id . "
        ");
        setcookie('autologinid', $id, time() + 2592000, '/', str_ireplace(['http://', 'https://', 'www.'], '', \Config::$DOMAIN), true, true);
        setcookie('autologinhash', $hash, time() + 2592000, '/', str_ireplace(['http://', 'https://', 'www.'], '', \Config::$DOMAIN), true, true);
        return $hash;
    }

    /**
     * log out and clear all data of user ($_SESSION, $_COOKIE)
     */
    static function logout()
    {
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }
        if (isset($_COOKIE['autologinid']) || isset($_COOKIE['autologinhash'])) {
            setcookie('autologinid', '', time() - 3600, '/', str_ireplace(['http://', 'https://', 'www.'], '', \Config::$DOMAIN), true, true);
            setcookie('autologinhash', '', time() - 3600, '/', str_ireplace(['http://', 'https://', 'www.'], '', \Config::$DOMAIN), true, true);
        }
    }
}
