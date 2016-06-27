<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'TDG.php';

use rokugasenpai\TestDataGenerator\TDG;

try
{
    $tdg = new TDG();
    $tdg->main();
}
catch (Exception $e)
{
    echo $e->getFile() . ' : ' . $e->getLine() . ' : ' . $e->getMessage();
}
