<?php

namespace User;

use Core;

class Registration
{
    public $error = [];
    public $id = 0;
    public $hash = '';
    protected $verifyEmail = false;
    protected $allowFields = ['email', 'password', 'name'];

    /**
     * registration by multiple fields
     * @param array $data
     * @return bool
     */
    function registByField(array $data = []): bool
    {
        if (!$this->verifyEmail(trim($data['email']))) return false;

        if (isset($data['password'])) {
            if ($data['password'] === '') {
                $this->error['password'] = 'Password can not be empty';
                return false;
            } elseif (mb_strlen($data['password']) < 7) {
                $this->error['password'] = 'You have entered a short password. At least 7 characters';
                return false;
            }
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $insert = [];
        foreach ($data as $k => $v) {
            if (!in_array($k, $this->allowFields)) continue;
            $insert[] = "`" . \es($k) . "` = '" . \es(trim($v)) . "'";
        }

        q("
            INSERT INTO `fw_users` SET
            " . implode(',', $insert) . "
        ");
        $this->id = \Core\DB::_()->insert_id;
        $this->hash = md5($this->id . microtime(true) . rand(1, 1000000) . trim(isset($data['password']) ?? time()));

        q("
            UPDATE `fw_users` SET
             " . ($this->verifyEmail ? '' : '`status` = \'active\',') . "
            `hash` = '" . es($this->hash) . "'
            WHERE `id` = " . $this->id . "
        ");
        return true;
    }

    /**
     * verify: email is busy or no
     * @param string $email
     * @param int $id
     * @return bool
     */
    public function verifyEmail(string $email, int $id = 0): bool
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error['email'] = 'Correct e-mail is required for registration';
            return false;
        }

        $row = q("
            SELECT 1
            FROM `fw_users`
            WHERE `email` = '" . \es($email) . "' AND `id` <> " . (int)$id . "
        ")->fetch_assoc();

        if (!empty($row)) {
            $this->error['email'] = 'Email is busy';
            return false;
        }
        return true;
    }

    /**
     * edit user data
     * @param array $data
     * @param int $id
     * @return bool
     */
    function edit(array $data, int $id): bool
    {
        if (isset($data['id'])) {
            $this->error['form'] = 'Cannot change ID';
            return false;
        }

        if (isset($data['password'])) {
            if (!empty($data['password'])) {
                if (mb_strlen($data['password']) < 7) {
                    $this->error['password'] = 'You have entered a short password. At least 7 characters';
                    return false;
                }
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                unset($data['password']);
            }
        }

        if (isset($data['email']) && !$this->verifyEmail(trim($data['email']), $id)) return false;

        $update = [];
        foreach ($data as $k => $v) {
            if (!in_array($k, $this->allowFields)) continue;
            $update[] = "`" . \es($k) . "` = '" . \es(trim($v)) . "'";
        }

        q("
            UPDATE `fw_users` SET
            " . implode(',', $update) . "
            WHERE `id` = " . (int)$id . "
        ");
        return true;
    }

    /**
     * TODO need to do method: send password recovery link
     * send password recovery link
     * @param string $email
     */
    function sendPassRecLink(string $email) {

    }
}
