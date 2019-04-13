<?php

namespace Core;

class DB
{
    static public $mysqli = [];
    static public $connect = [];

    /**
     * @param int $key
     * @return \mysqli;
     */
    static public function _($key = 0)
    {
        if (!isset(self::$mysqli[$key])) {
            if (!isset(self::$connect['server']))
                self::$connect['server'] = \Config::$DB_LOCAL;
            if (!isset(self::$connect['user']))
                self::$connect['user'] = \Config::$DB_LOGIN;
            if (!isset(self::$connect['pass']))
                self::$connect['pass'] = \Config::$DB_PASS;
            if (!isset(self::$connect['db']))
                self::$connect['db'] = \Config::$DB_NAME;

            self::$mysqli[$key] = @new \mysqli(self::$connect['server'], self::$connect['user'], self::$connect['pass'], self::$connect['db']); // WARNING
            if (self::$mysqli[$key]->connect_error) {
                echo 'Ошибка подключения к Базе Данных (' . self::$mysqli[$key]->connect_errno . ') ' . self::$mysqli[$key]->connect_error;
                exit;
            }
            if (!self::$mysqli[$key]->set_charset("utf8mb4")) {
                echo 'Ошибка при загрузке набора символов utf8:' . self::$mysqli[$key]->error;
                exit;
            }
            if (!empty(\Config::$DB_TIME_ZONE)) {
                self::$mysqli[$key]->query("set time_zone = '" . es(\Config::$DB_TIME_ZONE) . "'");
            }
        }
        return self::$mysqli[$key];
    }

    static public function close($key = 0)
    {
        self::$mysqli[$key]->close();
        unset(self::$mysqli[$key]);
    }

    /**
     * @param $res mysqli_result
     * @return mixed
     */
    static public function result(mysqli_result $res)
    {
        $row = $res->fetch_row();
        return $row[0];
    }

    static public function multi_query($res, $key = 0)
    {
        return self::$mysqli[$key]->multi_query($res);
    }

    static public function begin_transaction($key = 0)
    {
    }

    static public function commit($key = 0)
    {
    }

    static public function rollback($key = 0)
    {
    }
}