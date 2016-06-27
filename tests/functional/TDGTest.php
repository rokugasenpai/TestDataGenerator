<?php
use rokugasenpai\TestDataGenerator\TDG;

class TDGTest extends PHPUnit_Framework_TestCase
{
    const WEIGHT_9_COEFFICIENT = 3;

    private $app_dir = '';
    private $config_dir = '';
    private $json_config_dir =  '';
    private $yml_config_dir = '';
    private $proc_dir = '';

    protected function setUp()
    {
        $this->app_dir = dirname(dirname(__DIR__));
        $this->config_dir = $this->app_dir . DIRECTORY_SEPARATOR . 'config';
        $this->json_config_dir =  $this->config_dir . DIRECTORY_SEPARATOR . 'json';
        $this->yml_config_dir = $this->config_dir  . DIRECTORY_SEPARATOR . 'yml';
        $this->proc_dir = $this->app_dir . DIRECTORY_SEPARATOR . 'proc';
        foreach (glob($this->app_dir . DIRECTORY_SEPARATOR . '*.csv') as $csv_file)
        {
            unlink($csv_file);
        }
        foreach (glob($this->app_dir . DIRECTORY_SEPARATOR . '*.sql') as $sql_file)
        {
            unlink($sql_file);
        }
    }

    public function test_default_config()
    {
        $tdg = new TDG();
        $tdg->main(FALSE, TRUE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals($ir, $record[0]);
        }
    }

