<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'TDG.php';

use rokugasenpai\TestDataGenerator\TDG;

try
{
    $filepath = './config/json/実際の利用を想定したユーザーテーブルデータの生成.json';
    if (strpos(PHP_OS, 'WIN') === 0) mb_convert_encoding($filepath, 'SJIS-win', 'UTF-8');
    $tdg = new TDG($filepath);
    $tdg->main(TRUE, TRUE, TRUE);

    $filepath = './config/json/負荷試験を想定したユーザーテーブルデータの生成.json';
    if (strpos(PHP_OS, 'WIN') === 0) mb_convert_encoding($filepath, 'SJIS-win', 'UTF-8');
    $tdg = new TDG($filepath);
    $tdg->main(TRUE, TRUE, TRUE);
}
catch (Exception $e)
{
    echo $e->getFile() . ' : ' . $e->getLine() . ' : ' . $e->getMessage();
}
