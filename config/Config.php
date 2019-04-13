<?php

/**
 * Class Config
 * Global settings for project
 */
class Config
{
    static $STATUS          = 1; // 0 = PRODUCT. 1 = IN WORK
    static $HTTPS           = 0; // 0 - HTTP, 1 - HTTPS

    static $DB_NAME         = 'db';
    static $DB_LOGIN        = 'root';
    static $DB_LOCAL        = 'mysql';
    static $DB_PASS         = 'secret';

    static $DB_TIME_ZONE    = '';
    static $PHP_TIME_ZONE   = 'Europe/Kiev';

    static $DOMAIN          = 'https://mysite.com';
    static $ADMINMAIL       = 'admin@mysite.ru';
    static $SITENAME        = 'mysite.com';

    static $EVENTS          = false;

    static $LANGUAGE        = [
                                'lang' => 'ru',
                                'html_locale' => 'ru-RU',
                                'default' => 'ru',
                            ];
}