    public function test_１からシーケンス番号を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals($ir, $record[0]);
        }
    }

    public function test_１からシーケンス番号を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals($ir, $record[0]);
        }
    }

    public function test_出力ファイルパスを指定して１からシーケンス番号を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'output.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'output.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals($ir, $record[0]);
        }
    }

    public function test_出力ファイルパスを指定して１からシーケンス番号を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'output.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'output.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals($ir, $record[0]);
        }
    }

    public function test_ヘッダ無しで１からシーケンス番号を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(100, $data);
        foreach ($data as $ir => $record)
        {
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals($ir + 1, $record[0]);
        }
    }

    public function test_ヘッダ無しで１からシーケンス番号を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(100, $data);
        foreach ($data as $ir => $record)
        {
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals($ir + 1, $record[0]);
        }
    }

    public function test_データの標準出力ありで１からシーケンス番号を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $this->expectOutputRegex('/field01/s');
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals($ir, $record[0]);
        }
    }

    public function test_データの標準出力ありで１からシーケンス番号を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->expectOutputRegex('/field01/s');
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals($ir, $record[0]);
        }
    }

    public function test_改行コードCRLFで１からシーケンス番号を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\r\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\r\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals($ir, $record[0]);
        }
    }

    public function test_改行コードCRLFで１からシーケンス番号を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\r\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\r\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals($ir, $record[0]);
        }
    }

    public function test_１０００からシーケンス番号を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals($ir + 1000 - 1, $record[0]);
        }
    }

    public function test_１０００からシーケンス番号を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals($ir + 1000 - 1, $record[0]);
        }
    }

    public function test_４桁数値を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals(intval($record[0]), $record[0]);
            $this->assertGreaterThanOrEqual(1000, $record[0]);
            $this->assertLessThanOrEqual(9999, $record[0]);
        }
    }

    public function test_４桁数値を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals(intval($record[0]), $record[0]);
            $this->assertGreaterThanOrEqual(1000, $record[0]);
            $this->assertLessThanOrEqual(9999, $record[0]);
        }
    }

    public function test_間隔５の４桁数値を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals(intval($record[0]), $record[0]);
            $this->assertEquals(0, $record[0] % 5);
            $this->assertGreaterThanOrEqual(1000, $record[0]);
            $this->assertLessThanOrEqual(9999, $record[0]);
        }
    }

    public function test_間隔５の４桁数値を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals(intval($record[0]), $record[0]);
            $this->assertEquals(0, $record[0] % 5);
            $this->assertGreaterThanOrEqual(1000, $record[0]);
            $this->assertLessThanOrEqual(9999, $record[0]);
        }
    }

    public function test_間隔０．５の４桁数値を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertSame(0.0, $record[0] / 0.5 - intval($record[0] / 0.5));
            $this->assertGreaterThanOrEqual(1000, $record[0]);
            $this->assertLessThanOrEqual(9999, $record[0]);
        }
    }

    public function test_間隔０．５の４桁数値を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertSame(0.0, $record[0] / 0.5 - intval($record[0] / 0.5));
            $this->assertGreaterThanOrEqual(1000, $record[0]);
            $this->assertLessThanOrEqual(9999, $record[0]);
        }
    }

    public function test_１００漸増で９フィールドに３桁数値を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01","field02","field03","field04","field05",'
                    . '"field06","field07","field08","field09"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(9, $record);
            foreach ($record as $iv => $value)
            {
                $this->assertEquals(intval($value), $value);
                $this->assertGreaterThanOrEqual(($iv + 1) * 100, $value);
                $this->assertLessThanOrEqual(($iv + 2) * 100 - 1, $value);
            }
        }
    }

    public function test_１００漸増で９フィールドに３桁数値を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01","field02","field03","field04","field05",'
                    . '"field06","field07","field08","field09"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(9, $record);
            foreach ($record as $iv => $value)
            {
                $this->assertEquals(intval($value), $value);
                $this->assertGreaterThanOrEqual(($iv + 1) * 100, $value);
                $this->assertLessThanOrEqual(($iv + 2) * 100 - 1, $value);
            }
        }
    }

    public function test_高頻度で３桁数値・低頻度で４桁数値を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        $field01_major = 0;
        $field01_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            if ($record[0] >= 100 && $record[0] < 1000)
            {
                $field01_major++;
            }
            else if ($record[0] >= 1000 && $record[0] < 10000)
            {
                $field01_minor++;
            }
        }
        $this->assertEquals(100, $field01_major + $field01_minor);
        $this->assertLessThan($field01_major, $field01_minor * self::WEIGHT_9_COEFFICIENT);
    }

    public function test_高頻度で３桁数値・低頻度で４桁数値を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        $field01_major = 0;
        $field01_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            if ($record[0] >= 100 && $record[0] < 1000)
            {
                $field01_major++;
            }
            else if ($record[0] >= 1000 && $record[0] < 10000)
            {
                $field01_minor++;
            }
        }
        $this->assertEquals(100, $field01_major + $field01_minor);
        $this->assertLessThan($field01_major, $field01_minor * self::WEIGHT_9_COEFFICIENT);
    }

    public function test_現在の日時を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $now = new DateTime('now');
        $timestamp_lower = $now->getTimestamp();
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $now = new DateTime('now');
        $timestamp_higher = $now->getTimestamp();
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertGreaterThanOrEqual($timestamp_lower, strtotime($record[0]));
            $this->assertLessThanOrEqual($timestamp_higher, strtotime($record[0]));
        }
        unlink($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
    }

    public function test_現在の日時を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $timestamp_lower = time();
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $timestamp_higher = time();
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertGreaterThanOrEqual($timestamp_lower, strtotime($record[0]));
            $this->assertLessThanOrEqual($timestamp_higher, strtotime($record[0]));
        }
    }

    public function test_２０１０～２０１５年の日時を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $record[0]);
            $this->assertGreaterThanOrEqual(mktime(0, 0, 0, 1, 1, 2010), strtotime($record[0]));
            $this->assertLessThanOrEqual(mktime(23, 59, 59, 12, 31, 2015), strtotime($record[0]));
        }
    }

    public function test_２０１０～２０１５年の日時を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $record[0]);
            $this->assertGreaterThanOrEqual(mktime(0, 0, 0, 1, 1, 2010), strtotime($record[0]));
            $this->assertLessThanOrEqual(mktime(23, 59, 59, 12, 31, 2015), strtotime($record[0]));
        }
    }

    public function test_マイクロ秒単位で２０１０～２０１５年の日時を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/', $record[0]);
            $this->assertGreaterThanOrEqual(mktime(0, 0, 0, 1, 1, 2010), strtotime($record[0]));
            $this->assertLessThanOrEqual(mktime(23, 59, 59, 12, 31, 2015), strtotime($record[0]));
        }
    }

    public function test_マイクロ秒単位で２０１０～２０１５年の日時を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/', $record[0]);
            $this->assertGreaterThanOrEqual(mktime(0, 0, 0, 1, 1, 2010), strtotime($record[0]));
            $this->assertLessThanOrEqual(mktime(23, 59, 59, 12, 31, 2015), strtotime($record[0]));
        }
    }

    public function test_高頻度で２０１０～２０１２年・低頻度で２０１３～２０１５年の日時を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        $field01_major = 0;
        $field01_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $record[0]);
            if (strtotime($record[0]) >= mktime(0, 0, 0, 1, 1, 2010)
                && strtotime($record[0]) <= mktime(23, 59, 59, 12, 31, 2012))
            {
                $field01_major++;
            }
            else if (strtotime($record[0]) >= mktime(0, 0, 0, 1, 1, 2013)
                && strtotime($record[0]) <= mktime(23, 59, 59, 12, 31, 2015))
            {
                $field01_minor++;
            }
        }
        $this->assertEquals(100, $field01_major + $field01_minor);
        $this->assertLessThan($field01_major, $field01_minor * self::WEIGHT_9_COEFFICIENT);
    }

    public function test_高頻度で２０１０～２０１２年・低頻度で２０１３～２０１５年の日時を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        $field01_major = 0;
        $field01_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $record[0]);
            if (strtotime($record[0]) >= mktime(0, 0, 0, 1, 1, 2010)
                && strtotime($record[0]) <= mktime(23, 59, 59, 12, 31, 2012))
            {
                $field01_major++;
            }
            else if (strtotime($record[0]) >= mktime(0, 0, 0, 1, 1, 2013)
                && strtotime($record[0]) <= mktime(23, 59, 59, 12, 31, 2015))
            {
                $field01_minor++;
            }
        }
        $this->assertEquals(100, $field01_major + $field01_minor);
        $this->assertLessThan($field01_major, $field01_minor * self::WEIGHT_9_COEFFICIENT);
    }

    public function test_マイクロ秒単位で高頻度で２０１０～２０１２年・低頻度で２０１３～２０１５年の日時を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        $field01_major = 0;
        $field01_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/', $record[0]);
            if (strtotime($record[0]) >= mktime(0, 0, 0, 1, 1, 2010)
                && strtotime($record[0]) <= mktime(23, 59, 59, 12, 31, 2012))
            {
                $field01_major++;
            }
            else if (strtotime($record[0]) >= mktime(0, 0, 0, 1, 1, 2013)
                && strtotime($record[0]) <= mktime(23, 59, 59, 12, 31, 2015))
            {
                $field01_minor++;
            }
        }
        $this->assertEquals(100, $field01_major + $field01_minor);
        $this->assertLessThan($field01_major, $field01_minor * self::WEIGHT_9_COEFFICIENT);
    }

    public function test_マイクロ秒単位で高頻度で２０１０～２０１２年・低頻度で２０１３～２０１５年の日時を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        $field01_major = 0;
        $field01_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/', $record[0]);
            if (strtotime($record[0]) >= mktime(0, 0, 0, 1, 1, 2010)
                && strtotime($record[0]) <= mktime(23, 59, 59, 12, 31, 2012))
            {
                $field01_major++;
            }
            else if (strtotime($record[0]) >= mktime(0, 0, 0, 1, 1, 2013)
                && strtotime($record[0]) <= mktime(23, 59, 59, 12, 31, 2015))
            {
                $field01_minor++;
            }
        }
        $this->assertEquals(100, $field01_major + $field01_minor);
        $this->assertLessThan($field01_major, $field01_minor * self::WEIGHT_9_COEFFICIENT);
    }

    public function test_現在のタイムスタンプを生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $timestamp_lower = time();
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $timestamp_higher = time();
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d+$/', $record[0]);
            $this->assertGreaterThanOrEqual($timestamp_lower, $record[0]);
            $this->assertLessThanOrEqual($timestamp_higher, $record[0]);
        }
    }

    public function test_現在のタイムスタンプを生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $timestamp_lower = time();
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $timestamp_higher = time();
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d+$/', $record[0]);
            $this->assertGreaterThanOrEqual($timestamp_lower, $record[0]);
            $this->assertLessThanOrEqual($timestamp_higher, $record[0]);
        }
    }

    public function test_２０１０～２０１５年のタイムスタンプを生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d+$/', $record[0]);
            $this->assertGreaterThanOrEqual(mktime(0, 0, 0, 1, 1, 2010), $record[0]);
            $this->assertLessThanOrEqual(mktime(23, 59, 59, 12, 31, 2015), $record[0]);
        }
    }

    public function test_２０１０～２０１５年のタイムスタンプを生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d+$/', $record[0]);
            $this->assertGreaterThanOrEqual(mktime(0, 0, 0, 1, 1, 2010), $record[0]);
            $this->assertLessThanOrEqual(mktime(23, 59, 59, 12, 31, 2015), $record[0]);
        }
    }

    public function test_マイクロ秒単位で２０１０～２０１５年のタイムスタンプを生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d+?\.\d{6}$/', $record[0]);
            $this->assertGreaterThanOrEqual(mktime(0, 0, 0, 1, 1, 2010), $record[0]);
            $this->assertLessThanOrEqual(mktime(23, 59, 59, 12, 31, 2015), $record[0]);
        }
    }

    public function test_マイクロ秒単位で２０１０～２０１５年のタイムスタンプを生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
                $this->assertRegExp('/^\d+?\.\d{6}$/', $record[0]);
            $this->assertGreaterThanOrEqual(mktime(0, 0, 0, 1, 1, 2010), $record[0]);
            $this->assertLessThanOrEqual(mktime(23, 59, 59, 12, 31, 2015), $record[0]);
        }
    }

    public function test_高頻度で２０１０～２０１２年・低頻度で２０１３～２０１５年のタイムスタンプを生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        $field01_major = 0;
        $field01_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d+$/', $record[0]);
            if ($record[0] >= mktime(0, 0, 0, 1, 1, 2010)
                && $record[0] <= mktime(23, 59, 59, 12, 31, 2012))
            {
                $field01_major++;
            }
            else if ($record[0] >= mktime(0, 0, 0, 1, 1, 2013)
                && $record[0] <= mktime(23, 59, 59, 12, 31, 2015))
            {
                $field01_minor++;
            }
        }
        $this->assertEquals(100, $field01_major + $field01_minor);
        $this->assertLessThan($field01_major, $field01_minor * self::WEIGHT_9_COEFFICIENT);
    }

    public function test_高頻度で２０１０～２０１２年・低頻度で２０１３～２０１５年のタイムスタンプを生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        $field01_major = 0;
        $field01_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d+$/', $record[0]);
            if ($record[0] >= mktime(0, 0, 0, 1, 1, 2010)
                && $record[0] <= mktime(23, 59, 59, 12, 31, 2012))
            {
                $field01_major++;
            }
            else if ($record[0] >= mktime(0, 0, 0, 1, 1, 2013)
                && $record[0] <= mktime(23, 59, 59, 12, 31, 2015))
            {
                $field01_minor++;
            }
        }
        $this->assertEquals(100, $field01_major + $field01_minor);
        $this->assertLessThan($field01_major, $field01_minor * self::WEIGHT_9_COEFFICIENT);
    }

    public function test_マイクロ秒単位で高頻度で２０１０～２０１２年・低頻度で２０１３～２０１５年のタイムスタンプを生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        $field01_major = 0;
        $field01_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d+?\.\d{6}$/', $record[0]);
            if ($record[0] >= mktime(0, 0, 0, 1, 1, 2010)
                && $record[0] <= mktime(23, 59, 59, 12, 31, 2012))
            {
                $field01_major++;
            }
            else if ($record[0] >= mktime(0, 0, 0, 1, 1, 2013)
                && $record[0] <= mktime(23, 59, 59, 12, 31, 2015))
            {
                $field01_minor++;
            }
        }
        $this->assertEquals(100, $field01_major + $field01_minor);
        $this->assertLessThan($field01_major, $field01_minor * self::WEIGHT_9_COEFFICIENT);
    }

    public function test_マイクロ秒単位で高頻度で２０１０～２０１２年・低頻度で２０１３～２０１５年のタイムスタンプを生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        $field01_major = 0;
        $field01_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d+?\.\d{6}$/', $record[0]);
            if ($record[0] >= mktime(0, 0, 0, 1, 1, 2010)
                && $record[0] <= mktime(23, 59, 59, 12, 31, 2012))
            {
                $field01_major++;
            }
            else if ($record[0] >= mktime(0, 0, 0, 1, 1, 2013)
                && $record[0] <= mktime(23, 59, 59, 12, 31, 2015))
            {
                $field01_minor++;
            }
        }
        $this->assertEquals(100, $field01_major + $field01_minor);
        $this->assertLessThan($field01_major, $field01_minor * self::WEIGHT_9_COEFFICIENT);
    }

    public function test_パターンクラスを使い携帯電話番号を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^0[89]0\d{8}$/', $record[0]);
        }
    }

    public function test_パターンクラスを使い携帯電話番号を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^0[89]0\d{8}$/', $record[0]);
        }
    }

    public function test_ドル記号ありでパターンクラスを使い携帯電話番号を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^0[89]0\d{8}$/', $record[0]);
        }
    }

    public function test_ドル記号ありでパターンクラスを使い携帯電話番号を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^0[89]0\d{8}$/', $record[0]);
        }
    }

    public function test_重み付けありパターンから生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        $field01_common = 0;
        $field01_uncommon = 0;
        $field01_rare = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            if ($record[0] == 'common')
            {
                $field01_common++;
            }
            else if ($record[0] == 'uncommon')
            {
                $field01_uncommon++;
            }
            else if ($record[0] == 'rare')
            {
                $field01_rare++;
            }
        }
        $this->assertEquals(100, $field01_common + $field01_uncommon + $field01_rare);
        $this->assertLessThan($field01_common, $field01_uncommon);
        $this->assertLessThan($field01_uncommon, $field01_rare);
    }

    public function test_重み付けありパターンから生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        $field01_common = 0;
        $field01_uncommon = 0;
        $field01_rare = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            if ($record[0] == 'common')
            {
                $field01_common++;
            }
            else if ($record[0] == 'uncommon')
            {
                $field01_uncommon++;
            }
            else if ($record[0] == 'rare')
            {
                $field01_rare++;
            }
        }
        $this->assertEquals(100, $field01_common + $field01_uncommon + $field01_rare);
        $this->assertLessThan($field01_common, $field01_uncommon);
        $this->assertLessThan($field01_uncommon, $field01_rare);
    }

    public function test_文字数指定重み付けありパターンから生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        $field01_common = 0;
        $field01_uncommon = 0;
        $field01_rare = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals(10, mb_strlen($record[0], 'UTF-8'));
            for ($i = 0; $i < 10; $i++)
            {
                if (mb_substr($record[0], $i, 1, 'UTF-8') == '○')
                {
                    $field01_common++;
                }
                else if (mb_substr($record[0], $i, 1, 'UTF-8') == '△')
                {
                    $field01_uncommon++;
                }
                else if (mb_substr($record[0], $i, 1, 'UTF-8') == '×')
                {
                    $field01_rare++;
                }
            }
        }
        $this->assertEquals(10 * 100, $field01_common + $field01_uncommon + $field01_rare);
        $this->assertLessThan($field01_common, $field01_uncommon);
        $this->assertLessThan($field01_uncommon, $field01_rare);
    }

    public function test_文字数指定重み付けありパターンから生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        $field01_common = 0;
        $field01_uncommon = 0;
        $field01_rare = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertEquals(10, mb_strlen($record[0], 'UTF-8'));
            for ($i = 0; $i < 10; $i++)
            {
                if (mb_substr($record[0], $i, 1, 'UTF-8') == '○')
                {
                    $field01_common++;
                }
                else if (mb_substr($record[0], $i, 1, 'UTF-8') == '△')
                {
                    $field01_uncommon++;
                }
                else if (mb_substr($record[0], $i, 1, 'UTF-8') == '×')
                {
                    $field01_rare++;
                }
            }
        }
        $this->assertEquals(10 * 100, $field01_common + $field01_uncommon + $field01_rare);
        $this->assertLessThan($field01_common, $field01_uncommon);
        $this->assertLessThan($field01_uncommon, $field01_rare);
    }

    public function test_指定した合計文字数と各パターンの文字数で各パターンの文字から生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^[1-5]{8}main[1-3][a-e]{4}$/', $record[0]);
        }
    }

    public function test_指定した合計文字数と各パターンの文字数で各パターンの文字から生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^[1-5]{8}main[1-3][a-e]{4}$/', $record[0]);
        }
    }

    public function test_指定した範囲の合計文字数と各パターンの文字数で各パターンの文字から生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^[1-5]{8}(?:len6__|len8____)[a-e]{4}$/', $record[0]);
        }
    }

    public function test_指定した範囲の合計文字数と各パターンの文字数で各パターンの文字から生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^[1-5]{8}(?:len6__|len8____)[a-e]{4}$/', $record[0]);
        }
    }

    public function test_テーブル削除ありでDBより郵便番号を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 2000, $data);
        $field01_major = 0;
        $field01_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d{7}$/', $record[0]);
            if (substr($record[0], 0, 1) == '1')
            {
                $field01_major++;
            }
            else if (substr($record[0], 0, 1) == '0')
            {
                $field01_minor++;
            }
        }
        $this->assertLessThan($field01_major, $field01_minor);
        $is_not_dropped = FALSE;
        $db = new \PDO(
            "mysql:dbname=tdg;host=localhost;port=3306;charset=utf8",
            'tdg', 'tdgpass',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NUM]);
        $stmt = $db->query('SHOW TABLES');
        while ($row = $stmt->fetch())
        {
            if ($row[0] == 'a') $is_not_dropped = TRUE;
        }
        $this->assertFalse($is_not_dropped);
    }

    public function test_テーブル削除ありでDBより郵便番号を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 2000, $data);
        $field01_major = 0;
        $field01_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d{7}$/', $record[0]);
            if (substr($record[0], 0, 1) == '1')
            {
                $field01_major++;
            }
            else if (substr($record[0], 0, 1) == '0')
            {
                $field01_minor++;
            }
        }
        $this->assertLessThan($field01_major, $field01_minor);
        $is_not_dropped = FALSE;
        $db = new \PDO(
            "mysql:dbname=tdg;host=localhost;port=3306;charset=utf8",
            'tdg', 'tdgpass',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NUM]);
        $stmt = $db->query('SHOW TABLES');
        while ($row = $stmt->fetch())
        {
            if ($row[0] == 'a') $is_not_dropped = TRUE;
        }
        $this->assertFalse($is_not_dropped);
    }

    public function test_テーブル削除無しでDBより郵便番号を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 2000, $data);
        $field01_major = 0;
        $field01_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d{7}$/', $record[0]);
            if (substr($record[0], 0, 1) == '1')
            {
                $field01_major++;
            }
            else if (substr($record[0], 0, 1) == '0')
            {
                $field01_minor++;
            }
        }
        $this->assertLessThan($field01_major, $field01_minor);
        $is_not_dropped = FALSE;
        $db = new \PDO(
            "mysql:dbname=tdg;host=localhost;port=3306;charset=utf8",
            'tdg', 'tdgpass',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NUM]);
        $stmt = $db->query('SHOW TABLES');
        while ($row = $stmt->fetch())
        {
            if ($row[0] == 'a') $is_not_dropped = TRUE;
        }
        $this->assertTrue($is_not_dropped);
    }

    public function test_テーブル削除無しでDBより郵便番号を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 2000, $data);
        $field01_major = 0;
        $field01_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d{7}$/', $record[0]);
            if (substr($record[0], 0, 1) == '1')
            {
                $field01_major++;
            }
            else if (substr($record[0], 0, 1) == '0')
            {
                $field01_minor++;
            }
        }
        $this->assertLessThan($field01_major, $field01_minor);
        $is_not_dropped = FALSE;
        $db = new \PDO(
            "mysql:dbname=tdg;host=localhost;port=3306;charset=utf8",
            'tdg', 'tdgpass',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NUM]);
        $stmt = $db->query('SHOW TABLES');
        while ($row = $stmt->fetch())
        {
            if ($row[0] == 'a') $is_not_dropped = TRUE;
        }
        $this->assertTrue($is_not_dropped);
    }

    /**
     * @depends test_テーブル削除無しでDBより郵便番号を生成_json
     * @depends test_テーブル削除無しでDBより郵便番号を生成_yml
     */
    public function test_先頭にパターンを付けてDBより郵便番号を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^head[1-3]_\d{7}$/', $record[0]);
        }
    }

    /**
     * @depends test_テーブル削除無しでDBより郵便番号を生成_json
     * @depends test_テーブル削除無しでDBより郵便番号を生成_yml
     */
    public function test_先頭にパターンを付けてDBより郵便番号を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^head[1-3]_\d{7}$/', $record[0]);
        }
    }

    /**
     * @depends test_テーブル削除無しでDBより郵便番号を生成_json
     * @depends test_テーブル削除無しでDBより郵便番号を生成_yml
     */
    public function test_末尾にパターンを付けてDBより郵便番号を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d{7}_tail[1-3]$/', $record[0]);
        }
    }

    /**
     * @depends test_テーブル削除無しでDBより郵便番号を生成_json
     * @depends test_テーブル削除無しでDBより郵便番号を生成_yml
     */
    public function test_末尾にパターンを付けてDBより郵便番号を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^\d{7}_tail[1-3]$/', $record[0]);
        }
    }

    /**
     * @depends test_テーブル削除無しでDBより郵便番号を生成_json
     * @depends test_テーブル削除無しでDBより郵便番号を生成_yml
     */
    public function test_先頭に重み付けありのパターンを付けてDBより郵便番号を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        $field01_common = 0;
        $field01_uncommon = 0;
        $field01_rare = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^head[1-3]_\d{7}$/', $record[0]);
            if (strpos($record[0], 'head1_') === 0)
            {
                $field01_common++;
            }
            else if (strpos($record[0], 'head2_') === 0)
            {
                $field01_uncommon++;
            }
            else if (strpos($record[0], 'head3_') === 0)
            {
                $field01_rare++;
            }
        }
        $this->assertEquals(100, $field01_common + $field01_uncommon + $field01_rare);
        $this->assertLessThan($field01_common, $field01_uncommon);
        $this->assertLessThan($field01_uncommon, $field01_rare);
    }

    /**
     * @depends test_テーブル削除無しでDBより郵便番号を生成_json
     * @depends test_テーブル削除無しでDBより郵便番号を生成_yml
     */
  public function test_先頭に重み付けありのパターンを付けてDBより郵便番号を生成_yml()
  {
      $_fn = explode('_', __FUNCTION__);
      $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
          . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
      if (strpos(PHP_OS, 'WIN') === 0)
      {
          $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
      }
      $this->assertFileExists($config_filepath);
      $tdg = new TDG($config_filepath);
      $tdg->main(FALSE, TRUE);
      $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
      $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
      $this->assertCount(1 + 100, $data);
      $field01_common = 0;
      $field01_uncommon = 0;
      $field01_rare = 0;
      foreach ($data as $ir => $record)
      {
          if (!$ir)
          {
              $this->assertEquals('"field01"' . "\n", $record);
              continue;
          }
          $record = str_getcsv(str_replace("\n", '', $record));
          $this->assertCount(1, $record);
          $this->assertRegExp('/^head[1-3]_\d{7}$/', $record[0]);
          if (strpos($record[0], 'head1_') === 0)
          {
              $field01_common++;
          }
          else if (strpos($record[0], 'head2_') === 0)
          {
              $field01_uncommon++;
          }
          else if (strpos($record[0], 'head3_') === 0)
          {
              $field01_rare++;
          }
      }
      $this->assertEquals(100, $field01_common + $field01_uncommon + $field01_rare);
      $this->assertLessThan($field01_common, $field01_uncommon);
      $this->assertLessThan($field01_uncommon, $field01_rare);
  }

    /**
     * @depends test_テーブル削除無しでDBより郵便番号を生成_json
     * @depends test_テーブル削除無しでDBより郵便番号を生成_yml
     */
    public function test_１からシーケンス番号・アンダースコア・DBより郵便番号を生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^' . $ir . '_\d{7}$/', $record[0]);
        }
    }

    /**
     * @depends test_テーブル削除無しでDBより郵便番号を生成_json
     * @depends test_テーブル削除無しでDBより郵便番号を生成_yml
     */
    public function test_１からシーケンス番号・アンダースコア・DBより郵便番号を生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'tdg.csv');
        $this->assertCount(1 + 100, $data);
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"field01"' . "\n", $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(1, $record);
            $this->assertRegExp('/^' . $ir . '_\d{7}$/', $record[0]);
        }
    }

    /**
     * @group shakedown
     */
    public function test_実際の利用を想定したユーザーテーブルデータの生成_json()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->json_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'users.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'users.csv');
        $this->assertCount(1 + 2000, $data);
        $family_name_rank1 = 0;
        $family_name_rank30 = 0;
        $sex_major = 0;
        $sex_minor = 0;
        $post_code_major = 0;
        $post_code_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"id","user_id","password","family_name","first_name","sex",'
                    . '"post_code","pref","city","town","address","delete_flg","created_at","updated_at"' . "\n",
                    $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(14, $record);
            $this->assertEquals($ir, $record[0]);
            $this->assertRegExp('/^[a-z]{4}[0-9]{4}$/', $record[1]);
            $this->assertRegExp('/^[0-9a-f]{32}$/', $record[2]);
            $this->assertGreaterThan(0, strlen($record[3]));
            if ($record[3] == '佐藤')
            {
                $family_name_rank1++;
            }
            else if ($record[3] == '石井')
            {
                $family_name_rank30++;
            }
            if ($record[5] == '1')
            {
                $this->assertContains($record[4], ['一郎', '二郎', '三郎', '四郎', '五郎']);
            }
            else if ($record[5] == '2')
            {
                $this->assertContains($record[4], ['花子', '春子', '秋子', '景子', '洋子']);
            }
            $this->assertContains($record[5], ['1', '2']);
            if ($record[5] == '2')
            {
                $sex_major++;
            }
            else if ($record[5] == '1')
            {
                $sex_minor++;
            }
            $this->assertRegExp('/^\d{7}$/', $record[6]);
            if (substr($record[6], 0, 1) == '1')
            {
                $post_code_major++;
            }
            else if (substr($record[6], 0, 1) == '0')
            {
                $post_code_minor++;
            }
            $this->assertRegExp('/(?:都|道|府|県)$/',
                $record[7]);
            $this->assertRegExp('/^(?:１|２|３|４|５)－(?:１|２|３|４|５|６|７|８|９|１０)－'
                . '(?:１|２|３|４|５|６|７|８|９|１０|１１|１２|１３|１４|１５|１６|１７|１８|１９|２０)$/',
                $record[10]);
            $this->assertEquals(0, $record[11]);
            $this->assertEquals('2015-01-01 00:00:00', $record[12]);
            $this->assertEquals('2015-01-01 00:00:00', $record[13]);
        }
        $this->assertLessThan($family_name_rank1, $family_name_rank30);
        $this->assertLessThan($sex_major, $sex_minor);
        $this->assertLessThan($post_code_major, $post_code_minor);
    }

    public function test_実際の利用を想定したユーザーテーブルデータの生成_yml()
    {
        $_fn = explode('_', __FUNCTION__);
        $config_filepath = $this->yml_config_dir . DIRECTORY_SEPARATOR
            . implode('', array_slice($_fn, 1, -1)) . '.' . implode('', array_slice($_fn, -1));
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $config_filepath = mb_convert_encoding($config_filepath, 'SJIS-win', 'UTF-8');
        }
        $this->assertFileExists($config_filepath);
        $tdg = new TDG($config_filepath);
        $tdg->main(FALSE, TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'users.csv');
        $data = file($this->app_dir . DIRECTORY_SEPARATOR . 'users.csv');
        $this->assertCount(1 + 2000, $data);
        $family_name_rank1 = 0;
        $family_name_rank30 = 0;
        $sex_major = 0;
        $sex_minor = 0;
        $post_code_major = 0;
        $post_code_minor = 0;
        foreach ($data as $ir => $record)
        {
            if (!$ir)
            {
                $this->assertEquals('"id","user_id","password","family_name","first_name","sex",'
                    . '"post_code","pref","city","town","address","delete_flg","created_at","updated_at"' . "\n",
                    $record);
                continue;
            }
            $record = str_getcsv(str_replace("\n", '', $record));
            $this->assertCount(14, $record);
            $this->assertEquals($ir, $record[0]);
            $this->assertRegExp('/^[a-z]{4}[0-9]{4}$/', $record[1]);
            $this->assertRegExp('/^[0-9a-f]{32}$/', $record[2]);
            $this->assertGreaterThan(0, strlen($record[3]));
            if ($record[3] == '佐藤')
            {
                $family_name_rank1++;
            }
            else if ($record[3] == '石井')
            {
                $family_name_rank30++;
            }
            if ($record[5] == '1')
            {
                $this->assertContains($record[4], ['一郎', '二郎', '三郎', '四郎', '五郎']);
            }
            else if ($record[5] == '2')
            {
                $this->assertContains($record[4], ['花子', '春子', '秋子', '景子', '洋子']);
            }
            $this->assertContains($record[5], ['1', '2']);
            if ($record[5] == '2')
            {
                $sex_major++;
            }
            else if ($record[5] == '1')
            {
                $sex_minor++;
            }
            $this->assertRegExp('/^\d{7}$/', $record[6]);
            if (substr($record[6], 0, 1) == '1')
            {
                $post_code_major++;
            }
            else if (substr($record[6], 0, 1) == '0')
            {
                $post_code_minor++;
            }
            $this->assertRegExp('/(?:都|道|府|県)$/',
                $record[7]);
            $this->assertRegExp('/^(?:１|２|３|４|５)－(?:１|２|３|４|５|６|７|８|９|１０)－'
                . '(?:１|２|３|４|５|６|７|８|９|１０|１１|１２|１３|１４|１５|１６|１７|１８|１９|２０)$/',
                $record[10]);
            $this->assertEquals(0, $record[11]);
            $this->assertEquals('2015-01-01 00:00:00', $record[12]);
            $this->assertEquals('2015-01-01 00:00:00', $record[13]);
        }
        $this->assertLessThan($family_name_rank1, $family_name_rank30);
        $this->assertLessThan($sex_major, $sex_minor);
        $this->assertLessThan($post_code_major, $post_code_minor);
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
