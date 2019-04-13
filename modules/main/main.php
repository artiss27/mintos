<?php
CORE::$META['title'] = 'Test task';
CORE::$META['description'] = 'Description test task';
CORE::$META['keywords'] = 'Keywords test task';

$arResult = [];
if (\User::$id) {
    $rss = new Rss();
    $arResult = $rss->getArraylists();
}

//wtf($arResult);