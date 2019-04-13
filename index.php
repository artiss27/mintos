<?php
$tmp = explode('?',$_SERVER['REQUEST_URI']);
$_GET['route'] = trim(urldecode($tmp[0]),'/');
unset($tmp);

require_once './library/Core/functions.php';

Core::$ROOT = __DIR__;
Core::start();

$content = Core\FrontController::init($_GET['route']);

if (isset($_GET['ajax'])) {
    echo $content;
    exit;
}

require Core::$ROOT . '/skins/index.tpl';
exit;
