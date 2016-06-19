<?php
use rokugasenpai\TestDataGenerator\TDG;

class TDGTest extends PHPUnit_Framework_TestCase
{
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
        $GLOBALS['test_case'] = __CLASS__;
        $GLOBALS['test_method'] = $this->getName();
    }

    public function test_実際の利用を想定したユーザーテーブルデータの生成_json()
    {
        $GLOBALS['benchmark'] = '1';
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
        $tdg->main(TRUE);
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

    /**
     * @depends test_実際の利用を想定したユーザーテーブルデータの生成_json
     * @depends test_実際の利用を想定したユーザーテーブルデータの生成_yml
     */
    public function test_出力ファイルをsqlに指定して実際の利用を想定したユーザーテーブルデータの生成_json()
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
        $tdg->main(TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'users.sql');
        $sqls = file($this->app_dir . DIRECTORY_SEPARATOR . 'users.sql');
        $this->assertCount(2000 / 1000, $sqls);
        // 細かいチェックは行わない。
        foreach ($sqls as $sql)
        {
            $parts = explode('), ', $sql);
            foreach ($parts as $ip => $part)
            {
                if (!$ip)
                {
                    $this->assertRegExp('/^INSERT INTO `users` '
                        . '\(`id`, `user_id`, `password`, `family_name`, `first_name`, `sex`, '
                        . '`post_code`, `pref`, `city`, `town`, `address`, '
                        . '`delete_flg`, `created_at`, `updated_at`\) VALUES '
                        . '\(\'\d+?\', \'[0-9a-z]{8}\', \'[0-9a-f]{32}\', \'.+?\', \'.+?\', \'[12]\', '
                        . '\'\d{7}\', \'.+?\', \'.+?\', \'.+?\', \'.+?\', '
                        . '\'0\', \'2015-01-01 00:00:00\', \'2015-01-01 00:00:00\'$/', $part);
                    continue;
                }
                if ($ip == count($parts) - 1)
                {

                    $this->assertRegExp('/^\(\'\d+?\', \'[0-9a-z]{8}\', \'[0-9a-f]{32}\', \'\S+?\', \'\S+?\', '
                        . '\'[12]\', \'\d{7}\', \'.+?\', \'.+?\', \'.+?\', \'.+?\', '
                        . '\'0\', \'2015-01-01 00:00:00\', \'2015-01-01 00:00:00\'\);$/', $part);
                    break;
                }
                $this->assertRegExp('/^\(\'\d+?\', \'[0-9a-z]{8}\', \'[0-9a-f]{32}\', \'\S+?\', \'\S+?\', '
                    . '\'[12]\', \'\d{7}\', \'.+?\', \'.+?\', \'.+?\', \'.+?\', '
                    . '\'0\', \'2015-01-01 00:00:00\', \'2015-01-01 00:00:00\'$/', $part);
            }
        }
    }

    /**
     * @depends test_実際の利用を想定したユーザーテーブルデータの生成_json
     * @depends test_実際の利用を想定したユーザーテーブルデータの生成_yml
     */
    public function test_出力ファイルをsqlに指定して実際の利用を想定したユーザーテーブルデータの生成_yml()
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
        $tdg->main(TRUE);
        $this->assertFileExists($this->app_dir . DIRECTORY_SEPARATOR . 'users.sql');
        $sqls = file($this->app_dir . DIRECTORY_SEPARATOR . 'users.sql');
        $this->assertCount(2000 / 1000, $sqls);
        foreach ($sqls as $sql)
        {
            $parts = explode('), ', $sql);
            foreach ($parts as $ip => $part)
            {
                if (!$ip)
                {
                    $this->assertRegExp('/^INSERT INTO `users` '
                        . '\(`id`, `user_id`, `password`, `family_name`, `first_name`, `sex`, '
                        . '`post_code`, `pref`, `city`, `town`, `address`, '
                        . '`delete_flg`, `created_at`, `updated_at`\) VALUES '
                        . '\(\'\d+?\', \'[0-9a-z]{8}\', \'[0-9a-f]{32}\', \'.+?\', \'.+?\', \'[12]\', '
                        . '\'\d{7}\', \'.+?\', \'.+?\', \'.+?\', \'.+?\', '
                        . '\'0\', \'2015-01-01 00:00:00\', \'2015-01-01 00:00:00\'$/', $part);
                    continue;
                }
                if ($ip == count($parts) - 1)
                {
                    $this->assertRegExp('/^\(\'\d+?\', \'[0-9a-z]{8}\', \'[0-9a-f]{32}\', \'\S+?\', \'\S+?\', '
                        . '\'[12]\', \'\d{7}\', \'.+?\', \'.+?\', \'.+?\', \'.+?\', '
                        . '\'0\', \'2015-01-01 00:00:00\', \'2015-01-01 00:00:00\'\);$/', $part);
                    break;
                }
                $this->assertRegExp('/^\(\'\d+?\', \'[0-9a-z]{8}\', \'[0-9a-f]{32}\', \'\S+?\', \'\S+?\', '
                    . '\'[12]\', \'\d{7}\', \'.+?\', \'.+?\', \'.+?\', \'.+?\', '
                    . '\'0\', \'2015-01-01 00:00:00\', \'2015-01-01 00:00:00\'$/', $part);
            }
        }
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
