<?php
use rokugasenpai\TestDataGenerator\TDG;

class TDGTest extends PHPUnit_Framework_TestCase
{
    private $app_dir = '';
    private $config_dir = '';
    private $json_config_dir =  '';
    private $proc_dir = '';

    protected function setUp()
    {
        $this->app_dir = dirname(dirname(__DIR__));
        $this->config_dir = $this->app_dir . DIRECTORY_SEPARATOR . 'config';
        $this->json_config_dir =  $this->config_dir . DIRECTORY_SEPARATOR . 'json';
        $this->proc_dir = $this->app_dir . DIRECTORY_SEPARATOR . 'proc';
        foreach (glob($this->app_dir . DIRECTORY_SEPARATOR . '*.csv') as $csv_file)
        {
            unlink($csv_file);
        }
        foreach (glob($this->app_dir . DIRECTORY_SEPARATOR . '*.sql') as $sql_file)
        {
            unlink($sql_file);
        }
        $GLOBALS['test_case'] = __CLASS__;
        $GLOBALS['test_method'] = $this->getName();
    }

    public function test_負荷試験を想定したユーザーテーブルデータの生成_json()
    {
        ini_set('memory_limit', '1G');
        $GLOBAL['benchmark'] = '1';
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'users.csv');
        // 処理時間計測が主目的なので、詳細な検査を行わず、
        // ファイル行数をもって正常に処理できたか判断する。
        $fp = fopen($this->app_dir . DIRECTORY_SEPARATOR . 'users.csv', 'r');
        for ($num_lines=0; fgets($fp); $num_lines++);
        $this->assertEquals(1 + 100000, $num_lines);
    }

    protected function tearDown()
    {
        $bak_files = $this->proc_dir . DIRECTORY_SEPARATOR . '*.bak';
        foreach (glob($bak_files) as $bak_file)
        {
            $orig_file = substr($bak_file, 0, -4);
            if (is_file($orig_file)) unlink($orig_file);
            rename($bak_file, $orig_file);
        }
    }
}
