<?php
function myAuthoload($class)
{
    $document_root = $_SERVER['DOCUMENT_ROOT'];
    $module = (isset($_GET['_module']) ? $_GET['_module'] : 'main');
    if (strpos($class, '\\') === false) {
        if (file_exists($document_root . '/modules/' . $module . '/model/' . $class . '.php')) {
            require_once $document_root . '/modules/' . $module . '/model/' . $class . '.php';
        } elseif (file_exists($document_root . '/library/' . $class . '/' . $class . '.php')) {
            require_once $document_root . '/library/' . $class . '/' . $class . '.php';
        }
    } else {
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        if (file_exists($document_root . '/modules/' . $module . '/model/' . $class . '.php')) {
            require_once $document_root . '/modules/' . $module . '/model/' . $class . '.php';
        } elseif (file_exists($document_root . '/library/' . $class . '.php')) {
            require $document_root . '/library/' . $class . '.php';
        }

    }
}

spl_autoload_register('myAuthoload');

function wtf($array, $stop = false)
{
    echo '<pre>' . htmlspecialchars(print_r($array, 1)) . '</pre>';
    if (!$stop) {
        exit();
    }
}

function trimAll($el, $array = false)
{
    if (!$array && !is_string($el)) {
        throw new Exception('An array was passed, it is necessary to pass a string');
    }
    if (!is_array($el)) {
        $el = trim($el);
    } else {
        $el = array_map('trimAll', $el);
    }
    return $el;
}

function intAll($el, $array = false)
{
    if (!$array && !is_numeric($el)) {
        throw new Exception('An array was passed, it is necessary to pass a string');
    }
    if (!is_array($el)) {
        $el = (int)($el);
    } else {
        $el = array_map('intAll', $el);
    }
    return $el;
}

function floatAll($el, $array = false)
{
    if (!$array && !is_numeric($el)) {
        throw new Exception('An array was passed, it is necessary to pass a string');
    }
    if (!is_array($el)) {
        $el = (float)($el);
    } else {
        $el = array_map('floatAll', $el);
    }
    return $el;
}

function hc($el, $array = false)
{
    if (!$array && !is_string($el)) {
        throw new Exception('An array was passed, it is necessary to pass a string');
    }
    if (!is_array($el)) {
        $el = htmlspecialchars($el);
    } else {
        $el = array_map('hc', $el, $array);
    }
    return $el;
}

function hcE($str, $allowedTegs = '')
{
    $allowed = '<p> <strong> <h2> <h3> <h4> <b> <i> <ul> <ol> <li> <br> <a>';
    $allowed = (!empty($allowedTegs) ? $allowedTegs : $allowed);
    $str = strip_tags($str, $allowed);
    $allowed = explode(' ', str_replace(['<', '>'], '', $allowed));
    $str = htmlspecialchars($str);
    foreach ($allowed as $a) {
        $str = str_replace("&lt;" . $a . "&gt;", "<" . $a . ">", $str);
        $str = str_replace("&lt;/" . $a . "&gt;", "</" . $a . ">", $str);
    }
    return $str;
}

/**
 * @param $query
 * @param int $key
 * @return mysqli_result;
 */
function q($query, $key = 0)
{
    $res = \Core\DB::_($key)->query($query);
    if ($res === false) {
        $info = debug_backtrace();
        if (stripos($info[0]['file'], 'library\Pagination\Pagination') !== false) {
            $file = $info[1]['file'];
            $line = $info[1]['line'];
        } else {
            $file = $info[0]['file'];
            $line = $info[0]['line'];
        }
        $error = $query . "\r\n--" . \Core\DB::_($key)->error . "\r\n" .
            '--file: ' . $file . "\r\n" .
            '--line: ' . $line . "\r\n" .
            '--date: ' . date("Y-m-d H:i:s") . "\r\n" .
            "===================================";

        file_put_contents('./logs/mysql.log', $error . "\r\n\r\n", FILE_APPEND);
        if (\Config::$STATUS == 1) {
            echo nl2br(htmlspecialchars($error));
        } else {
            echo \FrontController::init('404');
        }
        exit();
    }
    return $res;
}

function lastAddedId()
{
    return \Core\DB::_()->insert_id;
}

function es($el, $key = 0)
{
    return \Core\DB::_($key)->real_escape_string($el);
}

function redirect($url = '/')
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        header("Content-Security-Policy:default-src *; script-src * 'unsafe-inline';style-src * 'unsafe-inline';");
        echo '<script>window.location.href="' . $url . '";</script><noscript><meta http-equiv="refresh" content="0;url=' . $url . '"></noscript>';
    }
    exit;
}

function urlFix($text)
{
    $text = preg_replace('#[^a-zа-яё\s\_\-\w\d]#ius', '', $text);
    $text = preg_replace('#[\s\_]#ius', '-', $text);
    $text = preg_replace('#\-{2,}#ius', '-', $text);
    $text = mb_strtolower($text);
    $text = htmlspecialchars($text);
    return $text;
}

function getEng($text)
{
    $tr = [
        "А" => "A", "Б" => "B", "В" => "V", "Г" => "G",
        "Д" => "D", "Е" => "E", "Ж" => "J", "З" => "Z", "И" => "I",
        "Й" => "Y", "К" => "K", "Л" => "L", "М" => "M", "Н" => "N",
        "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T",
        "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "TS", "Ч" => "CH",
        "Ш" => "SH", "Щ" => "SCH", "Ъ" => "", "Ы" => "YI", "Ь" => "",
        "Э" => "E", "Ю" => "YU", "Я" => "YA", "а" => "a", "б" => "b",
        "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "j",
        "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
        "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
        "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
        "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y",
        "ы" => "yi", "ь" => "'", "э" => "e", "ю" => "yu", "я" => "ya",
        "ё" => "e", "Ё" => 'E',
        "." => "-", " " => "-", "?" => "_", "/" => "_", "\\" => "_",
        "*" => "-", ":" => "_", "\"" => "_", "<" => "_",
        ">" => "-", "|" => "-"
    ];
    return mb_strtolower(strtr($text, $tr), 'UTF-8');
}

function myHash($var, $salt = 'nchek', $salt2 = 'iuygbvjk')
{
    return crypt(md5($var . $salt), $salt2);
}
