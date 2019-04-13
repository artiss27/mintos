<?php

class Core
{
    static $ROOT = '';

    static $SITEMAP = [
        'single' => [
            '404'
        ]
    ];
    static $SECTIONS = [
        'admin' => [],
        'api' => [],
    ];
    static $DIRECTORY;
    static $END = '';
    static $HEAD = '';
    static $TITLE = 'Главная страница';

    static $META = [
        'title' => 'стандартный TITLE',
        'description' => 'description',
        'keywords' => 'keywords',
        'canonical' => '',
        'shortlink' => '',
        'prev' => '',
        'next' => '',
        'dns-prefetch' => [],
        'head' => '',
    ];

    static public function start()
    {
        require_once self::$ROOT . '/config/Config.php';

        self::setDisplayErrors((\Config::$STATUS ? true : false));
        session_start();

        if (!isset($_SESSION['antixsrf'])) {
            $_SESSION['antixsrf'] = md5(time() . $_SERVER['REMOTE_ADDR'] . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : rand(1, 99999)));
        }

        \User::Start(isset($_SESSION['user']['id']) ? ['id' => (int)$_SESSION['user']['id']] : []);
    }


    static public function setDisplayErrors($allow)
    {
        if (!$allow) {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(-1);
            ini_set('log_errors', 1);
        } else {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
            ini_set('log_errors', 1);
        }
    }

    static public function addCss($url, $cache = false, $head = false)
    {
        $url = str_replace(self::$ROOT, '', $url);
        $content = '<link rel="stylesheet" href="' . $url . ($cache ? '' : '?' . filemtime(self::$ROOT . $url)) . '">';
        if ($head) {
            self::$HEAD .= $content;
        } else {
            self::$END .= $content;
        }

    }

    static public function addJs($url, $cache = false, $head = false)
    {
        $url = str_replace(self::$ROOT, '', $url);
        $content = '<script src="' . $url . ($cache ? '' : '?' . filemtime(self::$ROOT . $url)) . '"></script>';
        if ($head) {
            self::$HEAD .= $content;
        } else {
            self::$END .= $content;
        }
    }
}