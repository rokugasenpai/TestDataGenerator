<?php
use rokugasenpai\TestDataGenerator\Util;

class UtilTest extends PHPUnit_Framework_TestCase
{
    public function test_is_numeric_uint_intの正の整数()
    {
        $this->assertTrue(Util::is_numeric_uint(1));
    }

    public function test_is_numeric_uint_intのゼロ()
    {
        $this->assertTrue(Util::is_numeric_uint(0));
    }

    public function test_is_numeric_uint_stringの正の整数()
    {
        $this->assertTrue(Util::is_numeric_uint('1'));
    }

    public function test_is_numeric_uint_stringのゼロ()
    {
        $this->assertTrue(Util::is_numeric_uint('0'));
    }

    public function test_is_numeric_uint_intの負の整数()
    {
        $this->assertFalse(Util::is_numeric_uint(-1));
    }

    public function test_is_numeric_uint_stringの負の整数()
    {
        $this->assertFalse(Util::is_numeric_uint('-1'));
    }

    public function test_is_numeric_uint_floatの正の浮動小数点数()
    {
        $this->assertFalse(Util::is_numeric_uint(3.14));
    }

    public function test_is_numeric_uint_NAN()
    {
        $this->assertFalse(Util::is_numeric_uint(NAN));
    }

    public function test_is_numeric_uint_NULL()
    {
        $this->assertFalse(Util::is_numeric_uint(NULL));
    }

    public function test_is_numeric_uint_配列()
    {
        $this->assertFalse(Util::is_numeric_uint([1]));
    }

    public function test_array_depth_空の配列()
    {
        $this->assertSame(0, Util::array_depth([]));
    }

    public function test_array_depth_１次元の配列()
    {
        $this->assertSame(1, Util::array_depth([
            '1-1', '1-2'
        ]));
    }

    public function test_array_depth_２次元の配列()
    {
        $this->assertSame(2, Util::array_depth([
            '1-1', '1-2', [
                '2-1', '2-2'
            ]
        ]));
    }

    public function test_array_depth_オブジェクト()
    {
        $this->assertSame(0, Util::array_depth(new DateTime()));
    }

    public function test_s_to_hms_小数で1時間以上()
    {
        $this->assertEquals('1時間14分4.4秒', Util::s_to_hms(4444.4));
    }

    public function test_s_to_hms_1時間以上()
    {
        $this->assertEquals('1時間14分4秒', Util::s_to_hms(4444));
    }

    public function test_s_to_hms_1時間()
    {
        $this->assertEquals('1時間', Util::s_to_hms(3600));
    }

    public function test_s_to_hms_1分以上()
    {
        $this->assertEquals('7分24秒', Util::s_to_hms(444));
    }

    public function test_s_to_hms_1分()
    {
        $this->assertEquals('1分', Util::s_to_hms(60));
    }

    public function test_s_to_hms_1分未満()
    {
        $this->assertEquals('44秒', Util::s_to_hms(44));
    }

    public function test_s_to_hms_1秒未満()
    {
        $this->assertEquals('0.4秒', Util::s_to_hms(0.4));
    }

    public function test_s_to_hms_数値以外()
    {
        $this->assertFalse(Util::s_to_hms('abc'));
    }

    public function test_s_to_hms_負数()
    {
        $this->assertFalse(Util::s_to_hms(-1));
    }

    public function test_json_last_error_msg_正常なJSONをデコード()
    {
        @json_decode('{"key":"value"}', TRUE);
        $this->assertSame('', Util::json_last_error_msg());
    }

    public function test_json_last_error_msg_構文エラーなJSONをデコード()
    {
        @json_decode("{'key':'value'}", TRUE);
        $this->assertSame('構文エラー。', Util::json_last_error_msg());
    }

    public function test_json_last_error_msg_NANを含む配列をJSONにエンコード()
    {
        @json_encode(['key' => NAN]);
        if (version_compare(PHP_VERSION, '5.5') >= 0)
        {
            $this->assertSame('エンコード対象の値に NAN あるいは INF が含まれています。', Util::json_last_error_msg());
        }
        else
        {
            $this->assertSame('', Util::json_last_error_msg());
        }
    }

    public function test_json_encode_JSONにエンコード可能な連想配列()
    {
        $this->assertSame('{"key":"value"}', Util::json_encode(['key' => 'value']));
    }

    public function test_json_encode_スカラ値の一種である文字列()
    {
        $this->assertSame('', Util::json_encode('value'));
    }

    public function test_json_encode_NANとINFを含む配列()
    {
        $this->assertSame('["NAN","INF"]', Util::json_encode([NAN, INF]));
    }

    public function test_json_decode_正常なJSON()
    {
        $this->assertSame(['key' => 'value'], Util::json_decode('{"key": "value"}'));
    }

    public function test_json_decode_構文エラーなJSON()
    {
        $this->assertSame('構文エラー。', Util::json_decode("{'key':'value'}"));
    }

    public function test_normalize_charset_Shift_JISをSJISに変換()
    {
        $this->assertEquals('SJIS', Util::normalize_charset('Shift_JIS'));
    }

    public function test_normalize_charset_MySQL用にをUTF8に変換()
    {
        $this->assertEquals('utf8', Util::normalize_charset('UTF-8', TRUE));
    }

    public function test_check_ext_拡張子csv()
    {
        $filepath = (strpos(PHP_OS, 'WIN') === 0) ? 'C:\Users\user\check.csv' : '/home/user/check.csv';
        $this->assertTrue(Util::check_ext($filepath, Util::CSV_EXT));
    }

    public function test_check_ext_拡張子はないがファイル名末尾が拡張子と同じ()
    {
        $filepath = (strpos(PHP_OS, 'WIN') === 0) ? 'C:\Users\user\checkcsv' : '/home/user/checkcsv';
        $this->assertFalse(Util::check_ext($filepath, Util::CSV_EXT));
    }

    public function test_get_data_by_json_file_存在するJSONファイルパス()
    {
        $filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'tdg.json';
        $this->assertFileExists($filepath, 'precondition error.');
        $this->assertArrayHasKey('num_data', Util::get_data_by_json_file($filepath));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function test_get_data_by_json_file_存在しないJSONファイルパス()
    {
        $filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'tsuchinoko.json';
        $this->assertFileNotExists($filepath, 'precondition error.');
        Util::get_data_by_json_file('./config/tsuchinoko.json');
    }

    public function test_get_data_by_yml_file_存在するYMLファイルパス()
    {
        $filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'tdg.yml';
        $this->assertFileExists($filepath, 'precondition error.');
        $this->assertArrayHasKey('num_data', Util::get_data_by_yml_file($filepath));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function test_get_data_by_yml_file_存在しないYMLファイルパス()
    {
        $filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'tsuchinoko.yml';
        $this->assertFileNotExists($filepath, 'precondition error.');
        Util::get_data_by_yml_file($filepath);
    }

    public function test_create_weighted_csv_ヘッダありCSVの重み付け()
    {
        $filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'weight.csv';
        $this->assertFileExists($filepath, 'precondition error.');
        copy($filepath, "{$filepath}.bak");
        $this->assertEquals($filepath, Util::create_weighted_csv(100, $filepath, '', 'num'));
        $this->assertFileExists($filepath);
        $cnt = 0;
        $last_id = 0;
        $num_common = 0;
        $num_uncommon = 0;
        $num_rare = 0;
        $num_unexpected = 0;
        $fp = fopen($filepath, 'r');
        while (($record = fgetcsv($fp)) !== FALSE)
        {
            if (!$cnt)
            {
                $cnt++;
                continue;
            }
            $last_id = $record[0];
            if ($record[1] == 'common') $num_common++;
            else if ($record[1] == 'uncommon') $num_uncommon++;
            else if ($record[1] == 'rare') $num_rare++;
            else $num_unexpected++;
            $cnt++;
        }
        fclose($fp);
        $this->assertEquals(101, $cnt);
        $this->assertEquals(100, $last_id);
        $this->assertLessThan($num_common, $num_uncommon);
        $this->assertLessThan($num_uncommon, $num_rare);
        $this->assertGreaterThan(0, $num_common);
        $this->assertGreaterThan(0, $num_uncommon);
        $this->assertGreaterThan(0, $num_rare);
        $this->assertEquals(0, $num_unexpected);
    }

    public function test_create_weighted_csv_ヘッダ無しCSVの重み付け()
    {
        $filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'headerless_weight.csv';
        $this->assertFileExists($filepath, 'precondition error.');
        copy($filepath, "{$filepath}.bak");
        $this->assertEquals($filepath, Util::create_weighted_csv(100, $filepath, '', 2, 1, FALSE));
        $this->assertFileExists($filepath);
        $cnt = 0;
        $last_id = 0;
        $num_common = 0;
        $num_uncommon = 0;
        $num_rare = 0;
        $num_unexpected = 0;
        $fp = fopen($filepath, 'r');
        while (($record = fgetcsv($fp)) !== FALSE)
        {
            $last_id = $record[0];
            if ($record[1] == 'common') $num_common++;
            else if ($record[1] == 'uncommon') $num_uncommon++;
            else if ($record[1] == 'rare') $num_rare++;
            else $num_unexpected++;
            $cnt++;
        }
        fclose($fp);
        $this->assertEquals(100, $cnt);
        $this->assertEquals(100, $last_id);
        $this->assertLessThan($num_common, $num_uncommon);
        $this->assertLessThan($num_uncommon, $num_rare);
        $this->assertGreaterThan(0, $num_common);
        $this->assertGreaterThan(0, $num_uncommon);
        $this->assertGreaterThan(0, $num_rare);
        $this->assertEquals(0, $num_unexpected);
    }

    public function test_create_weighted_csv_引数max_recordsの検証()
    {
        $filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'kana1010.csv';
        $this->assertFileExists($filepath, 'precondition error.');
        $this->assertFalse(Util::create_weighted_csv(100, $filepath, '', 'num', 1, TRUE, TRUE, ',', '"', 1000));
        $this->assertFileExists($filepath);
    }

    public function test_create_weighted_csv_配列の重み付け()
    {
        $input = [
            ['id' => '1', 'name' => 'common', 'num' => '75'],
            ['id' => '2', 'name' => 'uncommon', 'num' => '20'],
            ['id' => '3', 'name' => 'rare', 'num' => '5']
        ];
        $output = Util::create_weighted_csv(100, $input, '', 'num');
        $this->assertNotFalse($output);
        $cnt = 0;
        $last_id = 0;
        $num_common = 0;
        $num_uncommon = 0;
        $num_rare = 0;
        $num_unexpected = 0;
        foreach ($output as $record)
        {
            $last_id = $record['id'];
            if ($record['name'] == 'common') $num_common++;
            else if ($record['name'] == 'uncommon') $num_uncommon++;
            else if ($record['name'] == 'rare') $num_rare++;
            else $num_unexpected++;
            $cnt++;
        }
        $this->assertEquals(100, $cnt);
        $this->assertEquals(100, $last_id);
        $this->assertLessThan($num_common, $num_uncommon);
        $this->assertLessThan($num_uncommon, $num_rare);
        $this->assertGreaterThan(0, $num_common);
        $this->assertGreaterThan(0, $num_uncommon);
        $this->assertGreaterThan(0, $num_rare);
        $this->assertEquals(0, $num_unexpected);
    }

    public function test_csv_to_bulk_insert_データ数１０()
    {
        $input_filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'kana10.csv';
        $output_filepath = substr($input_filepath, 0, strrpos($input_filepath, '.')) . Util::SQL_EXT;
        $this->assertFileExists($input_filepath, 'precondition error.');
        $this->assertEquals($output_filepath, Util::csv_to_bulk_insert($input_filepath));
        $this->assertFileExists($output_filepath);
        $sql_regex = '/^INSERT INTO `kana10` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 10, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';$/s';
        $lines = file($output_filepath);
        $this->assertCount(1, $lines);
        $this->assertRegExp($sql_regex, $lines[0]);
    }

    public function test_csv_to_bulk_insert_データ数１０１０()
    {
        $input_filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'kana1010.csv';
        $output_filepath = substr($input_filepath, 0, strrpos($input_filepath, '.')) . Util::SQL_EXT;
        $this->assertFileExists($input_filepath, 'precondition error.');
        $this->assertEquals($output_filepath, Util::csv_to_bulk_insert($input_filepath));
        $this->assertFileExists($output_filepath);
        $sql_regex_1 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 1000, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';' . PHP_EOL . '$/s';
        $sql_regex_2 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 10, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';$/s';
        $lines = file($output_filepath);
        $this->assertCount(2, $lines);
        $this->assertRegExp($sql_regex_1, $lines[0]);
        $this->assertRegExp($sql_regex_2, $lines[1]);
    }

    public function test_csv_to_bulk_insert_テーブル名変更データ数１０１０()
    {
        $input_filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'kana1010.csv';
        $output_filepath = substr($input_filepath, 0, strrpos($input_filepath, '.')) . Util::SQL_EXT;
        $this->assertFileExists($input_filepath, 'precondition error.');
        $this->assertEquals($output_filepath, Util::csv_to_bulk_insert($input_filepath, '', 'new_kana'));
        $this->assertFileExists($output_filepath);
        $sql_regex_1 = '/^INSERT INTO `new_kana` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 1000, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';' . PHP_EOL . '$/s';
        $sql_regex_2 = '/^INSERT INTO `new_kana` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 10, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';$/s';
        $lines = file($output_filepath);
        $this->assertCount(2, $lines);
        $this->assertRegExp($sql_regex_1, $lines[0]);
        $this->assertRegExp($sql_regex_2, $lines[1]);
    }

    public function test_csv_to_bulk_insert_カラム変更データ数１０１０()
    {
        $input_filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'kana1010.csv';
        $output_filepath = substr($input_filepath, 0, strrpos($input_filepath, '.')) . Util::SQL_EXT;
        $this->assertFileExists($input_filepath, 'precondition error.');
        $this->assertEquals($output_filepath, Util::csv_to_bulk_insert(
            $input_filepath, '', '', ['id', 'kana', 'num']));
        $this->assertFileExists($output_filepath);
        $sql_regex_1 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`\) VALUES '
            . implode(', ', array_fill(0, 1000, '\(\'\d+?\', \'.+?\', \'\d+?\'\)')) . ';' . PHP_EOL . '$/s';
        $sql_regex_2 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`\) VALUES '
            . implode(', ', array_fill(0, 10, '\(\'\d+?\', \'.+?\', \'\d+?\'\)')) . ';$/s';
        $lines = file($output_filepath);
        $this->assertCount(2, $lines);
        $this->assertRegExp($sql_regex_1, $lines[0]);
        $this->assertRegExp($sql_regex_2, $lines[1]);
    }

    public function test_csv_to_bulk_insert_ヘッダ無しデータ数１０１０()
    {
        $input_filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'headerless_kana1010.csv';
        $output_filepath = substr($input_filepath, 0, strrpos($input_filepath, '.')) . Util::SQL_EXT;
        $this->assertFileExists($input_filepath, 'precondition error.');
        $this->assertEquals($output_filepath, Util::csv_to_bulk_insert(
            $input_filepath, '', '', [], FALSE));
        $this->assertFileExists($output_filepath);
        $sql_regex_1 = '/^INSERT INTO `headerless_kana1010` VALUES '
            . implode(', ', array_fill(0, 1000, '\(\'\d+?\', \'.*?\', \'\d+?\', NULL\)')) . ';' . PHP_EOL . '$/s';
        $sql_regex_2 = '/^INSERT INTO `headerless_kana1010` VALUES '
            . implode(', ', array_fill(0, 10, '\(\'\d+?\', \'.*?\', \'\d+?\', NULL\)')) . ';$/s';
        $lines = file($output_filepath);
        $this->assertCount(2, $lines);
        $this->assertRegExp($sql_regex_1, $lines[0]);
        $this->assertRegExp($sql_regex_2, $lines[1]);
    }

    public function test_csv_to_bulk_insert_NOT_NULLデータ数１０１０()
    {
        $input_filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'kana1010.csv';
        $output_filepath = substr($input_filepath, 0, strrpos($input_filepath, '.')) . Util::SQL_EXT;
        $this->assertTrue(is_file($input_filepath), 'precondition error.');
        $this->assertEquals($output_filepath, Util::csv_to_bulk_insert(
            $input_filepath, '', '', [], TRUE, FALSE));
        $this->assertTrue(is_file($output_filepath));
        $sql_regex_1 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 1000, '\(\'\d+?\', \'.+?\', \'\d+?\', \'\'\)')) . ';' . PHP_EOL . '$/s';
        $sql_regex_2 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 10, '\(\'\d+?\', \'.+?\', \'\d+?\', \'\'\)')) . ';$/s';
        $lines = file($output_filepath);
        $this->assertCount(2, $lines);
        $this->assertRegExp($sql_regex_1, $lines[0]);
        $this->assertRegExp($sql_regex_2, $lines[1]);
    }

    public function test_csv_to_bulk_insert_NULL文字列変更データ数１０１０()
    {
        $input_filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'modnull_kana1010.csv';
        $output_filepath = substr($input_filepath, 0, strrpos($input_filepath, '.')) . Util::SQL_EXT;
        $this->assertFileExists($input_filepath, 'precondition error.');
        $this->assertEquals($output_filepath, Util::csv_to_bulk_insert(
            $input_filepath, '', '', [], TRUE, TRUE, '\N'));
        $this->assertFileExists($output_filepath);
        $sql_regex_1 = '/^INSERT INTO `modnull_kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 1000, '\(\'.*?\', \'.*?\', \'.*?\', NULL\)')) . ';' . PHP_EOL . '$/s';
        $sql_regex_2 = '/^INSERT INTO `modnull_kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 10, '\(\'.*?\', \'.*?\', \'.*?\', NULL\)')) . ';$/s';
        $lines = file($output_filepath);
        $this->assertCount(2, $lines);
        $this->assertRegExp($sql_regex_1, $lines[0]);
        $this->assertRegExp($sql_regex_2, $lines[1]);
    }

    public function test_csv_to_bulk_insert_改行文字変更データ数１０１０()
    {
        $input_filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'kana1010.csv';
        $output_filepath = substr($input_filepath, 0, strrpos($input_filepath, '.')) . Util::SQL_EXT;
        $this->assertFileExists($input_filepath, 'precondition error.');
        $eol = (strpos(PHP_OS, 'WIN') === 0) ? "\n" : "\r\n";
        $this->assertEquals($output_filepath, Util::csv_to_bulk_insert(
            $input_filepath, '', '', [], TRUE, TRUE, 'NULL', $eol));
        $this->assertFileExists($output_filepath);
        $sql_regex_1 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 1000, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';' . $eol . '$/s';
        $sql_regex_2 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 10, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';$/s';
        $lines = file($output_filepath);
        $this->assertCount(2, $lines);
        $this->assertRegExp($sql_regex_1, $lines[0]);
        $this->assertEquals($eol, substr($lines[0], 0 - strlen($eol)));
        $this->assertRegExp($sql_regex_2, $lines[1]);
    }

    public function test_csv_to_bulk_insert_引数divisor１００データ数１０１０()
    {
        $input_filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'kana1010.csv';
        $output_filepath = substr($input_filepath, 0, strrpos($input_filepath, '.')) . Util::SQL_EXT;
        $this->assertFileExists($input_filepath, 'precondition error.');
        $this->assertEquals($output_filepath, Util::csv_to_bulk_insert(
            $input_filepath, '', '', [], TRUE, TRUE, 'NULL', PHP_EOL, 100));
        $this->assertFileExists($output_filepath);
        $sql_regex_1 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 100, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';' . PHP_EOL . '$/s';
        $sql_regex_2 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 10, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';$/s';
        $lines = file($output_filepath);
        $this->assertCount(11, $lines);
        $this->assertRegExp($sql_regex_1, $lines[0]);
        $this->assertRegExp($sql_regex_2, $lines[10]);
    }

    public function test_csv_to_bulk_insert_ユニークカラムありデータ数１０１０()
    {
        $input_filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'kana1010.csv';
        $output_filepath = substr($input_filepath, 0, strrpos($input_filepath, '.')) . Util::SQL_EXT;
        $this->assertFileExists($input_filepath, 'precondition error.');
        $this->assertEquals($output_filepath, Util::csv_to_bulk_insert(
            $input_filepath, '', '', [], TRUE, TRUE, 'NULL', PHP_EOL, 1000, ['kana']));
        $this->assertFileExists($output_filepath);
        $sql_regex = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 46, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';$/s';
        $lines = file($output_filepath);
        $this->assertCount(1, $lines);
        $this->assertRegExp($sql_regex, $lines[0]);
    }

    public function test_csv_to_bulk_insert_集計カラムありデータ数１０１０()
    {
        $input_filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'kana1010.csv';
        $output_filepath = substr($input_filepath, 0, strrpos($input_filepath, '.')) . Util::SQL_EXT;
        $this->assertFileExists($input_filepath, 'precondition error.');
        $this->assertEquals($output_filepath, Util::csv_to_bulk_insert(
            $input_filepath, '', '', [], TRUE, TRUE, 'NULL', PHP_EOL, 1000, [], ['num' => 'sum']));
        $this->assertFileExists($output_filepath);
        $sql_regex_1 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`, `empty`, `sum`\) VALUES '
            . implode(', ', array_fill(0, 1000, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL, \'\d+?\'\)'))
            . ';' . PHP_EOL . '$/s';
        $sql_regex_2 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`, `empty`, `sum`\) VALUES '
            . implode(', ', array_fill(0, 10, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL, \'\d+?\'\)')) . ';$/s';
        $lines = file($output_filepath);
        $this->assertCount(2, $lines);
        $this->assertRegExp($sql_regex_1, $lines[0]);
        $this->assertRegExp($sql_regex_2, $lines[1]);
    }

    public function test_csv_to_bulk_insert_前後SQLありデータ数１０１０()
    {
        $input_filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'kana1010.csv';
        $output_filepath = substr($input_filepath, 0, strrpos($input_filepath, '.')) . Util::SQL_EXT;
        $head_sql = 'SET tmp_table_size=268435456, max_heap_table_size=268435456;';
        $tail_sql = 'OPTIMIZE TABLE kana1010;';
        $this->assertFileExists($input_filepath, 'precondition error.');
        $this->assertEquals($output_filepath, Util::csv_to_bulk_insert(
            $input_filepath, '', '', [], TRUE, TRUE, 'NULL', PHP_EOL, 1000, [], [], $head_sql, $tail_sql));
        $this->assertFileExists($output_filepath);
        $sql_regex_1 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 1000, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';' . PHP_EOL . '$/s';
        $sql_regex_2 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 10, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';' . PHP_EOL . '$/s';
        $lines = file($output_filepath);
        $this->assertCount(4, $lines);
        $this->assertRegExp('/^' . $head_sql . PHP_EOL . '$/s', $lines[0]);
        $this->assertRegExp($sql_regex_1, $lines[1]);
        $this->assertRegExp($sql_regex_2, $lines[2]);
        $this->assertRegExp('/^' . $tail_sql . '$/s', $lines[3]);
    }

    public function test_csv_to_bulk_insert_SJISからUTF8に変換データ数１０１０()
    {
        $input_filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'sjis_kana1010.csv';
        $output_filepath = substr($input_filepath, 0, strrpos($input_filepath, '.')) . Util::SQL_EXT;
        $this->assertFileExists($input_filepath, 'precondition error.');
        $this->assertEquals($output_filepath, Util::csv_to_bulk_insert(
            $input_filepath, '', '', [], TRUE, TRUE, 'NULL', PHP_EOL, 1000, [], [], '', '', Util::UTF8, Util::SJIS));
        $this->assertFileExists($output_filepath);
        $sql_regex_1 = '/^INSERT INTO `sjis_kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 1000, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';' . PHP_EOL . '$/s';
        $sql_regex_2 = '/^INSERT INTO `sjis_kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 10, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';$/s';
        $lines = file($output_filepath);
        $this->assertCount(2, $lines);
        $this->assertRegExp($sql_regex_1, $lines[0]);
        $this->assertRegExp($sql_regex_2, $lines[1]);
    }

    public function test_csv_to_bulk_insert_引数max_recordsの検証データ数１０１０()
    {
        $input_filepath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'kana1010.csv';
        $output_filepath = substr($input_filepath, 0, strrpos($input_filepath, '.')) . Util::SQL_EXT;
        $this->assertFileExists($input_filepath, 'precondition error.');
        $this->assertFalse(Util::csv_to_bulk_insert(
            $input_filepath, '', '', [], TRUE, TRUE, 'NULL', PHP_EOL, 1000, [], [], '', '', '', '', ',', '"', 1000));
    }

    public function test_csv_to_bulk_insert_配列でデータ数１０１０()
    {
        $output = Util::csv_to_bulk_insert($this->kana1010, '', 'kana1010');
        $sql_regex_1 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 1000, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';$/';
        $sql_regex_2 = '/^INSERT INTO `kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 10, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';$/';
        $this->assertCount(2, $output);
        $this->assertRegExp($sql_regex_1, $output[0]);
        $this->assertRegExp($sql_regex_2, $output[1]);
    }

    public function test_csv_to_bulk_insert_ヘッダ無し配列でデータ数１０１０()
    {
        $output = Util::csv_to_bulk_insert($this->headerless_kana1010, '', 'headerless_kana1010', [], FALSE);
        $sql_regex_1 = '/^INSERT INTO `headerless_kana1010` VALUES '
            . implode(', ', array_fill(0, 1000, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';$/';
        $sql_regex_2 = '/^INSERT INTO `headerless_kana1010` VALUES '
            . implode(', ', array_fill(0, 10, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';$/';
        $this->assertCount(2, $output);
        $this->assertRegExp($sql_regex_1, $output[0]);
        $this->assertRegExp($sql_regex_2, $output[1]);
    }

    public function test_csv_to_bulk_insert_連想配列でデータ数１０１０()
    {
        $output = Util::csv_to_bulk_insert($this->kana1010, '', 'assoc_kana1010');
        $sql_regex_1 = '/^INSERT INTO `assoc_kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 1000, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';$/';
        $sql_regex_2 = '/^INSERT INTO `assoc_kana1010` \(`id`, `kana`, `num`, `empty`\) VALUES '
            . implode(', ', array_fill(0, 10, '\(\'\d+?\', \'.+?\', \'\d+?\', NULL\)')) . ';$/';
        $this->assertCount(2, $output);
        $this->assertRegExp($sql_regex_1, $output[0]);
        $this->assertRegExp($sql_regex_2, $output[1]);
    }

    public function test_fputcsv_１レコード書き込み()
    {
        $record = ['あ', 'い', 'う', 'え', 'お'];
        $fp = fopen(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'fputcsv.csv', 'w+');
        $this->assertEquals(strlen('"あ","い","う","え","お"' . PHP_EOL), Util::fputcsv($fp, $record));
        $this->assertFileExists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'fputcsv.csv');
        $contents = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'fputcsv.csv');
        $this->assertEquals('"あ","い","う","え","お"' . PHP_EOL, $contents);
    }

    public function test_fputcsv_非NULL１レコード書き込み()
    {
        $record = [NULL];
        $fp = fopen(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'fputcsv.csv', 'w');
        $this->assertEquals(strlen('""' . PHP_EOL), Util::fputcsv($fp, $record));
        $this->assertFileExists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'fputcsv.csv');
        $contents = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'fputcsv.csv');
        $this->assertEquals('""' . PHP_EOL, $contents);
    }

    public function test_fputcsv_NULL１レコード書き込み()
    {
        $record = [NULL];
        $fp = fopen(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'fputcsv.csv', 'w');
        $this->assertEquals(strlen('NULL' . PHP_EOL), Util::fputcsv($fp, $record, TRUE));
        $this->assertFileExists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'fputcsv.csv');
        $contents = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'fputcsv.csv');
        $this->assertEquals('NULL' . PHP_EOL, $contents);
    }

    public function test_fputcsv_UTF8からSJISに変換１レコード書き込み()
    {
        $record = ['あ', 'い', 'う', 'え', 'お'];
        $fp = fopen(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'fputcsv.csv', 'w');
        $this->assertEquals(strlen(mb_convert_encoding('"あ","い","う","え","お"' . PHP_EOL, Util::SJIS, Util::UTF8)),
            Util::fputcsv($fp, $record, FALSE, PHP_EOL, Util::SJIS, Util::UTF8));
        $this->assertFileExists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'fputcsv.csv');
        $contents = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files'
            . DIRECTORY_SEPARATOR . 'fputcsv.csv');
        $this->assertEquals(
            mb_convert_encoding('"あ","い","う","え","お"' . PHP_EOL, Util::SJIS, Util::UTF8), $contents);
    }

    public function test_println_ASCII文字列()
    {
        $this->expectOutputString('test' . PHP_EOL);
        Util::println('test');
    }

    public function test_println_改行コード付きASCII文字列()
    {
        $this->expectOutputString('test' . PHP_EOL);
        Util::println('test' . PHP_EOL);
    }

    public function test_println_日本語文字列()
    {
        if (strpos(PHP_OS, 'WIN') === 0 && strpos(exec('chcp'), '932') !== FALSE)
        {
            $this->expectOutputString(mb_convert_encoding('テスト', Util::SJIS, Util::UTF8) . PHP_EOL);
        }
        else
        {
            $this->expectOutputString('テスト' . PHP_EOL);
        }
        Util::println('テスト');
    }

    public function test_println_改行コード付き日本語文字列()
    {
        if (strpos(PHP_OS, 'WIN') === 0 && strpos(exec('chcp'), '932') !== FALSE)
        {
            $this->expectOutputString(mb_convert_encoding('テスト', Util::SJIS, Util::UTF8) . PHP_EOL);
        }
        else
        {
            $this->expectOutputString('テスト' . PHP_EOL);
        }
        Util::println('テスト' . PHP_EOL);
    }

    public function test_safely_eval_問題無いコード()
    {
        $this->assertEquals('lave', Util::safely_eval('return strrev("eval");'));
    }

    public function test_safely_eval_構文エラーなコード()
    {
        $this->assertFalse(Util::safely_eval('return strrev(eval);'));
    }

    public function test_safely_eval_ホワイトリストに無いトークンを使用したコード()
    {
        $this->assertFalse(Util::safely_eval('return range(1, 10);'));
    }

    public function test_safely_eval_ホワイトリストに追加したトークンを使用したコード()
    {
        $this->assertCount(10, Util::safely_eval('return range(1, 10);', TRUE, [T_STRING => ['range']]));
    }

    public function test_safely_eval_ブラックリストに追加したトークンを使用したコード()
    {
        $this->assertFalse(Util::safely_eval('exit();', FALSE, [], [T_EXIT => ['exit']]));
    }

    public function test_safely_eval_必須リストに追加したトークンを使用していないコード()
    {
        $this->assertFalse(Util::safely_eval('return strrev("eval");', TRUE, [], [], [T_COMMENT => []]));
    }
    public function test_safely_eval_必須リストに追加したトークンを使用したコード()
    {
        $this->assertEquals('lave', 
            Util::safely_eval('/* comment */ return strrev("eval");', TRUE, [], [], [T_COMMENT => []]));
    }

    protected function tearDown()
    {
        $bak_files = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . '*.bak';
        foreach (glob($bak_files) as $bak_file)
        {
            $orig_file = substr($bak_file, 0, -4);
            if (is_file($orig_file)) unlink($orig_file);
            rename($bak_file, $orig_file);
        }
        $sql_files = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . '*.sql';
        foreach (glob($sql_files) as $sql_file)
        {
            if (is_file($sql_file)) unlink($sql_file);
        }
        $fputcsv_file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'fputcsv.csv';
        if (is_file($fputcsv_file)) unlink($fputcsv_file);
    }

    private $kana1010 = [
        ['id', 'kana', 'num', 'empty'],
        ['1', 'ふ', '39', 'NULL'],
        ['2', 'り', '29', 'NULL'],
        ['3', 'ふ', '87', 'NULL'],
        ['4', 'む', '78', 'NULL'],
        ['5', 'こ', '45', 'NULL'],
        ['6', 'る', '5', 'NULL'],
        ['7', 'さ', '2', 'NULL'],
        ['8', 'ろ', '72', 'NULL'],
        ['9', 'の', '13', 'NULL'],
        ['10', 'の', '100', 'NULL'],
        ['11', 'て', '67', 'NULL'],
        ['12', 'え', '37', 'NULL'],
        ['13', 'に', '44', 'NULL'],
        ['14', 'つ', '90', 'NULL'],
        ['15', 'た', '87', 'NULL'],
        ['16', 'る', '48', 'NULL'],
        ['17', 'の', '94', 'NULL'],
        ['18', 'き', '44', 'NULL'],
        ['19', 'う', '41', 'NULL'],
        ['20', 'わ', '6', 'NULL'],
        ['21', 'に', '4', 'NULL'],
        ['22', 'け', '75', 'NULL'],
        ['23', 'ほ', '60', 'NULL'],
        ['24', 'ひ', '77', 'NULL'],
        ['25', 'け', '56', 'NULL'],
        ['26', 'し', '7', 'NULL'],
        ['27', 'か', '91', 'NULL'],
        ['28', 'み', '5', 'NULL'],
        ['29', 'に', '74', 'NULL'],
        ['30', 'へ', '41', 'NULL'],
        ['31', 'つ', '36', 'NULL'],
        ['32', 'し', '48', 'NULL'],
        ['33', 'す', '26', 'NULL'],
        ['34', 'と', '59', 'NULL'],
        ['35', 'あ', '43', 'NULL'],
        ['36', 'り', '22', 'NULL'],
        ['37', 'の', '95', 'NULL'],
        ['38', 'ね', '50', 'NULL'],
        ['39', 'い', '91', 'NULL'],
        ['40', 'せ', '22', 'NULL'],
        ['41', 'し', '16', 'NULL'],
        ['42', 'か', '31', 'NULL'],
        ['43', 'ふ', '98', 'NULL'],
        ['44', 'ゆ', '34', 'NULL'],
        ['45', 'さ', '89', 'NULL'],
        ['46', 'ゆ', '58', 'NULL'],
        ['47', 'な', '44', 'NULL'],
        ['48', 'け', '86', 'NULL'],
        ['49', 'ん', '9', 'NULL'],
        ['50', 'れ', '96', 'NULL'],
        ['51', 'わ', '32', 'NULL'],
        ['52', 'や', '84', 'NULL'],
        ['53', 'ひ', '68', 'NULL'],
        ['54', 'け', '87', 'NULL'],
        ['55', 'せ', '92', 'NULL'],
        ['56', 'ら', '6', 'NULL'],
        ['57', 'ら', '100', 'NULL'],
        ['58', 'を', '46', 'NULL'],
        ['59', 'そ', '47', 'NULL'],
        ['60', 'さ', '86', 'NULL'],
        ['61', 'す', '14', 'NULL'],
        ['62', 'を', '36', 'NULL'],
        ['63', 'り', '20', 'NULL'],
        ['64', 'の', '56', 'NULL'],
        ['65', 'し', '94', 'NULL'],
        ['66', 'よ', '29', 'NULL'],
        ['67', 'れ', '29', 'NULL'],
        ['68', 'ん', '22', 'NULL'],
        ['69', 'ろ', '38', 'NULL'],
        ['70', 'う', '66', 'NULL'],
        ['71', 'さ', '65', 'NULL'],
        ['72', 'か', '17', 'NULL'],
        ['73', 'ろ', '33', 'NULL'],
        ['74', 'う', '73', 'NULL'],
        ['75', 'ま', '28', 'NULL'],
        ['76', 'て', '73', 'NULL'],
        ['77', 'ち', '32', 'NULL'],
        ['78', 'と', '13', 'NULL'],
        ['79', 'ま', '14', 'NULL'],
        ['80', 'ま', '54', 'NULL'],
        ['81', 'か', '73', 'NULL'],
        ['82', 'へ', '69', 'NULL'],
        ['83', 'ち', '75', 'NULL'],
        ['84', 'す', '100', 'NULL'],
        ['85', 'こ', '22', 'NULL'],
        ['86', 'と', '63', 'NULL'],
        ['87', 'す', '16', 'NULL'],
        ['88', 'さ', '84', 'NULL'],
        ['89', 'よ', '61', 'NULL'],
        ['90', 'な', '14', 'NULL'],
        ['91', 'わ', '16', 'NULL'],
        ['92', 'き', '89', 'NULL'],
        ['93', 'に', '34', 'NULL'],
        ['94', 'ん', '50', 'NULL'],
        ['95', 'て', '59', 'NULL'],
        ['96', 'ま', '98', 'NULL'],
        ['97', 'き', '63', 'NULL'],
        ['98', 'た', '25', 'NULL'],
        ['99', 'め', '64', 'NULL'],
        ['100', 'そ', '100', 'NULL'],
        ['101', 'に', '24', 'NULL'],
        ['102', 'ふ', '82', 'NULL'],
        ['103', 'も', '19', 'NULL'],
        ['104', 'な', '74', 'NULL'],
        ['105', 'お', '85', 'NULL'],
        ['106', 'ゆ', '56', 'NULL'],
        ['107', 'て', '86', 'NULL'],
        ['108', 'け', '26', 'NULL'],
        ['109', 'な', '51', 'NULL'],
        ['110', 'ゆ', '20', 'NULL'],
        ['111', 'さ', '7', 'NULL'],
        ['112', 'へ', '50', 'NULL'],
        ['113', 'は', '15', 'NULL'],
        ['114', 'ゆ', '32', 'NULL'],
        ['115', 'え', '42', 'NULL'],
        ['116', 'に', '91', 'NULL'],
        ['117', 'ゆ', '81', 'NULL'],
        ['118', 'す', '85', 'NULL'],
        ['119', 'や', '29', 'NULL'],
        ['120', 'へ', '28', 'NULL'],
        ['121', 'り', '81', 'NULL'],
        ['122', 'ち', '84', 'NULL'],
        ['123', 'ん', '21', 'NULL'],
        ['124', 'た', '66', 'NULL'],
        ['125', 'ん', '49', 'NULL'],
        ['126', 'め', '99', 'NULL'],
        ['127', 'る', '56', 'NULL'],
        ['128', 'れ', '50', 'NULL'],
        ['129', 'ね', '21', 'NULL'],
        ['130', 'め', '89', 'NULL'],
        ['131', 'む', '15', 'NULL'],
        ['132', 'も', '55', 'NULL'],
        ['133', 'え', '57', 'NULL'],
        ['134', 'は', '63', 'NULL'],
        ['135', 'ふ', '19', 'NULL'],
        ['136', 'ゆ', '30', 'NULL'],
        ['137', 'か', '17', 'NULL'],
        ['138', 'し', '79', 'NULL'],
        ['139', 'え', '55', 'NULL'],
        ['140', 'り', '79', 'NULL'],
        ['141', 'き', '30', 'NULL'],
        ['142', 'わ', '100', 'NULL'],
        ['143', 'く', '98', 'NULL'],
        ['144', 'け', '38', 'NULL'],
        ['145', 'け', '9', 'NULL'],
        ['146', 'い', '65', 'NULL'],
        ['147', 'ゆ', '15', 'NULL'],
        ['148', 'え', '29', 'NULL'],
        ['149', 'し', '33', 'NULL'],
        ['150', 'う', '9', 'NULL'],
        ['151', 'め', '67', 'NULL'],
        ['152', 'た', '98', 'NULL'],
        ['153', 'と', '18', 'NULL'],
        ['154', 'は', '99', 'NULL'],
        ['155', 'む', '91', 'NULL'],
        ['156', 'さ', '34', 'NULL'],
        ['157', 'ひ', '15', 'NULL'],
        ['158', 'か', '28', 'NULL'],
        ['159', 'ら', '5', 'NULL'],
        ['160', 'ち', '53', 'NULL'],
        ['161', 'け', '66', 'NULL'],
        ['162', 'ひ', '33', 'NULL'],
        ['163', 'て', '85', 'NULL'],
        ['164', 'ほ', '21', 'NULL'],
        ['165', 'め', '7', 'NULL'],
        ['166', 'や', '74', 'NULL'],
        ['167', 'め', '62', 'NULL'],
        ['168', 'み', '86', 'NULL'],
        ['169', 'に', '77', 'NULL'],
        ['170', 'う', '69', 'NULL'],
        ['171', 'つ', '19', 'NULL'],
        ['172', 'ら', '29', 'NULL'],
        ['173', 'ち', '91', 'NULL'],
        ['174', 'よ', '8', 'NULL'],
        ['175', 'め', '24', 'NULL'],
        ['176', 'け', '65', 'NULL'],
        ['177', 'ら', '67', 'NULL'],
        ['178', 'き', '95', 'NULL'],
        ['179', 'く', '57', 'NULL'],
        ['180', 'ろ', '9', 'NULL'],
        ['181', 'ゆ', '42', 'NULL'],
        ['182', 'こ', '81', 'NULL'],
        ['183', 'る', '24', 'NULL'],
        ['184', 'あ', '41', 'NULL'],
        ['185', 'し', '72', 'NULL'],
        ['186', 'る', '33', 'NULL'],
        ['187', 'む', '15', 'NULL'],
        ['188', 'さ', '63', 'NULL'],
        ['189', 'れ', '78', 'NULL'],
        ['190', 'ふ', '91', 'NULL'],
        ['191', 'ね', '91', 'NULL'],
        ['192', 'え', '40', 'NULL'],
        ['193', 'し', '88', 'NULL'],
        ['194', 'に', '57', 'NULL'],
        ['195', 'と', '48', 'NULL'],
        ['196', 'ふ', '2', 'NULL'],
        ['197', 'た', '85', 'NULL'],
        ['198', 'と', '12', 'NULL'],
        ['199', 'の', '78', 'NULL'],
        ['200', 'て', '82', 'NULL'],
        ['201', 'ん', '71', 'NULL'],
        ['202', 'ぬ', '32', 'NULL'],
        ['203', 'へ', '14', 'NULL'],
        ['204', 'り', '33', 'NULL'],
        ['205', 'ふ', '70', 'NULL'],
        ['206', 'て', '62', 'NULL'],
        ['207', 'ふ', '37', 'NULL'],
        ['208', 'き', '80', 'NULL'],
        ['209', 'み', '14', 'NULL'],
        ['210', 'よ', '26', 'NULL'],
        ['211', 'つ', '18', 'NULL'],
        ['212', 'と', '49', 'NULL'],
        ['213', 'し', '74', 'NULL'],
        ['214', 'な', '68', 'NULL'],
        ['215', 'あ', '20', 'NULL'],
        ['216', 'わ', '82', 'NULL'],
        ['217', 'つ', '10', 'NULL'],
        ['218', 'け', '12', 'NULL'],
        ['219', 'ん', '85', 'NULL'],
        ['220', 'ろ', '5', 'NULL'],
        ['221', 'み', '30', 'NULL'],
        ['222', 'さ', '40', 'NULL'],
        ['223', 'の', '78', 'NULL'],
        ['224', 'へ', '40', 'NULL'],
        ['225', 'よ', '88', 'NULL'],
        ['226', 'ほ', '36', 'NULL'],
        ['227', 'つ', '39', 'NULL'],
        ['228', 'し', '94', 'NULL'],
        ['229', 'の', '97', 'NULL'],
        ['230', 'う', '81', 'NULL'],
        ['231', 'た', '97', 'NULL'],
        ['232', 'お', '4', 'NULL'],
        ['233', 'こ', '53', 'NULL'],
        ['234', 'か', '98', 'NULL'],
        ['235', 'む', '70', 'NULL'],
        ['236', 'ね', '8', 'NULL'],
        ['237', 'た', '56', 'NULL'],
        ['238', 'か', '28', 'NULL'],
        ['239', 'も', '74', 'NULL'],
        ['240', 'す', '52', 'NULL'],
        ['241', 'を', '79', 'NULL'],
        ['242', 'か', '89', 'NULL'],
        ['243', 'あ', '33', 'NULL'],
        ['244', 'せ', '3', 'NULL'],
        ['245', 'き', '18', 'NULL'],
        ['246', 'に', '100', 'NULL'],
        ['247', 'へ', '91', 'NULL'],
        ['248', 'む', '33', 'NULL'],
        ['249', 'ま', '43', 'NULL'],
        ['250', 'は', '9', 'NULL'],
        ['251', 'は', '16', 'NULL'],
        ['252', 'ら', '60', 'NULL'],
        ['253', 'め', '44', 'NULL'],
        ['254', 'た', '55', 'NULL'],
        ['255', 'も', '69', 'NULL'],
        ['256', 'ほ', '61', 'NULL'],
        ['257', 'ひ', '10', 'NULL'],
        ['258', 'ふ', '78', 'NULL'],
        ['259', 'る', '39', 'NULL'],
        ['260', 'き', '77', 'NULL'],
        ['261', 'こ', '78', 'NULL'],
        ['262', 'い', '62', 'NULL'],
        ['263', 'み', '92', 'NULL'],
        ['264', 'み', '35', 'NULL'],
        ['265', 'け', '25', 'NULL'],
        ['266', 'ゆ', '71', 'NULL'],
        ['267', 'ひ', '68', 'NULL'],
        ['268', 'へ', '39', 'NULL'],
        ['269', 'ち', '81', 'NULL'],
        ['270', 'へ', '41', 'NULL'],
        ['271', 'は', '94', 'NULL'],
        ['272', 'な', '39', 'NULL'],
        ['273', 'つ', '68', 'NULL'],
        ['274', 'く', '100', 'NULL'],
        ['275', 'ほ', '19', 'NULL'],
        ['276', 'れ', '44', 'NULL'],
        ['277', 'の', '11', 'NULL'],
        ['278', 'ふ', '31', 'NULL'],
        ['279', 'む', '54', 'NULL'],
        ['280', 'か', '90', 'NULL'],
        ['281', 'よ', '55', 'NULL'],
        ['282', 'ん', '49', 'NULL'],
        ['283', 'せ', '81', 'NULL'],
        ['284', 'こ', '15', 'NULL'],
        ['285', 'よ', '63', 'NULL'],
        ['286', 'わ', '49', 'NULL'],
        ['287', 'ん', '5', 'NULL'],
        ['288', 'は', '47', 'NULL'],
        ['289', 'れ', '29', 'NULL'],
        ['290', 'ふ', '65', 'NULL'],
        ['291', 'け', '24', 'NULL'],
        ['292', 'あ', '6', 'NULL'],
        ['293', 'え', '36', 'NULL'],
        ['294', 'た', '13', 'NULL'],
        ['295', 'り', '33', 'NULL'],
        ['296', 'き', '33', 'NULL'],
        ['297', 'な', '16', 'NULL'],
        ['298', 'ま', '33', 'NULL'],
        ['299', 'お', '21', 'NULL'],
        ['300', 'ね', '22', 'NULL'],
        ['301', 'と', '30', 'NULL'],
        ['302', 'ら', '75', 'NULL'],
        ['303', 'お', '35', 'NULL'],
        ['304', 'ふ', '11', 'NULL'],
        ['305', 'ら', '62', 'NULL'],
        ['306', 'ね', '1', 'NULL'],
        ['307', 'は', '46', 'NULL'],
        ['308', 'も', '83', 'NULL'],
        ['309', 'ん', '61', 'NULL'],
        ['310', 'ゆ', '30', 'NULL'],
        ['311', 'ち', '29', 'NULL'],
        ['312', 'ち', '31', 'NULL'],
        ['313', 'か', '71', 'NULL'],
        ['314', 'か', '94', 'NULL'],
        ['315', 'と', '38', 'NULL'],
        ['316', 'え', '16', 'NULL'],
        ['317', 'ゆ', '2', 'NULL'],
        ['318', 'い', '2', 'NULL'],
        ['319', 'さ', '79', 'NULL'],
        ['320', 'ち', '32', 'NULL'],
        ['321', 'あ', '43', 'NULL'],
        ['322', 'ら', '39', 'NULL'],
        ['323', 'う', '68', 'NULL'],
        ['324', 'を', '37', 'NULL'],
        ['325', 'あ', '89', 'NULL'],
        ['326', 'せ', '68', 'NULL'],
        ['327', 'え', '23', 'NULL'],
        ['328', 'に', '51', 'NULL'],
        ['329', 'ち', '92', 'NULL'],
        ['330', 'あ', '69', 'NULL'],
        ['331', 'ね', '45', 'NULL'],
        ['332', 'ふ', '66', 'NULL'],
        ['333', 'せ', '89', 'NULL'],
        ['334', 'ひ', '42', 'NULL'],
        ['335', 'ち', '50', 'NULL'],
        ['336', 'も', '4', 'NULL'],
        ['337', 'を', '54', 'NULL'],
        ['338', 'し', '33', 'NULL'],
        ['339', 'れ', '73', 'NULL'],
        ['340', 'す', '51', 'NULL'],
        ['341', 'く', '68', 'NULL'],
        ['342', 'み', '75', 'NULL'],
        ['343', 'わ', '60', 'NULL'],
        ['344', 'す', '86', 'NULL'],
        ['345', 'る', '13', 'NULL'],
        ['346', 'も', '70', 'NULL'],
        ['347', 'か', '52', 'NULL'],
        ['348', 'む', '31', 'NULL'],
        ['349', 'り', '49', 'NULL'],
        ['350', 'よ', '31', 'NULL'],
        ['351', 'を', '46', 'NULL'],
        ['352', 'は', '72', 'NULL'],
        ['353', 'あ', '27', 'NULL'],
        ['354', 'れ', '20', 'NULL'],
        ['355', 'む', '58', 'NULL'],
        ['356', 'む', '73', 'NULL'],
        ['357', 'ん', '1', 'NULL'],
        ['358', 'け', '70', 'NULL'],
        ['359', 'ひ', '19', 'NULL'],
        ['360', 'お', '97', 'NULL'],
        ['361', 'を', '55', 'NULL'],
        ['362', 'か', '94', 'NULL'],
        ['363', 'ひ', '100', 'NULL'],
        ['364', 'た', '10', 'NULL'],
        ['365', 'ら', '93', 'NULL'],
        ['366', 'せ', '96', 'NULL'],
        ['367', 'ふ', '92', 'NULL'],
        ['368', 'や', '95', 'NULL'],
        ['369', 'に', '51', 'NULL'],
        ['370', 'け', '72', 'NULL'],
        ['371', 'わ', '7', 'NULL'],
        ['372', 'り', '44', 'NULL'],
        ['373', 'て', '51', 'NULL'],
        ['374', 'る', '44', 'NULL'],
        ['375', 'て', '77', 'NULL'],
        ['376', 'み', '94', 'NULL'],
        ['377', 'お', '90', 'NULL'],
        ['378', 'の', '62', 'NULL'],
        ['379', 'わ', '32', 'NULL'],
        ['380', 'な', '38', 'NULL'],
        ['381', 'を', '56', 'NULL'],
        ['382', 'す', '22', 'NULL'],
        ['383', 'た', '26', 'NULL'],
        ['384', 'く', '24', 'NULL'],
        ['385', 'い', '47', 'NULL'],
        ['386', 'に', '70', 'NULL'],
        ['387', 'よ', '43', 'NULL'],
        ['388', 'ふ', '51', 'NULL'],
        ['389', 'こ', '96', 'NULL'],
        ['390', 'る', '29', 'NULL'],
        ['391', 'も', '97', 'NULL'],
        ['392', 'ひ', '10', 'NULL'],
        ['393', 'は', '59', 'NULL'],
        ['394', 'ろ', '12', 'NULL'],
        ['395', 'あ', '47', 'NULL'],
        ['396', 'け', '1', 'NULL'],
        ['397', 'き', '53', 'NULL'],
        ['398', 'は', '95', 'NULL'],
        ['399', 'む', '66', 'NULL'],
        ['400', 'は', '78', 'NULL'],
        ['401', 'ゆ', '18', 'NULL'],
        ['402', 'よ', '47', 'NULL'],
        ['403', 'し', '23', 'NULL'],
        ['404', 'て', '58', 'NULL'],
        ['405', 'も', '19', 'NULL'],
        ['406', 'こ', '27', 'NULL'],
        ['407', 'わ', '6', 'NULL'],
        ['408', 'こ', '13', 'NULL'],
        ['409', 'お', '36', 'NULL'],
        ['410', 'え', '67', 'NULL'],
        ['411', 'る', '41', 'NULL'],
        ['412', 'い', '98', 'NULL'],
        ['413', 'た', '79', 'NULL'],
        ['414', 'そ', '25', 'NULL'],
        ['415', 'ゆ', '63', 'NULL'],
        ['416', 'へ', '38', 'NULL'],
        ['417', 'か', '56', 'NULL'],
        ['418', 'き', '29', 'NULL'],
        ['419', 'な', '79', 'NULL'],
        ['420', 'を', '52', 'NULL'],
        ['421', 'ぬ', '55', 'NULL'],
        ['422', 'き', '65', 'NULL'],
        ['423', 'に', '75', 'NULL'],
        ['424', 'ま', '36', 'NULL'],
        ['425', 'な', '89', 'NULL'],
        ['426', 'う', '96', 'NULL'],
        ['427', 'す', '94', 'NULL'],
        ['428', 'め', '15', 'NULL'],
        ['429', 'あ', '57', 'NULL'],
        ['430', 'れ', '33', 'NULL'],
        ['431', 'ぬ', '36', 'NULL'],
        ['432', 'え', '4', 'NULL'],
        ['433', 'ひ', '46', 'NULL'],
        ['434', 'け', '69', 'NULL'],
        ['435', 'た', '31', 'NULL'],
        ['436', 'き', '65', 'NULL'],
        ['437', 'れ', '51', 'NULL'],
        ['438', 'わ', '100', 'NULL'],
        ['439', 'い', '17', 'NULL'],
        ['440', 'つ', '3', 'NULL'],
        ['441', 'ひ', '26', 'NULL'],
        ['442', 'ひ', '15', 'NULL'],
        ['443', 'お', '76', 'NULL'],
        ['444', 'ま', '56', 'NULL'],
        ['445', 'ち', '23', 'NULL'],
        ['446', 'せ', '89', 'NULL'],
        ['447', 'も', '59', 'NULL'],
        ['448', 'か', '20', 'NULL'],
        ['449', 'は', '21', 'NULL'],
        ['450', 'え', '44', 'NULL'],
        ['451', 'う', '100', 'NULL'],
        ['452', 'と', '31', 'NULL'],
        ['453', 'ね', '91', 'NULL'],
        ['454', 'ふ', '17', 'NULL'],
        ['455', 'せ', '83', 'NULL'],
        ['456', 'こ', '29', 'NULL'],
        ['457', 'わ', '35', 'NULL'],
        ['458', 'の', '42', 'NULL'],
        ['459', 'ひ', '31', 'NULL'],
        ['460', 'や', '41', 'NULL'],
        ['461', 'は', '72', 'NULL'],
        ['462', 'も', '87', 'NULL'],
        ['463', 'こ', '94', 'NULL'],
        ['464', 'つ', '28', 'NULL'],
        ['465', 'み', '45', 'NULL'],
        ['466', 'ひ', '19', 'NULL'],
        ['467', 'ち', '43', 'NULL'],
        ['468', 'け', '8', 'NULL'],
        ['469', 'は', '42', 'NULL'],
        ['470', 'を', '98', 'NULL'],
        ['471', 'つ', '66', 'NULL'],
        ['472', 'に', '22', 'NULL'],
        ['473', 'ほ', '38', 'NULL'],
        ['474', 'の', '50', 'NULL'],
        ['475', 'り', '43', 'NULL'],
        ['476', 'せ', '99', 'NULL'],
        ['477', 'よ', '4', 'NULL'],
        ['478', 'や', '22', 'NULL'],
        ['479', 'る', '72', 'NULL'],
        ['480', 'れ', '33', 'NULL'],
        ['481', 'う', '33', 'NULL'],
        ['482', 'り', '38', 'NULL'],
        ['483', 'な', '71', 'NULL'],
        ['484', 'そ', '100', 'NULL'],
        ['485', 'さ', '19', 'NULL'],
        ['486', 'お', '79', 'NULL'],
        ['487', 'は', '58', 'NULL'],
        ['488', 'ね', '48', 'NULL'],
        ['489', 'ぬ', '16', 'NULL'],
        ['490', 'る', '7', 'NULL'],
        ['491', 'も', '25', 'NULL'],
        ['492', 'め', '49', 'NULL'],
        ['493', 'う', '69', 'NULL'],
        ['494', 'か', '53', 'NULL'],
        ['495', 'お', '2', 'NULL'],
        ['496', 'ひ', '59', 'NULL'],
        ['497', 'い', '68', 'NULL'],
        ['498', 'む', '42', 'NULL'],
        ['499', 'ほ', '7', 'NULL'],
        ['500', 'に', '12', 'NULL'],
        ['501', 'か', '11', 'NULL'],
        ['502', 'ろ', '28', 'NULL'],
        ['503', 'わ', '77', 'NULL'],
        ['504', 'ら', '62', 'NULL'],
        ['505', 'ゆ', '58', 'NULL'],
        ['506', 'ゆ', '72', 'NULL'],
        ['507', 'お', '54', 'NULL'],
        ['508', 'そ', '13', 'NULL'],
        ['509', 'み', '71', 'NULL'],
        ['510', 'て', '71', 'NULL'],
        ['511', 'か', '36', 'NULL'],
        ['512', 'あ', '74', 'NULL'],
        ['513', 'す', '1', 'NULL'],
        ['514', 'す', '11', 'NULL'],
        ['515', 'も', '46', 'NULL'],
        ['516', 'い', '66', 'NULL'],
        ['517', 'て', '19', 'NULL'],
        ['518', 'み', '26', 'NULL'],
        ['519', 'る', '94', 'NULL'],
        ['520', 'こ', '29', 'NULL'],
        ['521', 'た', '89', 'NULL'],
        ['522', 'そ', '3', 'NULL'],
        ['523', 'ゆ', '67', 'NULL'],
        ['524', 'こ', '93', 'NULL'],
        ['525', 'ら', '55', 'NULL'],
        ['526', 'ひ', '68', 'NULL'],
        ['527', 'み', '50', 'NULL'],
        ['528', 'け', '33', 'NULL'],
        ['529', 'ひ', '46', 'NULL'],
        ['530', 'や', '79', 'NULL'],
        ['531', 'き', '35', 'NULL'],
        ['532', 'ね', '72', 'NULL'],
        ['533', 'を', '21', 'NULL'],
        ['534', 'を', '1', 'NULL'],
        ['535', 'も', '15', 'NULL'],
        ['536', 'は', '29', 'NULL'],
        ['537', 'す', '40', 'NULL'],
        ['538', 'け', '91', 'NULL'],
        ['539', 'せ', '55', 'NULL'],
        ['540', 'に', '21', 'NULL'],
        ['541', 'へ', '4', 'NULL'],
        ['542', 'ゆ', '52', 'NULL'],
        ['543', 'の', '52', 'NULL'],
        ['544', 'あ', '69', 'NULL'],
        ['545', 'ひ', '38', 'NULL'],
        ['546', 'も', '36', 'NULL'],
        ['547', 'は', '69', 'NULL'],
        ['548', 'は', '70', 'NULL'],
        ['549', 'も', '74', 'NULL'],
        ['550', 'む', '47', 'NULL'],
        ['551', 'れ', '5', 'NULL'],
        ['552', 'ね', '6', 'NULL'],
        ['553', 'に', '17', 'NULL'],
        ['554', 'せ', '97', 'NULL'],
        ['555', 'わ', '49', 'NULL'],
        ['556', 'く', '5', 'NULL'],
        ['557', 'る', '18', 'NULL'],
        ['558', 'と', '47', 'NULL'],
        ['559', 'え', '60', 'NULL'],
        ['560', 'め', '74', 'NULL'],
        ['561', 'と', '97', 'NULL'],
        ['562', 'た', '69', 'NULL'],
        ['563', 'に', '81', 'NULL'],
        ['564', 'さ', '30', 'NULL'],
        ['565', 'わ', '39', 'NULL'],
        ['566', 'う', '68', 'NULL'],
        ['567', 'め', '49', 'NULL'],
        ['568', 'る', '19', 'NULL'],
        ['569', 'こ', '50', 'NULL'],
        ['570', 'か', '68', 'NULL'],
        ['571', 'お', '41', 'NULL'],
        ['572', 'を', '32', 'NULL'],
        ['573', 'に', '36', 'NULL'],
        ['574', 'に', '35', 'NULL'],
        ['575', 'め', '2', 'NULL'],
        ['576', 'い', '43', 'NULL'],
        ['577', 'さ', '10', 'NULL'],
        ['578', 'し', '5', 'NULL'],
        ['579', 'と', '84', 'NULL'],
        ['580', 'そ', '31', 'NULL'],
        ['581', 'き', '83', 'NULL'],
        ['582', 'そ', '83', 'NULL'],
        ['583', 'ぬ', '31', 'NULL'],
        ['584', 'わ', '54', 'NULL'],
        ['585', 'ち', '61', 'NULL'],
        ['586', 'み', '14', 'NULL'],
        ['587', 'れ', '55', 'NULL'],
        ['588', 'ほ', '85', 'NULL'],
        ['589', 'わ', '8', 'NULL'],
        ['590', 'ひ', '18', 'NULL'],
        ['591', 'も', '62', 'NULL'],
        ['592', 'く', '48', 'NULL'],
        ['593', 'け', '57', 'NULL'],
        ['594', 'こ', '32', 'NULL'],
        ['595', 'ね', '56', 'NULL'],
        ['596', 'か', '25', 'NULL'],
        ['597', 'の', '4', 'NULL'],
        ['598', 'も', '34', 'NULL'],
        ['599', 'て', '24', 'NULL'],
        ['600', 'す', '11', 'NULL'],
        ['601', 'と', '70', 'NULL'],
        ['602', 'こ', '9', 'NULL'],
        ['603', 'せ', '53', 'NULL'],
        ['604', 'わ', '4', 'NULL'],
        ['605', 'る', '12', 'NULL'],
        ['606', 'り', '53', 'NULL'],
        ['607', 'に', '88', 'NULL'],
        ['608', 'こ', '63', 'NULL'],
        ['609', 'も', '66', 'NULL'],
        ['610', 'れ', '25', 'NULL'],
        ['611', 'ぬ', '88', 'NULL'],
        ['612', 'そ', '2', 'NULL'],
        ['613', 'ぬ', '99', 'NULL'],
        ['614', 'る', '61', 'NULL'],
        ['615', 'ゆ', '63', 'NULL'],
        ['616', 'た', '92', 'NULL'],
        ['617', 'は', '37', 'NULL'],
        ['618', 'ち', '73', 'NULL'],
        ['619', 'か', '19', 'NULL'],
        ['620', 'れ', '23', 'NULL'],
        ['621', 'る', '97', 'NULL'],
        ['622', 'に', '53', 'NULL'],
        ['623', 'う', '49', 'NULL'],
        ['624', 'な', '39', 'NULL'],
        ['625', 'ぬ', '10', 'NULL'],
        ['626', 'う', '68', 'NULL'],
        ['627', 'す', '87', 'NULL'],
        ['628', 'る', '12', 'NULL'],
        ['629', 'た', '4', 'NULL'],
        ['630', 'ふ', '14', 'NULL'],
        ['631', 'へ', '1', 'NULL'],
        ['632', 'あ', '54', 'NULL'],
        ['633', 'み', '9', 'NULL'],
        ['634', 'い', '67', 'NULL'],
        ['635', 'そ', '78', 'NULL'],
        ['636', 'そ', '24', 'NULL'],
        ['637', 'ろ', '61', 'NULL'],
        ['638', 'ら', '99', 'NULL'],
        ['639', 'る', '59', 'NULL'],
        ['640', 'つ', '33', 'NULL'],
        ['641', 'く', '58', 'NULL'],
        ['642', 'な', '39', 'NULL'],
        ['643', 'ま', '90', 'NULL'],
        ['644', 'し', '66', 'NULL'],
        ['645', 'す', '94', 'NULL'],
        ['646', 'き', '46', 'NULL'],
        ['647', 'め', '100', 'NULL'],
        ['648', 'よ', '23', 'NULL'],
        ['649', 'そ', '96', 'NULL'],
        ['650', 'け', '55', 'NULL'],
        ['651', 'め', '22', 'NULL'],
        ['652', 'お', '16', 'NULL'],
        ['653', 'く', '88', 'NULL'],
        ['654', 'ろ', '93', 'NULL'],
        ['655', 'ほ', '60', 'NULL'],
        ['656', 'た', '42', 'NULL'],
        ['657', 'け', '14', 'NULL'],
        ['658', 'た', '46', 'NULL'],
        ['659', 'う', '84', 'NULL'],
        ['660', 'え', '59', 'NULL'],
        ['661', 'き', '96', 'NULL'],
        ['662', 'の', '60', 'NULL'],
        ['663', 'り', '40', 'NULL'],
        ['664', 'に', '69', 'NULL'],
        ['665', 'に', '15', 'NULL'],
        ['666', 'め', '30', 'NULL'],
        ['667', 'わ', '93', 'NULL'],
        ['668', 'れ', '86', 'NULL'],
        ['669', 'た', '65', 'NULL'],
        ['670', 'よ', '52', 'NULL'],
        ['671', 'を', '8', 'NULL'],
        ['672', 'ら', '39', 'NULL'],
        ['673', 'な', '77', 'NULL'],
        ['674', 'わ', '7', 'NULL'],
        ['675', 'し', '51', 'NULL'],
        ['676', 'ん', '83', 'NULL'],
        ['677', 'か', '57', 'NULL'],
        ['678', 'ゆ', '49', 'NULL'],
        ['679', 'し', '13', 'NULL'],
        ['680', 'ろ', '50', 'NULL'],
        ['681', 'ひ', '40', 'NULL'],
        ['682', 'を', '40', 'NULL'],
        ['683', 'へ', '11', 'NULL'],
        ['684', 'く', '72', 'NULL'],
        ['685', 'ろ', '19', 'NULL'],
        ['686', 'え', '20', 'NULL'],
        ['687', 'え', '68', 'NULL'],
        ['688', 'と', '70', 'NULL'],
        ['689', 'う', '79', 'NULL'],
        ['690', 'ひ', '80', 'NULL'],
        ['691', 'す', '61', 'NULL'],
        ['692', 'わ', '27', 'NULL'],
        ['693', 'ち', '93', 'NULL'],
        ['694', 'る', '88', 'NULL'],
        ['695', 'ま', '73', 'NULL'],
        ['696', 'も', '88', 'NULL'],
        ['697', 'ふ', '97', 'NULL'],
        ['698', 'み', '23', 'NULL'],
        ['699', 'ね', '28', 'NULL'],
        ['700', 'て', '26', 'NULL'],
        ['701', 'と', '61', 'NULL'],
        ['702', 'こ', '56', 'NULL'],
        ['703', 'め', '10', 'NULL'],
        ['704', 'い', '43', 'NULL'],
        ['705', 'る', '52', 'NULL'],
        ['706', 'そ', '46', 'NULL'],
        ['707', 'こ', '30', 'NULL'],
        ['708', 'ぬ', '11', 'NULL'],
        ['709', 'か', '100', 'NULL'],
        ['710', 'む', '96', 'NULL'],
        ['711', 'ゆ', '13', 'NULL'],
        ['712', 'ね', '19', 'NULL'],
        ['713', 'と', '64', 'NULL'],
        ['714', 'う', '31', 'NULL'],
        ['715', 'ほ', '16', 'NULL'],
        ['716', 'む', '77', 'NULL'],
        ['717', 'く', '69', 'NULL'],
        ['718', 'る', '72', 'NULL'],
        ['719', 'へ', '96', 'NULL'],
        ['720', 'し', '96', 'NULL'],
        ['721', 'と', '4', 'NULL'],
        ['722', 'を', '30', 'NULL'],
        ['723', 'わ', '29', 'NULL'],
        ['724', 'ち', '56', 'NULL'],
        ['725', 'ね', '46', 'NULL'],
        ['726', 'ん', '15', 'NULL'],
        ['727', 'り', '61', 'NULL'],
        ['728', 'ひ', '57', 'NULL'],
        ['729', 'こ', '49', 'NULL'],
        ['730', 'を', '68', 'NULL'],
        ['731', 'こ', '24', 'NULL'],
        ['732', 'て', '84', 'NULL'],
        ['733', 'す', '99', 'NULL'],
        ['734', 'か', '25', 'NULL'],
        ['735', 'お', '45', 'NULL'],
        ['736', 'く', '7', 'NULL'],
        ['737', 'え', '16', 'NULL'],
        ['738', 'は', '12', 'NULL'],
        ['739', 'こ', '67', 'NULL'],
        ['740', 'み', '79', 'NULL'],
        ['741', 'に', '77', 'NULL'],
        ['742', 'を', '60', 'NULL'],
        ['743', 'り', '8', 'NULL'],
        ['744', 'ろ', '47', 'NULL'],
        ['745', 'る', '27', 'NULL'],
        ['746', 'け', '56', 'NULL'],
        ['747', 'ほ', '52', 'NULL'],
        ['748', 'た', '19', 'NULL'],
        ['749', 'も', '23', 'NULL'],
        ['750', 'な', '65', 'NULL'],
        ['751', 'れ', '51', 'NULL'],
        ['752', 'な', '48', 'NULL'],
        ['753', 'も', '28', 'NULL'],
        ['754', 'さ', '44', 'NULL'],
        ['755', 'も', '61', 'NULL'],
        ['756', 'そ', '34', 'NULL'],
        ['757', 'し', '4', 'NULL'],
        ['758', 'ひ', '19', 'NULL'],
        ['759', 'け', '29', 'NULL'],
        ['760', 'き', '2', 'NULL'],
        ['761', 'は', '93', 'NULL'],
        ['762', 'ね', '73', 'NULL'],
        ['763', 'い', '69', 'NULL'],
        ['764', 'め', '85', 'NULL'],
        ['765', 'は', '100', 'NULL'],
        ['766', 'ち', '73', 'NULL'],
        ['767', 'ま', '78', 'NULL'],
        ['768', 'り', '22', 'NULL'],
        ['769', 'こ', '77', 'NULL'],
        ['770', 'さ', '22', 'NULL'],
        ['771', 'ゆ', '87', 'NULL'],
        ['772', 'と', '15', 'NULL'],
        ['773', 'ふ', '16', 'NULL'],
        ['774', 'う', '23', 'NULL'],
        ['775', 'と', '54', 'NULL'],
        ['776', 'ん', '28', 'NULL'],
        ['777', 'ぬ', '97', 'NULL'],
        ['778', 'を', '61', 'NULL'],
        ['779', 'き', '29', 'NULL'],
        ['780', 'き', '30', 'NULL'],
        ['781', 'よ', '5', 'NULL'],
        ['782', 'せ', '14', 'NULL'],
        ['783', 'な', '43', 'NULL'],
        ['784', 'ら', '94', 'NULL'],
        ['785', 'み', '14', 'NULL'],
        ['786', 'れ', '54', 'NULL'],
        ['787', 'す', '15', 'NULL'],
        ['788', 'う', '98', 'NULL'],
        ['789', 'こ', '40', 'NULL'],
        ['790', 'わ', '27', 'NULL'],
        ['791', 'む', '84', 'NULL'],
        ['792', 'え', '90', 'NULL'],
        ['793', 'ゆ', '83', 'NULL'],
        ['794', 'ほ', '91', 'NULL'],
        ['795', 'わ', '10', 'NULL'],
        ['796', 'ろ', '45', 'NULL'],
        ['797', 'ま', '18', 'NULL'],
        ['798', 'み', '7', 'NULL'],
        ['799', 'れ', '72', 'NULL'],
        ['800', 'よ', '77', 'NULL'],
        ['801', 'ね', '5', 'NULL'],
        ['802', 'に', '40', 'NULL'],
        ['803', 'ろ', '62', 'NULL'],
        ['804', 'め', '46', 'NULL'],
        ['805', 'う', '19', 'NULL'],
        ['806', 'む', '69', 'NULL'],
        ['807', 'さ', '26', 'NULL'],
        ['808', 'し', '17', 'NULL'],
        ['809', 'け', '10', 'NULL'],
        ['810', 'み', '89', 'NULL'],
        ['811', 'ぬ', '87', 'NULL'],
        ['812', 'く', '5', 'NULL'],
        ['813', 'や', '18', 'NULL'],
        ['814', 'ん', '87', 'NULL'],
        ['815', 'う', '30', 'NULL'],
        ['816', 'に', '12', 'NULL'],
        ['817', 'る', '57', 'NULL'],
        ['818', 'め', '28', 'NULL'],
        ['819', 'も', '82', 'NULL'],
        ['820', 'く', '72', 'NULL'],
        ['821', 'を', '80', 'NULL'],
        ['822', 'り', '43', 'NULL'],
        ['823', 'お', '78', 'NULL'],
        ['824', 'こ', '54', 'NULL'],
        ['825', 'り', '94', 'NULL'],
        ['826', 'け', '35', 'NULL'],
        ['827', 'た', '40', 'NULL'],
        ['828', 'へ', '5', 'NULL'],
        ['829', 'に', '4', 'NULL'],
        ['830', 'お', '59', 'NULL'],
        ['831', 'そ', '28', 'NULL'],
        ['832', 'へ', '14', 'NULL'],
        ['833', 'ひ', '55', 'NULL'],
        ['834', 'り', '73', 'NULL'],
        ['835', 'に', '14', 'NULL'],
        ['836', 'さ', '46', 'NULL'],
        ['837', 'め', '24', 'NULL'],
        ['838', 'そ', '86', 'NULL'],
        ['839', 'か', '91', 'NULL'],
        ['840', 'の', '82', 'NULL'],
        ['841', 'お', '59', 'NULL'],
        ['842', 'む', '64', 'NULL'],
        ['843', 'ふ', '80', 'NULL'],
        ['844', 'り', '58', 'NULL'],
        ['845', 'ま', '64', 'NULL'],
        ['846', 'を', '61', 'NULL'],
        ['847', 'ら', '86', 'NULL'],
        ['848', 'も', '70', 'NULL'],
        ['849', 'し', '47', 'NULL'],
        ['850', 'さ', '30', 'NULL'],
        ['851', 'り', '8', 'NULL'],
        ['852', 'な', '19', 'NULL'],
        ['853', 'め', '89', 'NULL'],
        ['854', 'つ', '47', 'NULL'],
        ['855', 'と', '36', 'NULL'],
        ['856', 'ち', '92', 'NULL'],
        ['857', 'し', '3', 'NULL'],
        ['858', 'り', '71', 'NULL'],
        ['859', 'し', '56', 'NULL'],
        ['860', 'て', '91', 'NULL'],
        ['861', 'の', '77', 'NULL'],
        ['862', 'さ', '72', 'NULL'],
        ['863', 'と', '12', 'NULL'],
        ['864', 'け', '73', 'NULL'],
        ['865', 'も', '53', 'NULL'],
        ['866', 'つ', '14', 'NULL'],
        ['867', 'し', '6', 'NULL'],
        ['868', 'ち', '72', 'NULL'],
        ['869', 'め', '65', 'NULL'],
        ['870', 'ゆ', '75', 'NULL'],
        ['871', 'に', '35', 'NULL'],
        ['872', 'り', '66', 'NULL'],
        ['873', 'を', '51', 'NULL'],
        ['874', 'ち', '5', 'NULL'],
        ['875', 'ら', '55', 'NULL'],
        ['876', 'ぬ', '78', 'NULL'],
        ['877', 'え', '98', 'NULL'],
        ['878', 'か', '8', 'NULL'],
        ['879', 'め', '89', 'NULL'],
        ['880', 'と', '43', 'NULL'],
        ['881', 'こ', '25', 'NULL'],
        ['882', 'ひ', '1', 'NULL'],
        ['883', 'か', '44', 'NULL'],
        ['884', 'ん', '74', 'NULL'],
        ['885', 'ひ', '94', 'NULL'],
        ['886', 'わ', '47', 'NULL'],
        ['887', 'ん', '83', 'NULL'],
        ['888', 'ち', '6', 'NULL'],
        ['889', 'ら', '77', 'NULL'],
        ['890', 'や', '97', 'NULL'],
        ['891', 'け', '62', 'NULL'],
        ['892', 'つ', '58', 'NULL'],
        ['893', 'め', '78', 'NULL'],
        ['894', 'な', '94', 'NULL'],
        ['895', 'き', '32', 'NULL'],
        ['896', 'く', '48', 'NULL'],
        ['897', 'ま', '45', 'NULL'],
        ['898', 'た', '41', 'NULL'],
        ['899', 'と', '71', 'NULL'],
        ['900', 'め', '47', 'NULL'],
        ['901', 'れ', '67', 'NULL'],
        ['902', 'う', '72', 'NULL'],
        ['903', 'わ', '14', 'NULL'],
        ['904', 'ゆ', '53', 'NULL'],
        ['905', 'り', '47', 'NULL'],
        ['906', 'ほ', '29', 'NULL'],
        ['907', 'へ', '100', 'NULL'],
        ['908', 'か', '89', 'NULL'],
        ['909', 'り', '74', 'NULL'],
        ['910', 'に', '88', 'NULL'],
        ['911', 'か', '24', 'NULL'],
        ['912', 'か', '95', 'NULL'],
        ['913', 'ほ', '27', 'NULL'],
        ['914', 'や', '29', 'NULL'],
        ['915', 'ね', '19', 'NULL'],
        ['916', 'し', '28', 'NULL'],
        ['917', 'ん', '32', 'NULL'],
        ['918', 'も', '5', 'NULL'],
        ['919', 'き', '22', 'NULL'],
        ['920', 'ほ', '40', 'NULL'],
        ['921', 'せ', '65', 'NULL'],
        ['922', 'く', '4', 'NULL'],
        ['923', 'れ', '20', 'NULL'],
        ['924', 'も', '5', 'NULL'],
        ['925', 'い', '37', 'NULL'],
        ['926', 'ね', '72', 'NULL'],
        ['927', 'ひ', '55', 'NULL'],
        ['928', 'え', '25', 'NULL'],
        ['929', 'な', '71', 'NULL'],
        ['930', 'わ', '96', 'NULL'],
        ['931', 'ふ', '76', 'NULL'],
        ['932', 'も', '41', 'NULL'],
        ['933', 'る', '38', 'NULL'],
        ['934', 'た', '67', 'NULL'],
        ['935', 'む', '96', 'NULL'],
        ['936', 'せ', '19', 'NULL'],
        ['937', 'い', '12', 'NULL'],
        ['938', 'ら', '51', 'NULL'],
        ['939', 'く', '42', 'NULL'],
        ['940', 'き', '94', 'NULL'],
        ['941', 'く', '49', 'NULL'],
        ['942', 'め', '97', 'NULL'],
        ['943', 'す', '77', 'NULL'],
        ['944', 'よ', '74', 'NULL'],
        ['945', 'せ', '26', 'NULL'],
        ['946', 'く', '16', 'NULL'],
        ['947', 'は', '20', 'NULL'],
        ['948', 'し', '7', 'NULL'],
        ['949', 'め', '99', 'NULL'],
        ['950', 'め', '63', 'NULL'],
        ['951', 'り', '75', 'NULL'],
        ['952', 'さ', '91', 'NULL'],
        ['953', 'た', '24', 'NULL'],
        ['954', 'ん', '54', 'NULL'],
        ['955', 'て', '2', 'NULL'],
        ['956', 'く', '64', 'NULL'],
        ['957', 'へ', '20', 'NULL'],
        ['958', 'も', '37', 'NULL'],
        ['959', 'け', '49', 'NULL'],
        ['960', 'ゆ', '97', 'NULL'],
        ['961', 'い', '73', 'NULL'],
        ['962', 'と', '39', 'NULL'],
        ['963', 'は', '34', 'NULL'],
        ['964', 'の', '8', 'NULL'],
        ['965', 'つ', '95', 'NULL'],
        ['966', 'ち', '3', 'NULL'],
        ['967', 'へ', '48', 'NULL'],
        ['968', 'く', '74', 'NULL'],
        ['969', 'ろ', '99', 'NULL'],
        ['970', 'い', '75', 'NULL'],
        ['971', 'ひ', '63', 'NULL'],
        ['972', 'も', '22', 'NULL'],
        ['973', 'あ', '40', 'NULL'],
        ['974', 'せ', '25', 'NULL'],
        ['975', 'て', '49', 'NULL'],
        ['976', 'さ', '54', 'NULL'],
        ['977', 'も', '91', 'NULL'],
        ['978', 'も', '42', 'NULL'],
        ['979', 'し', '44', 'NULL'],
        ['980', 'を', '88', 'NULL'],
        ['981', 'わ', '44', 'NULL'],
        ['982', 'め', '87', 'NULL'],
        ['983', 'す', '7', 'NULL'],
        ['984', 'る', '73', 'NULL'],
        ['985', 'ん', '32', 'NULL'],
        ['986', 'ね', '52', 'NULL'],
        ['987', 'よ', '99', 'NULL'],
        ['988', 'ろ', '52', 'NULL'],
        ['989', 'れ', '90', 'NULL'],
        ['990', 'ら', '43', 'NULL'],
        ['991', 'え', '83', 'NULL'],
        ['992', 'る', '55', 'NULL'],
        ['993', 'や', '71', 'NULL'],
        ['994', 'つ', '35', 'NULL'],
        ['995', 'い', '56', 'NULL'],
        ['996', 'て', '48', 'NULL'],
        ['997', 'ゆ', '44', 'NULL'],
        ['998', 'か', '33', 'NULL'],
        ['999', 'た', '52', 'NULL'],
        ['1000', 'て', '6', 'NULL'],
        ['1001', 'な', '84', 'NULL'],
        ['1002', 'ち', '10', 'NULL'],
        ['1003', 'た', '44', 'NULL'],
        ['1004', 'せ', '24', 'NULL'],
        ['1005', 'た', '5', 'NULL'],
        ['1006', 'や', '39', 'NULL'],
        ['1007', 'の', '46', 'NULL'],
        ['1008', 'つ', '36', 'NULL'],
        ['1009', 'め', '69', 'NULL'],
        ['1010', 'も', '33', 'NULL']
    ];

    private $headerless_kana1010 = [
        ['1', 'ふ', '39', 'NULL'],
        ['2', 'り', '29', 'NULL'],
        ['3', 'ふ', '87', 'NULL'],
        ['4', 'む', '78', 'NULL'],
        ['5', 'こ', '45', 'NULL'],
        ['6', 'る', '5', 'NULL'],
        ['7', 'さ', '2', 'NULL'],
        ['8', 'ろ', '72', 'NULL'],
        ['9', 'の', '13', 'NULL'],
        ['10', 'の', '100', 'NULL'],
        ['11', 'て', '67', 'NULL'],
        ['12', 'え', '37', 'NULL'],
        ['13', 'に', '44', 'NULL'],
        ['14', 'つ', '90', 'NULL'],
        ['15', 'た', '87', 'NULL'],
        ['16', 'る', '48', 'NULL'],
        ['17', 'の', '94', 'NULL'],
        ['18', 'き', '44', 'NULL'],
        ['19', 'う', '41', 'NULL'],
        ['20', 'わ', '6', 'NULL'],
        ['21', 'に', '4', 'NULL'],
        ['22', 'け', '75', 'NULL'],
        ['23', 'ほ', '60', 'NULL'],
        ['24', 'ひ', '77', 'NULL'],
        ['25', 'け', '56', 'NULL'],
        ['26', 'し', '7', 'NULL'],
        ['27', 'か', '91', 'NULL'],
        ['28', 'み', '5', 'NULL'],
        ['29', 'に', '74', 'NULL'],
        ['30', 'へ', '41', 'NULL'],
        ['31', 'つ', '36', 'NULL'],
        ['32', 'し', '48', 'NULL'],
        ['33', 'す', '26', 'NULL'],
        ['34', 'と', '59', 'NULL'],
        ['35', 'あ', '43', 'NULL'],
        ['36', 'り', '22', 'NULL'],
        ['37', 'の', '95', 'NULL'],
        ['38', 'ね', '50', 'NULL'],
        ['39', 'い', '91', 'NULL'],
        ['40', 'せ', '22', 'NULL'],
        ['41', 'し', '16', 'NULL'],
        ['42', 'か', '31', 'NULL'],
        ['43', 'ふ', '98', 'NULL'],
        ['44', 'ゆ', '34', 'NULL'],
        ['45', 'さ', '89', 'NULL'],
        ['46', 'ゆ', '58', 'NULL'],
        ['47', 'な', '44', 'NULL'],
        ['48', 'け', '86', 'NULL'],
        ['49', 'ん', '9', 'NULL'],
        ['50', 'れ', '96', 'NULL'],
        ['51', 'わ', '32', 'NULL'],
        ['52', 'や', '84', 'NULL'],
        ['53', 'ひ', '68', 'NULL'],
        ['54', 'け', '87', 'NULL'],
        ['55', 'せ', '92', 'NULL'],
        ['56', 'ら', '6', 'NULL'],
        ['57', 'ら', '100', 'NULL'],
        ['58', 'を', '46', 'NULL'],
        ['59', 'そ', '47', 'NULL'],
        ['60', 'さ', '86', 'NULL'],
        ['61', 'す', '14', 'NULL'],
        ['62', 'を', '36', 'NULL'],
        ['63', 'り', '20', 'NULL'],
        ['64', 'の', '56', 'NULL'],
        ['65', 'し', '94', 'NULL'],
        ['66', 'よ', '29', 'NULL'],
        ['67', 'れ', '29', 'NULL'],
        ['68', 'ん', '22', 'NULL'],
        ['69', 'ろ', '38', 'NULL'],
        ['70', 'う', '66', 'NULL'],
        ['71', 'さ', '65', 'NULL'],
        ['72', 'か', '17', 'NULL'],
        ['73', 'ろ', '33', 'NULL'],
        ['74', 'う', '73', 'NULL'],
        ['75', 'ま', '28', 'NULL'],
        ['76', 'て', '73', 'NULL'],
        ['77', 'ち', '32', 'NULL'],
        ['78', 'と', '13', 'NULL'],
        ['79', 'ま', '14', 'NULL'],
        ['80', 'ま', '54', 'NULL'],
        ['81', 'か', '73', 'NULL'],
        ['82', 'へ', '69', 'NULL'],
        ['83', 'ち', '75', 'NULL'],
        ['84', 'す', '100', 'NULL'],
        ['85', 'こ', '22', 'NULL'],
        ['86', 'と', '63', 'NULL'],
        ['87', 'す', '16', 'NULL'],
        ['88', 'さ', '84', 'NULL'],
        ['89', 'よ', '61', 'NULL'],
        ['90', 'な', '14', 'NULL'],
        ['91', 'わ', '16', 'NULL'],
        ['92', 'き', '89', 'NULL'],
        ['93', 'に', '34', 'NULL'],
        ['94', 'ん', '50', 'NULL'],
        ['95', 'て', '59', 'NULL'],
        ['96', 'ま', '98', 'NULL'],
        ['97', 'き', '63', 'NULL'],
        ['98', 'た', '25', 'NULL'],
        ['99', 'め', '64', 'NULL'],
        ['100', 'そ', '100', 'NULL'],
        ['101', 'に', '24', 'NULL'],
        ['102', 'ふ', '82', 'NULL'],
        ['103', 'も', '19', 'NULL'],
        ['104', 'な', '74', 'NULL'],
        ['105', 'お', '85', 'NULL'],
        ['106', 'ゆ', '56', 'NULL'],
        ['107', 'て', '86', 'NULL'],
        ['108', 'け', '26', 'NULL'],
        ['109', 'な', '51', 'NULL'],
        ['110', 'ゆ', '20', 'NULL'],
        ['111', 'さ', '7', 'NULL'],
        ['112', 'へ', '50', 'NULL'],
        ['113', 'は', '15', 'NULL'],
        ['114', 'ゆ', '32', 'NULL'],
        ['115', 'え', '42', 'NULL'],
        ['116', 'に', '91', 'NULL'],
        ['117', 'ゆ', '81', 'NULL'],
        ['118', 'す', '85', 'NULL'],
        ['119', 'や', '29', 'NULL'],
        ['120', 'へ', '28', 'NULL'],
        ['121', 'り', '81', 'NULL'],
        ['122', 'ち', '84', 'NULL'],
        ['123', 'ん', '21', 'NULL'],
        ['124', 'た', '66', 'NULL'],
        ['125', 'ん', '49', 'NULL'],
        ['126', 'め', '99', 'NULL'],
        ['127', 'る', '56', 'NULL'],
        ['128', 'れ', '50', 'NULL'],
        ['129', 'ね', '21', 'NULL'],
        ['130', 'め', '89', 'NULL'],
        ['131', 'む', '15', 'NULL'],
        ['132', 'も', '55', 'NULL'],
        ['133', 'え', '57', 'NULL'],
        ['134', 'は', '63', 'NULL'],
        ['135', 'ふ', '19', 'NULL'],
        ['136', 'ゆ', '30', 'NULL'],
        ['137', 'か', '17', 'NULL'],
        ['138', 'し', '79', 'NULL'],
        ['139', 'え', '55', 'NULL'],
        ['140', 'り', '79', 'NULL'],
        ['141', 'き', '30', 'NULL'],
        ['142', 'わ', '100', 'NULL'],
        ['143', 'く', '98', 'NULL'],
        ['144', 'け', '38', 'NULL'],
        ['145', 'け', '9', 'NULL'],
        ['146', 'い', '65', 'NULL'],
        ['147', 'ゆ', '15', 'NULL'],
        ['148', 'え', '29', 'NULL'],
        ['149', 'し', '33', 'NULL'],
        ['150', 'う', '9', 'NULL'],
        ['151', 'め', '67', 'NULL'],
        ['152', 'た', '98', 'NULL'],
        ['153', 'と', '18', 'NULL'],
        ['154', 'は', '99', 'NULL'],
        ['155', 'む', '91', 'NULL'],
        ['156', 'さ', '34', 'NULL'],
        ['157', 'ひ', '15', 'NULL'],
        ['158', 'か', '28', 'NULL'],
        ['159', 'ら', '5', 'NULL'],
        ['160', 'ち', '53', 'NULL'],
        ['161', 'け', '66', 'NULL'],
        ['162', 'ひ', '33', 'NULL'],
        ['163', 'て', '85', 'NULL'],
        ['164', 'ほ', '21', 'NULL'],
        ['165', 'め', '7', 'NULL'],
        ['166', 'や', '74', 'NULL'],
        ['167', 'め', '62', 'NULL'],
        ['168', 'み', '86', 'NULL'],
        ['169', 'に', '77', 'NULL'],
        ['170', 'う', '69', 'NULL'],
        ['171', 'つ', '19', 'NULL'],
        ['172', 'ら', '29', 'NULL'],
        ['173', 'ち', '91', 'NULL'],
        ['174', 'よ', '8', 'NULL'],
        ['175', 'め', '24', 'NULL'],
        ['176', 'け', '65', 'NULL'],
        ['177', 'ら', '67', 'NULL'],
        ['178', 'き', '95', 'NULL'],
        ['179', 'く', '57', 'NULL'],
        ['180', 'ろ', '9', 'NULL'],
        ['181', 'ゆ', '42', 'NULL'],
        ['182', 'こ', '81', 'NULL'],
        ['183', 'る', '24', 'NULL'],
        ['184', 'あ', '41', 'NULL'],
        ['185', 'し', '72', 'NULL'],
        ['186', 'る', '33', 'NULL'],
        ['187', 'む', '15', 'NULL'],
        ['188', 'さ', '63', 'NULL'],
        ['189', 'れ', '78', 'NULL'],
        ['190', 'ふ', '91', 'NULL'],
        ['191', 'ね', '91', 'NULL'],
        ['192', 'え', '40', 'NULL'],
        ['193', 'し', '88', 'NULL'],
        ['194', 'に', '57', 'NULL'],
        ['195', 'と', '48', 'NULL'],
        ['196', 'ふ', '2', 'NULL'],
        ['197', 'た', '85', 'NULL'],
        ['198', 'と', '12', 'NULL'],
        ['199', 'の', '78', 'NULL'],
        ['200', 'て', '82', 'NULL'],
        ['201', 'ん', '71', 'NULL'],
        ['202', 'ぬ', '32', 'NULL'],
        ['203', 'へ', '14', 'NULL'],
        ['204', 'り', '33', 'NULL'],
        ['205', 'ふ', '70', 'NULL'],
        ['206', 'て', '62', 'NULL'],
        ['207', 'ふ', '37', 'NULL'],
        ['208', 'き', '80', 'NULL'],
        ['209', 'み', '14', 'NULL'],
        ['210', 'よ', '26', 'NULL'],
        ['211', 'つ', '18', 'NULL'],
        ['212', 'と', '49', 'NULL'],
        ['213', 'し', '74', 'NULL'],
        ['214', 'な', '68', 'NULL'],
        ['215', 'あ', '20', 'NULL'],
        ['216', 'わ', '82', 'NULL'],
        ['217', 'つ', '10', 'NULL'],
        ['218', 'け', '12', 'NULL'],
        ['219', 'ん', '85', 'NULL'],
        ['220', 'ろ', '5', 'NULL'],
        ['221', 'み', '30', 'NULL'],
        ['222', 'さ', '40', 'NULL'],
        ['223', 'の', '78', 'NULL'],
        ['224', 'へ', '40', 'NULL'],
        ['225', 'よ', '88', 'NULL'],
        ['226', 'ほ', '36', 'NULL'],
        ['227', 'つ', '39', 'NULL'],
        ['228', 'し', '94', 'NULL'],
        ['229', 'の', '97', 'NULL'],
        ['230', 'う', '81', 'NULL'],
        ['231', 'た', '97', 'NULL'],
        ['232', 'お', '4', 'NULL'],
        ['233', 'こ', '53', 'NULL'],
        ['234', 'か', '98', 'NULL'],
        ['235', 'む', '70', 'NULL'],
        ['236', 'ね', '8', 'NULL'],
        ['237', 'た', '56', 'NULL'],
        ['238', 'か', '28', 'NULL'],
        ['239', 'も', '74', 'NULL'],
        ['240', 'す', '52', 'NULL'],
        ['241', 'を', '79', 'NULL'],
        ['242', 'か', '89', 'NULL'],
        ['243', 'あ', '33', 'NULL'],
        ['244', 'せ', '3', 'NULL'],
        ['245', 'き', '18', 'NULL'],
        ['246', 'に', '100', 'NULL'],
        ['247', 'へ', '91', 'NULL'],
        ['248', 'む', '33', 'NULL'],
        ['249', 'ま', '43', 'NULL'],
        ['250', 'は', '9', 'NULL'],
        ['251', 'は', '16', 'NULL'],
        ['252', 'ら', '60', 'NULL'],
        ['253', 'め', '44', 'NULL'],
        ['254', 'た', '55', 'NULL'],
        ['255', 'も', '69', 'NULL'],
        ['256', 'ほ', '61', 'NULL'],
        ['257', 'ひ', '10', 'NULL'],
        ['258', 'ふ', '78', 'NULL'],
        ['259', 'る', '39', 'NULL'],
        ['260', 'き', '77', 'NULL'],
        ['261', 'こ', '78', 'NULL'],
        ['262', 'い', '62', 'NULL'],
        ['263', 'み', '92', 'NULL'],
        ['264', 'み', '35', 'NULL'],
        ['265', 'け', '25', 'NULL'],
        ['266', 'ゆ', '71', 'NULL'],
        ['267', 'ひ', '68', 'NULL'],
        ['268', 'へ', '39', 'NULL'],
        ['269', 'ち', '81', 'NULL'],
        ['270', 'へ', '41', 'NULL'],
        ['271', 'は', '94', 'NULL'],
        ['272', 'な', '39', 'NULL'],
        ['273', 'つ', '68', 'NULL'],
        ['274', 'く', '100', 'NULL'],
        ['275', 'ほ', '19', 'NULL'],
        ['276', 'れ', '44', 'NULL'],
        ['277', 'の', '11', 'NULL'],
        ['278', 'ふ', '31', 'NULL'],
        ['279', 'む', '54', 'NULL'],
        ['280', 'か', '90', 'NULL'],
        ['281', 'よ', '55', 'NULL'],
        ['282', 'ん', '49', 'NULL'],
        ['283', 'せ', '81', 'NULL'],
        ['284', 'こ', '15', 'NULL'],
        ['285', 'よ', '63', 'NULL'],
        ['286', 'わ', '49', 'NULL'],
        ['287', 'ん', '5', 'NULL'],
        ['288', 'は', '47', 'NULL'],
        ['289', 'れ', '29', 'NULL'],
        ['290', 'ふ', '65', 'NULL'],
        ['291', 'け', '24', 'NULL'],
        ['292', 'あ', '6', 'NULL'],
        ['293', 'え', '36', 'NULL'],
        ['294', 'た', '13', 'NULL'],
        ['295', 'り', '33', 'NULL'],
        ['296', 'き', '33', 'NULL'],
        ['297', 'な', '16', 'NULL'],
        ['298', 'ま', '33', 'NULL'],
        ['299', 'お', '21', 'NULL'],
        ['300', 'ね', '22', 'NULL'],
        ['301', 'と', '30', 'NULL'],
        ['302', 'ら', '75', 'NULL'],
        ['303', 'お', '35', 'NULL'],
        ['304', 'ふ', '11', 'NULL'],
        ['305', 'ら', '62', 'NULL'],
        ['306', 'ね', '1', 'NULL'],
        ['307', 'は', '46', 'NULL'],
        ['308', 'も', '83', 'NULL'],
        ['309', 'ん', '61', 'NULL'],
        ['310', 'ゆ', '30', 'NULL'],
        ['311', 'ち', '29', 'NULL'],
        ['312', 'ち', '31', 'NULL'],
        ['313', 'か', '71', 'NULL'],
        ['314', 'か', '94', 'NULL'],
        ['315', 'と', '38', 'NULL'],
        ['316', 'え', '16', 'NULL'],
        ['317', 'ゆ', '2', 'NULL'],
        ['318', 'い', '2', 'NULL'],
        ['319', 'さ', '79', 'NULL'],
        ['320', 'ち', '32', 'NULL'],
        ['321', 'あ', '43', 'NULL'],
        ['322', 'ら', '39', 'NULL'],
        ['323', 'う', '68', 'NULL'],
        ['324', 'を', '37', 'NULL'],
        ['325', 'あ', '89', 'NULL'],
        ['326', 'せ', '68', 'NULL'],
        ['327', 'え', '23', 'NULL'],
        ['328', 'に', '51', 'NULL'],
        ['329', 'ち', '92', 'NULL'],
        ['330', 'あ', '69', 'NULL'],
        ['331', 'ね', '45', 'NULL'],
        ['332', 'ふ', '66', 'NULL'],
        ['333', 'せ', '89', 'NULL'],
        ['334', 'ひ', '42', 'NULL'],
        ['335', 'ち', '50', 'NULL'],
        ['336', 'も', '4', 'NULL'],
        ['337', 'を', '54', 'NULL'],
        ['338', 'し', '33', 'NULL'],
        ['339', 'れ', '73', 'NULL'],
        ['340', 'す', '51', 'NULL'],
        ['341', 'く', '68', 'NULL'],
        ['342', 'み', '75', 'NULL'],
        ['343', 'わ', '60', 'NULL'],
        ['344', 'す', '86', 'NULL'],
        ['345', 'る', '13', 'NULL'],
        ['346', 'も', '70', 'NULL'],
        ['347', 'か', '52', 'NULL'],
        ['348', 'む', '31', 'NULL'],
        ['349', 'り', '49', 'NULL'],
        ['350', 'よ', '31', 'NULL'],
        ['351', 'を', '46', 'NULL'],
        ['352', 'は', '72', 'NULL'],
        ['353', 'あ', '27', 'NULL'],
        ['354', 'れ', '20', 'NULL'],
        ['355', 'む', '58', 'NULL'],
        ['356', 'む', '73', 'NULL'],
        ['357', 'ん', '1', 'NULL'],
        ['358', 'け', '70', 'NULL'],
        ['359', 'ひ', '19', 'NULL'],
        ['360', 'お', '97', 'NULL'],
        ['361', 'を', '55', 'NULL'],
        ['362', 'か', '94', 'NULL'],
        ['363', 'ひ', '100', 'NULL'],
        ['364', 'た', '10', 'NULL'],
        ['365', 'ら', '93', 'NULL'],
        ['366', 'せ', '96', 'NULL'],
        ['367', 'ふ', '92', 'NULL'],
        ['368', 'や', '95', 'NULL'],
        ['369', 'に', '51', 'NULL'],
        ['370', 'け', '72', 'NULL'],
        ['371', 'わ', '7', 'NULL'],
        ['372', 'り', '44', 'NULL'],
        ['373', 'て', '51', 'NULL'],
        ['374', 'る', '44', 'NULL'],
        ['375', 'て', '77', 'NULL'],
        ['376', 'み', '94', 'NULL'],
        ['377', 'お', '90', 'NULL'],
        ['378', 'の', '62', 'NULL'],
        ['379', 'わ', '32', 'NULL'],
        ['380', 'な', '38', 'NULL'],
        ['381', 'を', '56', 'NULL'],
        ['382', 'す', '22', 'NULL'],
        ['383', 'た', '26', 'NULL'],
        ['384', 'く', '24', 'NULL'],
        ['385', 'い', '47', 'NULL'],
        ['386', 'に', '70', 'NULL'],
        ['387', 'よ', '43', 'NULL'],
        ['388', 'ふ', '51', 'NULL'],
        ['389', 'こ', '96', 'NULL'],
        ['390', 'る', '29', 'NULL'],
        ['391', 'も', '97', 'NULL'],
        ['392', 'ひ', '10', 'NULL'],
        ['393', 'は', '59', 'NULL'],
        ['394', 'ろ', '12', 'NULL'],
        ['395', 'あ', '47', 'NULL'],
        ['396', 'け', '1', 'NULL'],
        ['397', 'き', '53', 'NULL'],
        ['398', 'は', '95', 'NULL'],
        ['399', 'む', '66', 'NULL'],
        ['400', 'は', '78', 'NULL'],
        ['401', 'ゆ', '18', 'NULL'],
        ['402', 'よ', '47', 'NULL'],
        ['403', 'し', '23', 'NULL'],
        ['404', 'て', '58', 'NULL'],
        ['405', 'も', '19', 'NULL'],
        ['406', 'こ', '27', 'NULL'],
        ['407', 'わ', '6', 'NULL'],
        ['408', 'こ', '13', 'NULL'],
        ['409', 'お', '36', 'NULL'],
        ['410', 'え', '67', 'NULL'],
        ['411', 'る', '41', 'NULL'],
        ['412', 'い', '98', 'NULL'],
        ['413', 'た', '79', 'NULL'],
        ['414', 'そ', '25', 'NULL'],
        ['415', 'ゆ', '63', 'NULL'],
        ['416', 'へ', '38', 'NULL'],
        ['417', 'か', '56', 'NULL'],
        ['418', 'き', '29', 'NULL'],
        ['419', 'な', '79', 'NULL'],
        ['420', 'を', '52', 'NULL'],
        ['421', 'ぬ', '55', 'NULL'],
        ['422', 'き', '65', 'NULL'],
        ['423', 'に', '75', 'NULL'],
        ['424', 'ま', '36', 'NULL'],
        ['425', 'な', '89', 'NULL'],
        ['426', 'う', '96', 'NULL'],
        ['427', 'す', '94', 'NULL'],
        ['428', 'め', '15', 'NULL'],
        ['429', 'あ', '57', 'NULL'],
        ['430', 'れ', '33', 'NULL'],
        ['431', 'ぬ', '36', 'NULL'],
        ['432', 'え', '4', 'NULL'],
        ['433', 'ひ', '46', 'NULL'],
        ['434', 'け', '69', 'NULL'],
        ['435', 'た', '31', 'NULL'],
        ['436', 'き', '65', 'NULL'],
        ['437', 'れ', '51', 'NULL'],
        ['438', 'わ', '100', 'NULL'],
        ['439', 'い', '17', 'NULL'],
        ['440', 'つ', '3', 'NULL'],
        ['441', 'ひ', '26', 'NULL'],
        ['442', 'ひ', '15', 'NULL'],
        ['443', 'お', '76', 'NULL'],
        ['444', 'ま', '56', 'NULL'],
        ['445', 'ち', '23', 'NULL'],
        ['446', 'せ', '89', 'NULL'],
        ['447', 'も', '59', 'NULL'],
        ['448', 'か', '20', 'NULL'],
        ['449', 'は', '21', 'NULL'],
        ['450', 'え', '44', 'NULL'],
        ['451', 'う', '100', 'NULL'],
        ['452', 'と', '31', 'NULL'],
        ['453', 'ね', '91', 'NULL'],
        ['454', 'ふ', '17', 'NULL'],
        ['455', 'せ', '83', 'NULL'],
        ['456', 'こ', '29', 'NULL'],
        ['457', 'わ', '35', 'NULL'],
        ['458', 'の', '42', 'NULL'],
        ['459', 'ひ', '31', 'NULL'],
        ['460', 'や', '41', 'NULL'],
        ['461', 'は', '72', 'NULL'],
        ['462', 'も', '87', 'NULL'],
        ['463', 'こ', '94', 'NULL'],
        ['464', 'つ', '28', 'NULL'],
        ['465', 'み', '45', 'NULL'],
        ['466', 'ひ', '19', 'NULL'],
        ['467', 'ち', '43', 'NULL'],
        ['468', 'け', '8', 'NULL'],
        ['469', 'は', '42', 'NULL'],
        ['470', 'を', '98', 'NULL'],
        ['471', 'つ', '66', 'NULL'],
        ['472', 'に', '22', 'NULL'],
        ['473', 'ほ', '38', 'NULL'],
        ['474', 'の', '50', 'NULL'],
        ['475', 'り', '43', 'NULL'],
        ['476', 'せ', '99', 'NULL'],
        ['477', 'よ', '4', 'NULL'],
        ['478', 'や', '22', 'NULL'],
        ['479', 'る', '72', 'NULL'],
        ['480', 'れ', '33', 'NULL'],
        ['481', 'う', '33', 'NULL'],
        ['482', 'り', '38', 'NULL'],
        ['483', 'な', '71', 'NULL'],
        ['484', 'そ', '100', 'NULL'],
        ['485', 'さ', '19', 'NULL'],
        ['486', 'お', '79', 'NULL'],
        ['487', 'は', '58', 'NULL'],
        ['488', 'ね', '48', 'NULL'],
        ['489', 'ぬ', '16', 'NULL'],
        ['490', 'る', '7', 'NULL'],
        ['491', 'も', '25', 'NULL'],
        ['492', 'め', '49', 'NULL'],
        ['493', 'う', '69', 'NULL'],
        ['494', 'か', '53', 'NULL'],
        ['495', 'お', '2', 'NULL'],
        ['496', 'ひ', '59', 'NULL'],
        ['497', 'い', '68', 'NULL'],
        ['498', 'む', '42', 'NULL'],
        ['499', 'ほ', '7', 'NULL'],
        ['500', 'に', '12', 'NULL'],
        ['501', 'か', '11', 'NULL'],
        ['502', 'ろ', '28', 'NULL'],
        ['503', 'わ', '77', 'NULL'],
        ['504', 'ら', '62', 'NULL'],
        ['505', 'ゆ', '58', 'NULL'],
        ['506', 'ゆ', '72', 'NULL'],
        ['507', 'お', '54', 'NULL'],
        ['508', 'そ', '13', 'NULL'],
        ['509', 'み', '71', 'NULL'],
        ['510', 'て', '71', 'NULL'],
        ['511', 'か', '36', 'NULL'],
        ['512', 'あ', '74', 'NULL'],
        ['513', 'す', '1', 'NULL'],
        ['514', 'す', '11', 'NULL'],
        ['515', 'も', '46', 'NULL'],
        ['516', 'い', '66', 'NULL'],
        ['517', 'て', '19', 'NULL'],
        ['518', 'み', '26', 'NULL'],
        ['519', 'る', '94', 'NULL'],
        ['520', 'こ', '29', 'NULL'],
        ['521', 'た', '89', 'NULL'],
        ['522', 'そ', '3', 'NULL'],
        ['523', 'ゆ', '67', 'NULL'],
        ['524', 'こ', '93', 'NULL'],
        ['525', 'ら', '55', 'NULL'],
        ['526', 'ひ', '68', 'NULL'],
        ['527', 'み', '50', 'NULL'],
        ['528', 'け', '33', 'NULL'],
        ['529', 'ひ', '46', 'NULL'],
        ['530', 'や', '79', 'NULL'],
        ['531', 'き', '35', 'NULL'],
        ['532', 'ね', '72', 'NULL'],
        ['533', 'を', '21', 'NULL'],
        ['534', 'を', '1', 'NULL'],
        ['535', 'も', '15', 'NULL'],
        ['536', 'は', '29', 'NULL'],
        ['537', 'す', '40', 'NULL'],
        ['538', 'け', '91', 'NULL'],
        ['539', 'せ', '55', 'NULL'],
        ['540', 'に', '21', 'NULL'],
        ['541', 'へ', '4', 'NULL'],
        ['542', 'ゆ', '52', 'NULL'],
        ['543', 'の', '52', 'NULL'],
        ['544', 'あ', '69', 'NULL'],
        ['545', 'ひ', '38', 'NULL'],
        ['546', 'も', '36', 'NULL'],
        ['547', 'は', '69', 'NULL'],
        ['548', 'は', '70', 'NULL'],
        ['549', 'も', '74', 'NULL'],
        ['550', 'む', '47', 'NULL'],
        ['551', 'れ', '5', 'NULL'],
        ['552', 'ね', '6', 'NULL'],
        ['553', 'に', '17', 'NULL'],
        ['554', 'せ', '97', 'NULL'],
        ['555', 'わ', '49', 'NULL'],
        ['556', 'く', '5', 'NULL'],
        ['557', 'る', '18', 'NULL'],
        ['558', 'と', '47', 'NULL'],
        ['559', 'え', '60', 'NULL'],
        ['560', 'め', '74', 'NULL'],
        ['561', 'と', '97', 'NULL'],
        ['562', 'た', '69', 'NULL'],
        ['563', 'に', '81', 'NULL'],
        ['564', 'さ', '30', 'NULL'],
        ['565', 'わ', '39', 'NULL'],
        ['566', 'う', '68', 'NULL'],
        ['567', 'め', '49', 'NULL'],
        ['568', 'る', '19', 'NULL'],
        ['569', 'こ', '50', 'NULL'],
        ['570', 'か', '68', 'NULL'],
        ['571', 'お', '41', 'NULL'],
        ['572', 'を', '32', 'NULL'],
        ['573', 'に', '36', 'NULL'],
        ['574', 'に', '35', 'NULL'],
        ['575', 'め', '2', 'NULL'],
        ['576', 'い', '43', 'NULL'],
        ['577', 'さ', '10', 'NULL'],
        ['578', 'し', '5', 'NULL'],
        ['579', 'と', '84', 'NULL'],
        ['580', 'そ', '31', 'NULL'],
        ['581', 'き', '83', 'NULL'],
        ['582', 'そ', '83', 'NULL'],
        ['583', 'ぬ', '31', 'NULL'],
        ['584', 'わ', '54', 'NULL'],
        ['585', 'ち', '61', 'NULL'],
        ['586', 'み', '14', 'NULL'],
        ['587', 'れ', '55', 'NULL'],
        ['588', 'ほ', '85', 'NULL'],
        ['589', 'わ', '8', 'NULL'],
        ['590', 'ひ', '18', 'NULL'],
        ['591', 'も', '62', 'NULL'],
        ['592', 'く', '48', 'NULL'],
        ['593', 'け', '57', 'NULL'],
        ['594', 'こ', '32', 'NULL'],
        ['595', 'ね', '56', 'NULL'],
        ['596', 'か', '25', 'NULL'],
        ['597', 'の', '4', 'NULL'],
        ['598', 'も', '34', 'NULL'],
        ['599', 'て', '24', 'NULL'],
        ['600', 'す', '11', 'NULL'],
        ['601', 'と', '70', 'NULL'],
        ['602', 'こ', '9', 'NULL'],
        ['603', 'せ', '53', 'NULL'],
        ['604', 'わ', '4', 'NULL'],
        ['605', 'る', '12', 'NULL'],
        ['606', 'り', '53', 'NULL'],
        ['607', 'に', '88', 'NULL'],
        ['608', 'こ', '63', 'NULL'],
        ['609', 'も', '66', 'NULL'],
        ['610', 'れ', '25', 'NULL'],
        ['611', 'ぬ', '88', 'NULL'],
        ['612', 'そ', '2', 'NULL'],
        ['613', 'ぬ', '99', 'NULL'],
        ['614', 'る', '61', 'NULL'],
        ['615', 'ゆ', '63', 'NULL'],
        ['616', 'た', '92', 'NULL'],
        ['617', 'は', '37', 'NULL'],
        ['618', 'ち', '73', 'NULL'],
        ['619', 'か', '19', 'NULL'],
        ['620', 'れ', '23', 'NULL'],
        ['621', 'る', '97', 'NULL'],
        ['622', 'に', '53', 'NULL'],
        ['623', 'う', '49', 'NULL'],
        ['624', 'な', '39', 'NULL'],
        ['625', 'ぬ', '10', 'NULL'],
        ['626', 'う', '68', 'NULL'],
        ['627', 'す', '87', 'NULL'],
        ['628', 'る', '12', 'NULL'],
        ['629', 'た', '4', 'NULL'],
        ['630', 'ふ', '14', 'NULL'],
        ['631', 'へ', '1', 'NULL'],
        ['632', 'あ', '54', 'NULL'],
        ['633', 'み', '9', 'NULL'],
        ['634', 'い', '67', 'NULL'],
        ['635', 'そ', '78', 'NULL'],
        ['636', 'そ', '24', 'NULL'],
        ['637', 'ろ', '61', 'NULL'],
        ['638', 'ら', '99', 'NULL'],
        ['639', 'る', '59', 'NULL'],
        ['640', 'つ', '33', 'NULL'],
        ['641', 'く', '58', 'NULL'],
        ['642', 'な', '39', 'NULL'],
        ['643', 'ま', '90', 'NULL'],
        ['644', 'し', '66', 'NULL'],
        ['645', 'す', '94', 'NULL'],
        ['646', 'き', '46', 'NULL'],
        ['647', 'め', '100', 'NULL'],
        ['648', 'よ', '23', 'NULL'],
        ['649', 'そ', '96', 'NULL'],
        ['650', 'け', '55', 'NULL'],
        ['651', 'め', '22', 'NULL'],
        ['652', 'お', '16', 'NULL'],
        ['653', 'く', '88', 'NULL'],
        ['654', 'ろ', '93', 'NULL'],
        ['655', 'ほ', '60', 'NULL'],
        ['656', 'た', '42', 'NULL'],
        ['657', 'け', '14', 'NULL'],
        ['658', 'た', '46', 'NULL'],
        ['659', 'う', '84', 'NULL'],
        ['660', 'え', '59', 'NULL'],
        ['661', 'き', '96', 'NULL'],
        ['662', 'の', '60', 'NULL'],
        ['663', 'り', '40', 'NULL'],
        ['664', 'に', '69', 'NULL'],
        ['665', 'に', '15', 'NULL'],
        ['666', 'め', '30', 'NULL'],
        ['667', 'わ', '93', 'NULL'],
        ['668', 'れ', '86', 'NULL'],
        ['669', 'た', '65', 'NULL'],
        ['670', 'よ', '52', 'NULL'],
        ['671', 'を', '8', 'NULL'],
        ['672', 'ら', '39', 'NULL'],
        ['673', 'な', '77', 'NULL'],
        ['674', 'わ', '7', 'NULL'],
        ['675', 'し', '51', 'NULL'],
        ['676', 'ん', '83', 'NULL'],
        ['677', 'か', '57', 'NULL'],
        ['678', 'ゆ', '49', 'NULL'],
        ['679', 'し', '13', 'NULL'],
        ['680', 'ろ', '50', 'NULL'],
        ['681', 'ひ', '40', 'NULL'],
        ['682', 'を', '40', 'NULL'],
        ['683', 'へ', '11', 'NULL'],
        ['684', 'く', '72', 'NULL'],
        ['685', 'ろ', '19', 'NULL'],
        ['686', 'え', '20', 'NULL'],
        ['687', 'え', '68', 'NULL'],
        ['688', 'と', '70', 'NULL'],
        ['689', 'う', '79', 'NULL'],
        ['690', 'ひ', '80', 'NULL'],
        ['691', 'す', '61', 'NULL'],
        ['692', 'わ', '27', 'NULL'],
        ['693', 'ち', '93', 'NULL'],
        ['694', 'る', '88', 'NULL'],
        ['695', 'ま', '73', 'NULL'],
        ['696', 'も', '88', 'NULL'],
        ['697', 'ふ', '97', 'NULL'],
        ['698', 'み', '23', 'NULL'],
        ['699', 'ね', '28', 'NULL'],
        ['700', 'て', '26', 'NULL'],
        ['701', 'と', '61', 'NULL'],
        ['702', 'こ', '56', 'NULL'],
        ['703', 'め', '10', 'NULL'],
        ['704', 'い', '43', 'NULL'],
        ['705', 'る', '52', 'NULL'],
        ['706', 'そ', '46', 'NULL'],
        ['707', 'こ', '30', 'NULL'],
        ['708', 'ぬ', '11', 'NULL'],
        ['709', 'か', '100', 'NULL'],
        ['710', 'む', '96', 'NULL'],
        ['711', 'ゆ', '13', 'NULL'],
        ['712', 'ね', '19', 'NULL'],
        ['713', 'と', '64', 'NULL'],
        ['714', 'う', '31', 'NULL'],
        ['715', 'ほ', '16', 'NULL'],
        ['716', 'む', '77', 'NULL'],
        ['717', 'く', '69', 'NULL'],
        ['718', 'る', '72', 'NULL'],
        ['719', 'へ', '96', 'NULL'],
        ['720', 'し', '96', 'NULL'],
        ['721', 'と', '4', 'NULL'],
        ['722', 'を', '30', 'NULL'],
        ['723', 'わ', '29', 'NULL'],
        ['724', 'ち', '56', 'NULL'],
        ['725', 'ね', '46', 'NULL'],
        ['726', 'ん', '15', 'NULL'],
        ['727', 'り', '61', 'NULL'],
        ['728', 'ひ', '57', 'NULL'],
        ['729', 'こ', '49', 'NULL'],
        ['730', 'を', '68', 'NULL'],
        ['731', 'こ', '24', 'NULL'],
        ['732', 'て', '84', 'NULL'],
        ['733', 'す', '99', 'NULL'],
        ['734', 'か', '25', 'NULL'],
        ['735', 'お', '45', 'NULL'],
        ['736', 'く', '7', 'NULL'],
        ['737', 'え', '16', 'NULL'],
        ['738', 'は', '12', 'NULL'],
        ['739', 'こ', '67', 'NULL'],
        ['740', 'み', '79', 'NULL'],
        ['741', 'に', '77', 'NULL'],
        ['742', 'を', '60', 'NULL'],
        ['743', 'り', '8', 'NULL'],
        ['744', 'ろ', '47', 'NULL'],
        ['745', 'る', '27', 'NULL'],
        ['746', 'け', '56', 'NULL'],
        ['747', 'ほ', '52', 'NULL'],
        ['748', 'た', '19', 'NULL'],
        ['749', 'も', '23', 'NULL'],
        ['750', 'な', '65', 'NULL'],
        ['751', 'れ', '51', 'NULL'],
        ['752', 'な', '48', 'NULL'],
        ['753', 'も', '28', 'NULL'],
        ['754', 'さ', '44', 'NULL'],
        ['755', 'も', '61', 'NULL'],
        ['756', 'そ', '34', 'NULL'],
        ['757', 'し', '4', 'NULL'],
        ['758', 'ひ', '19', 'NULL'],
        ['759', 'け', '29', 'NULL'],
        ['760', 'き', '2', 'NULL'],
        ['761', 'は', '93', 'NULL'],
        ['762', 'ね', '73', 'NULL'],
        ['763', 'い', '69', 'NULL'],
        ['764', 'め', '85', 'NULL'],
        ['765', 'は', '100', 'NULL'],
        ['766', 'ち', '73', 'NULL'],
        ['767', 'ま', '78', 'NULL'],
        ['768', 'り', '22', 'NULL'],
        ['769', 'こ', '77', 'NULL'],
        ['770', 'さ', '22', 'NULL'],
        ['771', 'ゆ', '87', 'NULL'],
        ['772', 'と', '15', 'NULL'],
        ['773', 'ふ', '16', 'NULL'],
        ['774', 'う', '23', 'NULL'],
        ['775', 'と', '54', 'NULL'],
        ['776', 'ん', '28', 'NULL'],
        ['777', 'ぬ', '97', 'NULL'],
        ['778', 'を', '61', 'NULL'],
        ['779', 'き', '29', 'NULL'],
        ['780', 'き', '30', 'NULL'],
        ['781', 'よ', '5', 'NULL'],
        ['782', 'せ', '14', 'NULL'],
        ['783', 'な', '43', 'NULL'],
        ['784', 'ら', '94', 'NULL'],
        ['785', 'み', '14', 'NULL'],
        ['786', 'れ', '54', 'NULL'],
        ['787', 'す', '15', 'NULL'],
        ['788', 'う', '98', 'NULL'],
        ['789', 'こ', '40', 'NULL'],
        ['790', 'わ', '27', 'NULL'],
        ['791', 'む', '84', 'NULL'],
        ['792', 'え', '90', 'NULL'],
        ['793', 'ゆ', '83', 'NULL'],
        ['794', 'ほ', '91', 'NULL'],
        ['795', 'わ', '10', 'NULL'],
        ['796', 'ろ', '45', 'NULL'],
        ['797', 'ま', '18', 'NULL'],
        ['798', 'み', '7', 'NULL'],
        ['799', 'れ', '72', 'NULL'],
        ['800', 'よ', '77', 'NULL'],
        ['801', 'ね', '5', 'NULL'],
        ['802', 'に', '40', 'NULL'],
        ['803', 'ろ', '62', 'NULL'],
        ['804', 'め', '46', 'NULL'],
        ['805', 'う', '19', 'NULL'],
        ['806', 'む', '69', 'NULL'],
        ['807', 'さ', '26', 'NULL'],
        ['808', 'し', '17', 'NULL'],
        ['809', 'け', '10', 'NULL'],
        ['810', 'み', '89', 'NULL'],
        ['811', 'ぬ', '87', 'NULL'],
        ['812', 'く', '5', 'NULL'],
        ['813', 'や', '18', 'NULL'],
        ['814', 'ん', '87', 'NULL'],
        ['815', 'う', '30', 'NULL'],
        ['816', 'に', '12', 'NULL'],
        ['817', 'る', '57', 'NULL'],
        ['818', 'め', '28', 'NULL'],
        ['819', 'も', '82', 'NULL'],
        ['820', 'く', '72', 'NULL'],
        ['821', 'を', '80', 'NULL'],
        ['822', 'り', '43', 'NULL'],
        ['823', 'お', '78', 'NULL'],
        ['824', 'こ', '54', 'NULL'],
        ['825', 'り', '94', 'NULL'],
        ['826', 'け', '35', 'NULL'],
        ['827', 'た', '40', 'NULL'],
        ['828', 'へ', '5', 'NULL'],
        ['829', 'に', '4', 'NULL'],
        ['830', 'お', '59', 'NULL'],
        ['831', 'そ', '28', 'NULL'],
        ['832', 'へ', '14', 'NULL'],
        ['833', 'ひ', '55', 'NULL'],
        ['834', 'り', '73', 'NULL'],
        ['835', 'に', '14', 'NULL'],
        ['836', 'さ', '46', 'NULL'],
        ['837', 'め', '24', 'NULL'],
        ['838', 'そ', '86', 'NULL'],
        ['839', 'か', '91', 'NULL'],
        ['840', 'の', '82', 'NULL'],
        ['841', 'お', '59', 'NULL'],
        ['842', 'む', '64', 'NULL'],
        ['843', 'ふ', '80', 'NULL'],
        ['844', 'り', '58', 'NULL'],
        ['845', 'ま', '64', 'NULL'],
        ['846', 'を', '61', 'NULL'],
        ['847', 'ら', '86', 'NULL'],
        ['848', 'も', '70', 'NULL'],
        ['849', 'し', '47', 'NULL'],
        ['850', 'さ', '30', 'NULL'],
        ['851', 'り', '8', 'NULL'],
        ['852', 'な', '19', 'NULL'],
        ['853', 'め', '89', 'NULL'],
        ['854', 'つ', '47', 'NULL'],
        ['855', 'と', '36', 'NULL'],
        ['856', 'ち', '92', 'NULL'],
        ['857', 'し', '3', 'NULL'],
        ['858', 'り', '71', 'NULL'],
        ['859', 'し', '56', 'NULL'],
        ['860', 'て', '91', 'NULL'],
        ['861', 'の', '77', 'NULL'],
        ['862', 'さ', '72', 'NULL'],
        ['863', 'と', '12', 'NULL'],
        ['864', 'け', '73', 'NULL'],
        ['865', 'も', '53', 'NULL'],
        ['866', 'つ', '14', 'NULL'],
        ['867', 'し', '6', 'NULL'],
        ['868', 'ち', '72', 'NULL'],
        ['869', 'め', '65', 'NULL'],
        ['870', 'ゆ', '75', 'NULL'],
        ['871', 'に', '35', 'NULL'],
        ['872', 'り', '66', 'NULL'],
        ['873', 'を', '51', 'NULL'],
        ['874', 'ち', '5', 'NULL'],
        ['875', 'ら', '55', 'NULL'],
        ['876', 'ぬ', '78', 'NULL'],
        ['877', 'え', '98', 'NULL'],
        ['878', 'か', '8', 'NULL'],
        ['879', 'め', '89', 'NULL'],
        ['880', 'と', '43', 'NULL'],
        ['881', 'こ', '25', 'NULL'],
        ['882', 'ひ', '1', 'NULL'],
        ['883', 'か', '44', 'NULL'],
        ['884', 'ん', '74', 'NULL'],
        ['885', 'ひ', '94', 'NULL'],
        ['886', 'わ', '47', 'NULL'],
        ['887', 'ん', '83', 'NULL'],
        ['888', 'ち', '6', 'NULL'],
        ['889', 'ら', '77', 'NULL'],
        ['890', 'や', '97', 'NULL'],
        ['891', 'け', '62', 'NULL'],
        ['892', 'つ', '58', 'NULL'],
        ['893', 'め', '78', 'NULL'],
        ['894', 'な', '94', 'NULL'],
        ['895', 'き', '32', 'NULL'],
        ['896', 'く', '48', 'NULL'],
        ['897', 'ま', '45', 'NULL'],
        ['898', 'た', '41', 'NULL'],
        ['899', 'と', '71', 'NULL'],
        ['900', 'め', '47', 'NULL'],
        ['901', 'れ', '67', 'NULL'],
        ['902', 'う', '72', 'NULL'],
        ['903', 'わ', '14', 'NULL'],
        ['904', 'ゆ', '53', 'NULL'],
        ['905', 'り', '47', 'NULL'],
        ['906', 'ほ', '29', 'NULL'],
        ['907', 'へ', '100', 'NULL'],
        ['908', 'か', '89', 'NULL'],
        ['909', 'り', '74', 'NULL'],
        ['910', 'に', '88', 'NULL'],
        ['911', 'か', '24', 'NULL'],
        ['912', 'か', '95', 'NULL'],
        ['913', 'ほ', '27', 'NULL'],
        ['914', 'や', '29', 'NULL'],
        ['915', 'ね', '19', 'NULL'],
        ['916', 'し', '28', 'NULL'],
        ['917', 'ん', '32', 'NULL'],
        ['918', 'も', '5', 'NULL'],
        ['919', 'き', '22', 'NULL'],
        ['920', 'ほ', '40', 'NULL'],
        ['921', 'せ', '65', 'NULL'],
        ['922', 'く', '4', 'NULL'],
        ['923', 'れ', '20', 'NULL'],
        ['924', 'も', '5', 'NULL'],
        ['925', 'い', '37', 'NULL'],
        ['926', 'ね', '72', 'NULL'],
        ['927', 'ひ', '55', 'NULL'],
        ['928', 'え', '25', 'NULL'],
        ['929', 'な', '71', 'NULL'],
        ['930', 'わ', '96', 'NULL'],
        ['931', 'ふ', '76', 'NULL'],
        ['932', 'も', '41', 'NULL'],
        ['933', 'る', '38', 'NULL'],
        ['934', 'た', '67', 'NULL'],
        ['935', 'む', '96', 'NULL'],
        ['936', 'せ', '19', 'NULL'],
        ['937', 'い', '12', 'NULL'],
        ['938', 'ら', '51', 'NULL'],
        ['939', 'く', '42', 'NULL'],
        ['940', 'き', '94', 'NULL'],
        ['941', 'く', '49', 'NULL'],
        ['942', 'め', '97', 'NULL'],
        ['943', 'す', '77', 'NULL'],
        ['944', 'よ', '74', 'NULL'],
        ['945', 'せ', '26', 'NULL'],
        ['946', 'く', '16', 'NULL'],
        ['947', 'は', '20', 'NULL'],
        ['948', 'し', '7', 'NULL'],
        ['949', 'め', '99', 'NULL'],
        ['950', 'め', '63', 'NULL'],
        ['951', 'り', '75', 'NULL'],
        ['952', 'さ', '91', 'NULL'],
        ['953', 'た', '24', 'NULL'],
        ['954', 'ん', '54', 'NULL'],
        ['955', 'て', '2', 'NULL'],
        ['956', 'く', '64', 'NULL'],
        ['957', 'へ', '20', 'NULL'],
        ['958', 'も', '37', 'NULL'],
        ['959', 'け', '49', 'NULL'],
        ['960', 'ゆ', '97', 'NULL'],
        ['961', 'い', '73', 'NULL'],
        ['962', 'と', '39', 'NULL'],
        ['963', 'は', '34', 'NULL'],
        ['964', 'の', '8', 'NULL'],
        ['965', 'つ', '95', 'NULL'],
        ['966', 'ち', '3', 'NULL'],
        ['967', 'へ', '48', 'NULL'],
        ['968', 'く', '74', 'NULL'],
        ['969', 'ろ', '99', 'NULL'],
        ['970', 'い', '75', 'NULL'],
        ['971', 'ひ', '63', 'NULL'],
        ['972', 'も', '22', 'NULL'],
        ['973', 'あ', '40', 'NULL'],
        ['974', 'せ', '25', 'NULL'],
        ['975', 'て', '49', 'NULL'],
        ['976', 'さ', '54', 'NULL'],
        ['977', 'も', '91', 'NULL'],
        ['978', 'も', '42', 'NULL'],
        ['979', 'し', '44', 'NULL'],
        ['980', 'を', '88', 'NULL'],
        ['981', 'わ', '44', 'NULL'],
        ['982', 'め', '87', 'NULL'],
        ['983', 'す', '7', 'NULL'],
        ['984', 'る', '73', 'NULL'],
        ['985', 'ん', '32', 'NULL'],
        ['986', 'ね', '52', 'NULL'],
        ['987', 'よ', '99', 'NULL'],
        ['988', 'ろ', '52', 'NULL'],
        ['989', 'れ', '90', 'NULL'],
        ['990', 'ら', '43', 'NULL'],
        ['991', 'え', '83', 'NULL'],
        ['992', 'る', '55', 'NULL'],
        ['993', 'や', '71', 'NULL'],
        ['994', 'つ', '35', 'NULL'],
        ['995', 'い', '56', 'NULL'],
        ['996', 'て', '48', 'NULL'],
        ['997', 'ゆ', '44', 'NULL'],
        ['998', 'か', '33', 'NULL'],
        ['999', 'た', '52', 'NULL'],
        ['1000', 'て', '6', 'NULL'],
        ['1001', 'な', '84', 'NULL'],
        ['1002', 'ち', '10', 'NULL'],
        ['1003', 'た', '44', 'NULL'],
        ['1004', 'せ', '24', 'NULL'],
        ['1005', 'た', '5', 'NULL'],
        ['1006', 'や', '39', 'NULL'],
        ['1007', 'の', '46', 'NULL'],
        ['1008', 'つ', '36', 'NULL'],
        ['1009', 'め', '69', 'NULL'],
        ['1010', 'も', '33', 'NULL']
    ];

    private $assoc_kana1010 = [
        ['id' => '1', 'kana' => 'ふ', 'num' => '39', 'empty' => 'NULL'],
        ['id' => '2', 'kana' => 'り', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '3', 'kana' => 'ふ', 'num' => '87', 'empty' => 'NULL'],
        ['id' => '4', 'kana' => 'む', 'num' => '78', 'empty' => 'NULL'],
        ['id' => '5', 'kana' => 'こ', 'num' => '45', 'empty' => 'NULL'],
        ['id' => '6', 'kana' => 'る', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '7', 'kana' => 'さ', 'num' => '2', 'empty' => 'NULL'],
        ['id' => '8', 'kana' => 'ろ', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '9', 'kana' => 'の', 'num' => '13', 'empty' => 'NULL'],
        ['id' => '10', 'kana' => 'の', 'num' => '100', 'empty' => 'NULL'],
        ['id' => '11', 'kana' => 'て', 'num' => '67', 'empty' => 'NULL'],
        ['id' => '12', 'kana' => 'え', 'num' => '37', 'empty' => 'NULL'],
        ['id' => '13', 'kana' => 'に', 'num' => '44', 'empty' => 'NULL'],
        ['id' => '14', 'kana' => 'つ', 'num' => '90', 'empty' => 'NULL'],
        ['id' => '15', 'kana' => 'た', 'num' => '87', 'empty' => 'NULL'],
        ['id' => '16', 'kana' => 'る', 'num' => '48', 'empty' => 'NULL'],
        ['id' => '17', 'kana' => 'の', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '18', 'kana' => 'き', 'num' => '44', 'empty' => 'NULL'],
        ['id' => '19', 'kana' => 'う', 'num' => '41', 'empty' => 'NULL'],
        ['id' => '20', 'kana' => 'わ', 'num' => '6', 'empty' => 'NULL'],
        ['id' => '21', 'kana' => 'に', 'num' => '4', 'empty' => 'NULL'],
        ['id' => '22', 'kana' => 'け', 'num' => '75', 'empty' => 'NULL'],
        ['id' => '23', 'kana' => 'ほ', 'num' => '60', 'empty' => 'NULL'],
        ['id' => '24', 'kana' => 'ひ', 'num' => '77', 'empty' => 'NULL'],
        ['id' => '25', 'kana' => 'け', 'num' => '56', 'empty' => 'NULL'],
        ['id' => '26', 'kana' => 'し', 'num' => '7', 'empty' => 'NULL'],
        ['id' => '27', 'kana' => 'か', 'num' => '91', 'empty' => 'NULL'],
        ['id' => '28', 'kana' => 'み', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '29', 'kana' => 'に', 'num' => '74', 'empty' => 'NULL'],
        ['id' => '30', 'kana' => 'へ', 'num' => '41', 'empty' => 'NULL'],
        ['id' => '31', 'kana' => 'つ', 'num' => '36', 'empty' => 'NULL'],
        ['id' => '32', 'kana' => 'し', 'num' => '48', 'empty' => 'NULL'],
        ['id' => '33', 'kana' => 'す', 'num' => '26', 'empty' => 'NULL'],
        ['id' => '34', 'kana' => 'と', 'num' => '59', 'empty' => 'NULL'],
        ['id' => '35', 'kana' => 'あ', 'num' => '43', 'empty' => 'NULL'],
        ['id' => '36', 'kana' => 'り', 'num' => '22', 'empty' => 'NULL'],
        ['id' => '37', 'kana' => 'の', 'num' => '95', 'empty' => 'NULL'],
        ['id' => '38', 'kana' => 'ね', 'num' => '50', 'empty' => 'NULL'],
        ['id' => '39', 'kana' => 'い', 'num' => '91', 'empty' => 'NULL'],
        ['id' => '40', 'kana' => 'せ', 'num' => '22', 'empty' => 'NULL'],
        ['id' => '41', 'kana' => 'し', 'num' => '16', 'empty' => 'NULL'],
        ['id' => '42', 'kana' => 'か', 'num' => '31', 'empty' => 'NULL'],
        ['id' => '43', 'kana' => 'ふ', 'num' => '98', 'empty' => 'NULL'],
        ['id' => '44', 'kana' => 'ゆ', 'num' => '34', 'empty' => 'NULL'],
        ['id' => '45', 'kana' => 'さ', 'num' => '89', 'empty' => 'NULL'],
        ['id' => '46', 'kana' => 'ゆ', 'num' => '58', 'empty' => 'NULL'],
        ['id' => '47', 'kana' => 'な', 'num' => '44', 'empty' => 'NULL'],
        ['id' => '48', 'kana' => 'け', 'num' => '86', 'empty' => 'NULL'],
        ['id' => '49', 'kana' => 'ん', 'num' => '9', 'empty' => 'NULL'],
        ['id' => '50', 'kana' => 'れ', 'num' => '96', 'empty' => 'NULL'],
        ['id' => '51', 'kana' => 'わ', 'num' => '32', 'empty' => 'NULL'],
        ['id' => '52', 'kana' => 'や', 'num' => '84', 'empty' => 'NULL'],
        ['id' => '53', 'kana' => 'ひ', 'num' => '68', 'empty' => 'NULL'],
        ['id' => '54', 'kana' => 'け', 'num' => '87', 'empty' => 'NULL'],
        ['id' => '55', 'kana' => 'せ', 'num' => '92', 'empty' => 'NULL'],
        ['id' => '56', 'kana' => 'ら', 'num' => '6', 'empty' => 'NULL'],
        ['id' => '57', 'kana' => 'ら', 'num' => '100', 'empty' => 'NULL'],
        ['id' => '58', 'kana' => 'を', 'num' => '46', 'empty' => 'NULL'],
        ['id' => '59', 'kana' => 'そ', 'num' => '47', 'empty' => 'NULL'],
        ['id' => '60', 'kana' => 'さ', 'num' => '86', 'empty' => 'NULL'],
        ['id' => '61', 'kana' => 'す', 'num' => '14', 'empty' => 'NULL'],
        ['id' => '62', 'kana' => 'を', 'num' => '36', 'empty' => 'NULL'],
        ['id' => '63', 'kana' => 'り', 'num' => '20', 'empty' => 'NULL'],
        ['id' => '64', 'kana' => 'の', 'num' => '56', 'empty' => 'NULL'],
        ['id' => '65', 'kana' => 'し', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '66', 'kana' => 'よ', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '67', 'kana' => 'れ', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '68', 'kana' => 'ん', 'num' => '22', 'empty' => 'NULL'],
        ['id' => '69', 'kana' => 'ろ', 'num' => '38', 'empty' => 'NULL'],
        ['id' => '70', 'kana' => 'う', 'num' => '66', 'empty' => 'NULL'],
        ['id' => '71', 'kana' => 'さ', 'num' => '65', 'empty' => 'NULL'],
        ['id' => '72', 'kana' => 'か', 'num' => '17', 'empty' => 'NULL'],
        ['id' => '73', 'kana' => 'ろ', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '74', 'kana' => 'う', 'num' => '73', 'empty' => 'NULL'],
        ['id' => '75', 'kana' => 'ま', 'num' => '28', 'empty' => 'NULL'],
        ['id' => '76', 'kana' => 'て', 'num' => '73', 'empty' => 'NULL'],
        ['id' => '77', 'kana' => 'ち', 'num' => '32', 'empty' => 'NULL'],
        ['id' => '78', 'kana' => 'と', 'num' => '13', 'empty' => 'NULL'],
        ['id' => '79', 'kana' => 'ま', 'num' => '14', 'empty' => 'NULL'],
        ['id' => '80', 'kana' => 'ま', 'num' => '54', 'empty' => 'NULL'],
        ['id' => '81', 'kana' => 'か', 'num' => '73', 'empty' => 'NULL'],
        ['id' => '82', 'kana' => 'へ', 'num' => '69', 'empty' => 'NULL'],
        ['id' => '83', 'kana' => 'ち', 'num' => '75', 'empty' => 'NULL'],
        ['id' => '84', 'kana' => 'す', 'num' => '100', 'empty' => 'NULL'],
        ['id' => '85', 'kana' => 'こ', 'num' => '22', 'empty' => 'NULL'],
        ['id' => '86', 'kana' => 'と', 'num' => '63', 'empty' => 'NULL'],
        ['id' => '87', 'kana' => 'す', 'num' => '16', 'empty' => 'NULL'],
        ['id' => '88', 'kana' => 'さ', 'num' => '84', 'empty' => 'NULL'],
        ['id' => '89', 'kana' => 'よ', 'num' => '61', 'empty' => 'NULL'],
        ['id' => '90', 'kana' => 'な', 'num' => '14', 'empty' => 'NULL'],
        ['id' => '91', 'kana' => 'わ', 'num' => '16', 'empty' => 'NULL'],
        ['id' => '92', 'kana' => 'き', 'num' => '89', 'empty' => 'NULL'],
        ['id' => '93', 'kana' => 'に', 'num' => '34', 'empty' => 'NULL'],
        ['id' => '94', 'kana' => 'ん', 'num' => '50', 'empty' => 'NULL'],
        ['id' => '95', 'kana' => 'て', 'num' => '59', 'empty' => 'NULL'],
        ['id' => '96', 'kana' => 'ま', 'num' => '98', 'empty' => 'NULL'],
        ['id' => '97', 'kana' => 'き', 'num' => '63', 'empty' => 'NULL'],
        ['id' => '98', 'kana' => 'た', 'num' => '25', 'empty' => 'NULL'],
        ['id' => '99', 'kana' => 'め', 'num' => '64', 'empty' => 'NULL'],
        ['id' => '100', 'kana' => 'そ', 'num' => '100', 'empty' => 'NULL'],
        ['id' => '101', 'kana' => 'に', 'num' => '24', 'empty' => 'NULL'],
        ['id' => '102', 'kana' => 'ふ', 'num' => '82', 'empty' => 'NULL'],
        ['id' => '103', 'kana' => 'も', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '104', 'kana' => 'な', 'num' => '74', 'empty' => 'NULL'],
        ['id' => '105', 'kana' => 'お', 'num' => '85', 'empty' => 'NULL'],
        ['id' => '106', 'kana' => 'ゆ', 'num' => '56', 'empty' => 'NULL'],
        ['id' => '107', 'kana' => 'て', 'num' => '86', 'empty' => 'NULL'],
        ['id' => '108', 'kana' => 'け', 'num' => '26', 'empty' => 'NULL'],
        ['id' => '109', 'kana' => 'な', 'num' => '51', 'empty' => 'NULL'],
        ['id' => '110', 'kana' => 'ゆ', 'num' => '20', 'empty' => 'NULL'],
        ['id' => '111', 'kana' => 'さ', 'num' => '7', 'empty' => 'NULL'],
        ['id' => '112', 'kana' => 'へ', 'num' => '50', 'empty' => 'NULL'],
        ['id' => '113', 'kana' => 'は', 'num' => '15', 'empty' => 'NULL'],
        ['id' => '114', 'kana' => 'ゆ', 'num' => '32', 'empty' => 'NULL'],
        ['id' => '115', 'kana' => 'え', 'num' => '42', 'empty' => 'NULL'],
        ['id' => '116', 'kana' => 'に', 'num' => '91', 'empty' => 'NULL'],
        ['id' => '117', 'kana' => 'ゆ', 'num' => '81', 'empty' => 'NULL'],
        ['id' => '118', 'kana' => 'す', 'num' => '85', 'empty' => 'NULL'],
        ['id' => '119', 'kana' => 'や', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '120', 'kana' => 'へ', 'num' => '28', 'empty' => 'NULL'],
        ['id' => '121', 'kana' => 'り', 'num' => '81', 'empty' => 'NULL'],
        ['id' => '122', 'kana' => 'ち', 'num' => '84', 'empty' => 'NULL'],
        ['id' => '123', 'kana' => 'ん', 'num' => '21', 'empty' => 'NULL'],
        ['id' => '124', 'kana' => 'た', 'num' => '66', 'empty' => 'NULL'],
        ['id' => '125', 'kana' => 'ん', 'num' => '49', 'empty' => 'NULL'],
        ['id' => '126', 'kana' => 'め', 'num' => '99', 'empty' => 'NULL'],
        ['id' => '127', 'kana' => 'る', 'num' => '56', 'empty' => 'NULL'],
        ['id' => '128', 'kana' => 'れ', 'num' => '50', 'empty' => 'NULL'],
        ['id' => '129', 'kana' => 'ね', 'num' => '21', 'empty' => 'NULL'],
        ['id' => '130', 'kana' => 'め', 'num' => '89', 'empty' => 'NULL'],
        ['id' => '131', 'kana' => 'む', 'num' => '15', 'empty' => 'NULL'],
        ['id' => '132', 'kana' => 'も', 'num' => '55', 'empty' => 'NULL'],
        ['id' => '133', 'kana' => 'え', 'num' => '57', 'empty' => 'NULL'],
        ['id' => '134', 'kana' => 'は', 'num' => '63', 'empty' => 'NULL'],
        ['id' => '135', 'kana' => 'ふ', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '136', 'kana' => 'ゆ', 'num' => '30', 'empty' => 'NULL'],
        ['id' => '137', 'kana' => 'か', 'num' => '17', 'empty' => 'NULL'],
        ['id' => '138', 'kana' => 'し', 'num' => '79', 'empty' => 'NULL'],
        ['id' => '139', 'kana' => 'え', 'num' => '55', 'empty' => 'NULL'],
        ['id' => '140', 'kana' => 'り', 'num' => '79', 'empty' => 'NULL'],
        ['id' => '141', 'kana' => 'き', 'num' => '30', 'empty' => 'NULL'],
        ['id' => '142', 'kana' => 'わ', 'num' => '100', 'empty' => 'NULL'],
        ['id' => '143', 'kana' => 'く', 'num' => '98', 'empty' => 'NULL'],
        ['id' => '144', 'kana' => 'け', 'num' => '38', 'empty' => 'NULL'],
        ['id' => '145', 'kana' => 'け', 'num' => '9', 'empty' => 'NULL'],
        ['id' => '146', 'kana' => 'い', 'num' => '65', 'empty' => 'NULL'],
        ['id' => '147', 'kana' => 'ゆ', 'num' => '15', 'empty' => 'NULL'],
        ['id' => '148', 'kana' => 'え', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '149', 'kana' => 'し', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '150', 'kana' => 'う', 'num' => '9', 'empty' => 'NULL'],
        ['id' => '151', 'kana' => 'め', 'num' => '67', 'empty' => 'NULL'],
        ['id' => '152', 'kana' => 'た', 'num' => '98', 'empty' => 'NULL'],
        ['id' => '153', 'kana' => 'と', 'num' => '18', 'empty' => 'NULL'],
        ['id' => '154', 'kana' => 'は', 'num' => '99', 'empty' => 'NULL'],
        ['id' => '155', 'kana' => 'む', 'num' => '91', 'empty' => 'NULL'],
        ['id' => '156', 'kana' => 'さ', 'num' => '34', 'empty' => 'NULL'],
        ['id' => '157', 'kana' => 'ひ', 'num' => '15', 'empty' => 'NULL'],
        ['id' => '158', 'kana' => 'か', 'num' => '28', 'empty' => 'NULL'],
        ['id' => '159', 'kana' => 'ら', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '160', 'kana' => 'ち', 'num' => '53', 'empty' => 'NULL'],
        ['id' => '161', 'kana' => 'け', 'num' => '66', 'empty' => 'NULL'],
        ['id' => '162', 'kana' => 'ひ', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '163', 'kana' => 'て', 'num' => '85', 'empty' => 'NULL'],
        ['id' => '164', 'kana' => 'ほ', 'num' => '21', 'empty' => 'NULL'],
        ['id' => '165', 'kana' => 'め', 'num' => '7', 'empty' => 'NULL'],
        ['id' => '166', 'kana' => 'や', 'num' => '74', 'empty' => 'NULL'],
        ['id' => '167', 'kana' => 'め', 'num' => '62', 'empty' => 'NULL'],
        ['id' => '168', 'kana' => 'み', 'num' => '86', 'empty' => 'NULL'],
        ['id' => '169', 'kana' => 'に', 'num' => '77', 'empty' => 'NULL'],
        ['id' => '170', 'kana' => 'う', 'num' => '69', 'empty' => 'NULL'],
        ['id' => '171', 'kana' => 'つ', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '172', 'kana' => 'ら', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '173', 'kana' => 'ち', 'num' => '91', 'empty' => 'NULL'],
        ['id' => '174', 'kana' => 'よ', 'num' => '8', 'empty' => 'NULL'],
        ['id' => '175', 'kana' => 'め', 'num' => '24', 'empty' => 'NULL'],
        ['id' => '176', 'kana' => 'け', 'num' => '65', 'empty' => 'NULL'],
        ['id' => '177', 'kana' => 'ら', 'num' => '67', 'empty' => 'NULL'],
        ['id' => '178', 'kana' => 'き', 'num' => '95', 'empty' => 'NULL'],
        ['id' => '179', 'kana' => 'く', 'num' => '57', 'empty' => 'NULL'],
        ['id' => '180', 'kana' => 'ろ', 'num' => '9', 'empty' => 'NULL'],
        ['id' => '181', 'kana' => 'ゆ', 'num' => '42', 'empty' => 'NULL'],
        ['id' => '182', 'kana' => 'こ', 'num' => '81', 'empty' => 'NULL'],
        ['id' => '183', 'kana' => 'る', 'num' => '24', 'empty' => 'NULL'],
        ['id' => '184', 'kana' => 'あ', 'num' => '41', 'empty' => 'NULL'],
        ['id' => '185', 'kana' => 'し', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '186', 'kana' => 'る', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '187', 'kana' => 'む', 'num' => '15', 'empty' => 'NULL'],
        ['id' => '188', 'kana' => 'さ', 'num' => '63', 'empty' => 'NULL'],
        ['id' => '189', 'kana' => 'れ', 'num' => '78', 'empty' => 'NULL'],
        ['id' => '190', 'kana' => 'ふ', 'num' => '91', 'empty' => 'NULL'],
        ['id' => '191', 'kana' => 'ね', 'num' => '91', 'empty' => 'NULL'],
        ['id' => '192', 'kana' => 'え', 'num' => '40', 'empty' => 'NULL'],
        ['id' => '193', 'kana' => 'し', 'num' => '88', 'empty' => 'NULL'],
        ['id' => '194', 'kana' => 'に', 'num' => '57', 'empty' => 'NULL'],
        ['id' => '195', 'kana' => 'と', 'num' => '48', 'empty' => 'NULL'],
        ['id' => '196', 'kana' => 'ふ', 'num' => '2', 'empty' => 'NULL'],
        ['id' => '197', 'kana' => 'た', 'num' => '85', 'empty' => 'NULL'],
        ['id' => '198', 'kana' => 'と', 'num' => '12', 'empty' => 'NULL'],
        ['id' => '199', 'kana' => 'の', 'num' => '78', 'empty' => 'NULL'],
        ['id' => '200', 'kana' => 'て', 'num' => '82', 'empty' => 'NULL'],
        ['id' => '201', 'kana' => 'ん', 'num' => '71', 'empty' => 'NULL'],
        ['id' => '202', 'kana' => 'ぬ', 'num' => '32', 'empty' => 'NULL'],
        ['id' => '203', 'kana' => 'へ', 'num' => '14', 'empty' => 'NULL'],
        ['id' => '204', 'kana' => 'り', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '205', 'kana' => 'ふ', 'num' => '70', 'empty' => 'NULL'],
        ['id' => '206', 'kana' => 'て', 'num' => '62', 'empty' => 'NULL'],
        ['id' => '207', 'kana' => 'ふ', 'num' => '37', 'empty' => 'NULL'],
        ['id' => '208', 'kana' => 'き', 'num' => '80', 'empty' => 'NULL'],
        ['id' => '209', 'kana' => 'み', 'num' => '14', 'empty' => 'NULL'],
        ['id' => '210', 'kana' => 'よ', 'num' => '26', 'empty' => 'NULL'],
        ['id' => '211', 'kana' => 'つ', 'num' => '18', 'empty' => 'NULL'],
        ['id' => '212', 'kana' => 'と', 'num' => '49', 'empty' => 'NULL'],
        ['id' => '213', 'kana' => 'し', 'num' => '74', 'empty' => 'NULL'],
        ['id' => '214', 'kana' => 'な', 'num' => '68', 'empty' => 'NULL'],
        ['id' => '215', 'kana' => 'あ', 'num' => '20', 'empty' => 'NULL'],
        ['id' => '216', 'kana' => 'わ', 'num' => '82', 'empty' => 'NULL'],
        ['id' => '217', 'kana' => 'つ', 'num' => '10', 'empty' => 'NULL'],
        ['id' => '218', 'kana' => 'け', 'num' => '12', 'empty' => 'NULL'],
        ['id' => '219', 'kana' => 'ん', 'num' => '85', 'empty' => 'NULL'],
        ['id' => '220', 'kana' => 'ろ', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '221', 'kana' => 'み', 'num' => '30', 'empty' => 'NULL'],
        ['id' => '222', 'kana' => 'さ', 'num' => '40', 'empty' => 'NULL'],
        ['id' => '223', 'kana' => 'の', 'num' => '78', 'empty' => 'NULL'],
        ['id' => '224', 'kana' => 'へ', 'num' => '40', 'empty' => 'NULL'],
        ['id' => '225', 'kana' => 'よ', 'num' => '88', 'empty' => 'NULL'],
        ['id' => '226', 'kana' => 'ほ', 'num' => '36', 'empty' => 'NULL'],
        ['id' => '227', 'kana' => 'つ', 'num' => '39', 'empty' => 'NULL'],
        ['id' => '228', 'kana' => 'し', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '229', 'kana' => 'の', 'num' => '97', 'empty' => 'NULL'],
        ['id' => '230', 'kana' => 'う', 'num' => '81', 'empty' => 'NULL'],
        ['id' => '231', 'kana' => 'た', 'num' => '97', 'empty' => 'NULL'],
        ['id' => '232', 'kana' => 'お', 'num' => '4', 'empty' => 'NULL'],
        ['id' => '233', 'kana' => 'こ', 'num' => '53', 'empty' => 'NULL'],
        ['id' => '234', 'kana' => 'か', 'num' => '98', 'empty' => 'NULL'],
        ['id' => '235', 'kana' => 'む', 'num' => '70', 'empty' => 'NULL'],
        ['id' => '236', 'kana' => 'ね', 'num' => '8', 'empty' => 'NULL'],
        ['id' => '237', 'kana' => 'た', 'num' => '56', 'empty' => 'NULL'],
        ['id' => '238', 'kana' => 'か', 'num' => '28', 'empty' => 'NULL'],
        ['id' => '239', 'kana' => 'も', 'num' => '74', 'empty' => 'NULL'],
        ['id' => '240', 'kana' => 'す', 'num' => '52', 'empty' => 'NULL'],
        ['id' => '241', 'kana' => 'を', 'num' => '79', 'empty' => 'NULL'],
        ['id' => '242', 'kana' => 'か', 'num' => '89', 'empty' => 'NULL'],
        ['id' => '243', 'kana' => 'あ', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '244', 'kana' => 'せ', 'num' => '3', 'empty' => 'NULL'],
        ['id' => '245', 'kana' => 'き', 'num' => '18', 'empty' => 'NULL'],
        ['id' => '246', 'kana' => 'に', 'num' => '100', 'empty' => 'NULL'],
        ['id' => '247', 'kana' => 'へ', 'num' => '91', 'empty' => 'NULL'],
        ['id' => '248', 'kana' => 'む', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '249', 'kana' => 'ま', 'num' => '43', 'empty' => 'NULL'],
        ['id' => '250', 'kana' => 'は', 'num' => '9', 'empty' => 'NULL'],
        ['id' => '251', 'kana' => 'は', 'num' => '16', 'empty' => 'NULL'],
        ['id' => '252', 'kana' => 'ら', 'num' => '60', 'empty' => 'NULL'],
        ['id' => '253', 'kana' => 'め', 'num' => '44', 'empty' => 'NULL'],
        ['id' => '254', 'kana' => 'た', 'num' => '55', 'empty' => 'NULL'],
        ['id' => '255', 'kana' => 'も', 'num' => '69', 'empty' => 'NULL'],
        ['id' => '256', 'kana' => 'ほ', 'num' => '61', 'empty' => 'NULL'],
        ['id' => '257', 'kana' => 'ひ', 'num' => '10', 'empty' => 'NULL'],
        ['id' => '258', 'kana' => 'ふ', 'num' => '78', 'empty' => 'NULL'],
        ['id' => '259', 'kana' => 'る', 'num' => '39', 'empty' => 'NULL'],
        ['id' => '260', 'kana' => 'き', 'num' => '77', 'empty' => 'NULL'],
        ['id' => '261', 'kana' => 'こ', 'num' => '78', 'empty' => 'NULL'],
        ['id' => '262', 'kana' => 'い', 'num' => '62', 'empty' => 'NULL'],
        ['id' => '263', 'kana' => 'み', 'num' => '92', 'empty' => 'NULL'],
        ['id' => '264', 'kana' => 'み', 'num' => '35', 'empty' => 'NULL'],
        ['id' => '265', 'kana' => 'け', 'num' => '25', 'empty' => 'NULL'],
        ['id' => '266', 'kana' => 'ゆ', 'num' => '71', 'empty' => 'NULL'],
        ['id' => '267', 'kana' => 'ひ', 'num' => '68', 'empty' => 'NULL'],
        ['id' => '268', 'kana' => 'へ', 'num' => '39', 'empty' => 'NULL'],
        ['id' => '269', 'kana' => 'ち', 'num' => '81', 'empty' => 'NULL'],
        ['id' => '270', 'kana' => 'へ', 'num' => '41', 'empty' => 'NULL'],
        ['id' => '271', 'kana' => 'は', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '272', 'kana' => 'な', 'num' => '39', 'empty' => 'NULL'],
        ['id' => '273', 'kana' => 'つ', 'num' => '68', 'empty' => 'NULL'],
        ['id' => '274', 'kana' => 'く', 'num' => '100', 'empty' => 'NULL'],
        ['id' => '275', 'kana' => 'ほ', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '276', 'kana' => 'れ', 'num' => '44', 'empty' => 'NULL'],
        ['id' => '277', 'kana' => 'の', 'num' => '11', 'empty' => 'NULL'],
        ['id' => '278', 'kana' => 'ふ', 'num' => '31', 'empty' => 'NULL'],
        ['id' => '279', 'kana' => 'む', 'num' => '54', 'empty' => 'NULL'],
        ['id' => '280', 'kana' => 'か', 'num' => '90', 'empty' => 'NULL'],
        ['id' => '281', 'kana' => 'よ', 'num' => '55', 'empty' => 'NULL'],
        ['id' => '282', 'kana' => 'ん', 'num' => '49', 'empty' => 'NULL'],
        ['id' => '283', 'kana' => 'せ', 'num' => '81', 'empty' => 'NULL'],
        ['id' => '284', 'kana' => 'こ', 'num' => '15', 'empty' => 'NULL'],
        ['id' => '285', 'kana' => 'よ', 'num' => '63', 'empty' => 'NULL'],
        ['id' => '286', 'kana' => 'わ', 'num' => '49', 'empty' => 'NULL'],
        ['id' => '287', 'kana' => 'ん', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '288', 'kana' => 'は', 'num' => '47', 'empty' => 'NULL'],
        ['id' => '289', 'kana' => 'れ', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '290', 'kana' => 'ふ', 'num' => '65', 'empty' => 'NULL'],
        ['id' => '291', 'kana' => 'け', 'num' => '24', 'empty' => 'NULL'],
        ['id' => '292', 'kana' => 'あ', 'num' => '6', 'empty' => 'NULL'],
        ['id' => '293', 'kana' => 'え', 'num' => '36', 'empty' => 'NULL'],
        ['id' => '294', 'kana' => 'た', 'num' => '13', 'empty' => 'NULL'],
        ['id' => '295', 'kana' => 'り', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '296', 'kana' => 'き', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '297', 'kana' => 'な', 'num' => '16', 'empty' => 'NULL'],
        ['id' => '298', 'kana' => 'ま', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '299', 'kana' => 'お', 'num' => '21', 'empty' => 'NULL'],
        ['id' => '300', 'kana' => 'ね', 'num' => '22', 'empty' => 'NULL'],
        ['id' => '301', 'kana' => 'と', 'num' => '30', 'empty' => 'NULL'],
        ['id' => '302', 'kana' => 'ら', 'num' => '75', 'empty' => 'NULL'],
        ['id' => '303', 'kana' => 'お', 'num' => '35', 'empty' => 'NULL'],
        ['id' => '304', 'kana' => 'ふ', 'num' => '11', 'empty' => 'NULL'],
        ['id' => '305', 'kana' => 'ら', 'num' => '62', 'empty' => 'NULL'],
        ['id' => '306', 'kana' => 'ね', 'num' => '1', 'empty' => 'NULL'],
        ['id' => '307', 'kana' => 'は', 'num' => '46', 'empty' => 'NULL'],
        ['id' => '308', 'kana' => 'も', 'num' => '83', 'empty' => 'NULL'],
        ['id' => '309', 'kana' => 'ん', 'num' => '61', 'empty' => 'NULL'],
        ['id' => '310', 'kana' => 'ゆ', 'num' => '30', 'empty' => 'NULL'],
        ['id' => '311', 'kana' => 'ち', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '312', 'kana' => 'ち', 'num' => '31', 'empty' => 'NULL'],
        ['id' => '313', 'kana' => 'か', 'num' => '71', 'empty' => 'NULL'],
        ['id' => '314', 'kana' => 'か', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '315', 'kana' => 'と', 'num' => '38', 'empty' => 'NULL'],
        ['id' => '316', 'kana' => 'え', 'num' => '16', 'empty' => 'NULL'],
        ['id' => '317', 'kana' => 'ゆ', 'num' => '2', 'empty' => 'NULL'],
        ['id' => '318', 'kana' => 'い', 'num' => '2', 'empty' => 'NULL'],
        ['id' => '319', 'kana' => 'さ', 'num' => '79', 'empty' => 'NULL'],
        ['id' => '320', 'kana' => 'ち', 'num' => '32', 'empty' => 'NULL'],
        ['id' => '321', 'kana' => 'あ', 'num' => '43', 'empty' => 'NULL'],
        ['id' => '322', 'kana' => 'ら', 'num' => '39', 'empty' => 'NULL'],
        ['id' => '323', 'kana' => 'う', 'num' => '68', 'empty' => 'NULL'],
        ['id' => '324', 'kana' => 'を', 'num' => '37', 'empty' => 'NULL'],
        ['id' => '325', 'kana' => 'あ', 'num' => '89', 'empty' => 'NULL'],
        ['id' => '326', 'kana' => 'せ', 'num' => '68', 'empty' => 'NULL'],
        ['id' => '327', 'kana' => 'え', 'num' => '23', 'empty' => 'NULL'],
        ['id' => '328', 'kana' => 'に', 'num' => '51', 'empty' => 'NULL'],
        ['id' => '329', 'kana' => 'ち', 'num' => '92', 'empty' => 'NULL'],
        ['id' => '330', 'kana' => 'あ', 'num' => '69', 'empty' => 'NULL'],
        ['id' => '331', 'kana' => 'ね', 'num' => '45', 'empty' => 'NULL'],
        ['id' => '332', 'kana' => 'ふ', 'num' => '66', 'empty' => 'NULL'],
        ['id' => '333', 'kana' => 'せ', 'num' => '89', 'empty' => 'NULL'],
        ['id' => '334', 'kana' => 'ひ', 'num' => '42', 'empty' => 'NULL'],
        ['id' => '335', 'kana' => 'ち', 'num' => '50', 'empty' => 'NULL'],
        ['id' => '336', 'kana' => 'も', 'num' => '4', 'empty' => 'NULL'],
        ['id' => '337', 'kana' => 'を', 'num' => '54', 'empty' => 'NULL'],
        ['id' => '338', 'kana' => 'し', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '339', 'kana' => 'れ', 'num' => '73', 'empty' => 'NULL'],
        ['id' => '340', 'kana' => 'す', 'num' => '51', 'empty' => 'NULL'],
        ['id' => '341', 'kana' => 'く', 'num' => '68', 'empty' => 'NULL'],
        ['id' => '342', 'kana' => 'み', 'num' => '75', 'empty' => 'NULL'],
        ['id' => '343', 'kana' => 'わ', 'num' => '60', 'empty' => 'NULL'],
        ['id' => '344', 'kana' => 'す', 'num' => '86', 'empty' => 'NULL'],
        ['id' => '345', 'kana' => 'る', 'num' => '13', 'empty' => 'NULL'],
        ['id' => '346', 'kana' => 'も', 'num' => '70', 'empty' => 'NULL'],
        ['id' => '347', 'kana' => 'か', 'num' => '52', 'empty' => 'NULL'],
        ['id' => '348', 'kana' => 'む', 'num' => '31', 'empty' => 'NULL'],
        ['id' => '349', 'kana' => 'り', 'num' => '49', 'empty' => 'NULL'],
        ['id' => '350', 'kana' => 'よ', 'num' => '31', 'empty' => 'NULL'],
        ['id' => '351', 'kana' => 'を', 'num' => '46', 'empty' => 'NULL'],
        ['id' => '352', 'kana' => 'は', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '353', 'kana' => 'あ', 'num' => '27', 'empty' => 'NULL'],
        ['id' => '354', 'kana' => 'れ', 'num' => '20', 'empty' => 'NULL'],
        ['id' => '355', 'kana' => 'む', 'num' => '58', 'empty' => 'NULL'],
        ['id' => '356', 'kana' => 'む', 'num' => '73', 'empty' => 'NULL'],
        ['id' => '357', 'kana' => 'ん', 'num' => '1', 'empty' => 'NULL'],
        ['id' => '358', 'kana' => 'け', 'num' => '70', 'empty' => 'NULL'],
        ['id' => '359', 'kana' => 'ひ', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '360', 'kana' => 'お', 'num' => '97', 'empty' => 'NULL'],
        ['id' => '361', 'kana' => 'を', 'num' => '55', 'empty' => 'NULL'],
        ['id' => '362', 'kana' => 'か', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '363', 'kana' => 'ひ', 'num' => '100', 'empty' => 'NULL'],
        ['id' => '364', 'kana' => 'た', 'num' => '10', 'empty' => 'NULL'],
        ['id' => '365', 'kana' => 'ら', 'num' => '93', 'empty' => 'NULL'],
        ['id' => '366', 'kana' => 'せ', 'num' => '96', 'empty' => 'NULL'],
        ['id' => '367', 'kana' => 'ふ', 'num' => '92', 'empty' => 'NULL'],
        ['id' => '368', 'kana' => 'や', 'num' => '95', 'empty' => 'NULL'],
        ['id' => '369', 'kana' => 'に', 'num' => '51', 'empty' => 'NULL'],
        ['id' => '370', 'kana' => 'け', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '371', 'kana' => 'わ', 'num' => '7', 'empty' => 'NULL'],
        ['id' => '372', 'kana' => 'り', 'num' => '44', 'empty' => 'NULL'],
        ['id' => '373', 'kana' => 'て', 'num' => '51', 'empty' => 'NULL'],
        ['id' => '374', 'kana' => 'る', 'num' => '44', 'empty' => 'NULL'],
        ['id' => '375', 'kana' => 'て', 'num' => '77', 'empty' => 'NULL'],
        ['id' => '376', 'kana' => 'み', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '377', 'kana' => 'お', 'num' => '90', 'empty' => 'NULL'],
        ['id' => '378', 'kana' => 'の', 'num' => '62', 'empty' => 'NULL'],
        ['id' => '379', 'kana' => 'わ', 'num' => '32', 'empty' => 'NULL'],
        ['id' => '380', 'kana' => 'な', 'num' => '38', 'empty' => 'NULL'],
        ['id' => '381', 'kana' => 'を', 'num' => '56', 'empty' => 'NULL'],
        ['id' => '382', 'kana' => 'す', 'num' => '22', 'empty' => 'NULL'],
        ['id' => '383', 'kana' => 'た', 'num' => '26', 'empty' => 'NULL'],
        ['id' => '384', 'kana' => 'く', 'num' => '24', 'empty' => 'NULL'],
        ['id' => '385', 'kana' => 'い', 'num' => '47', 'empty' => 'NULL'],
        ['id' => '386', 'kana' => 'に', 'num' => '70', 'empty' => 'NULL'],
        ['id' => '387', 'kana' => 'よ', 'num' => '43', 'empty' => 'NULL'],
        ['id' => '388', 'kana' => 'ふ', 'num' => '51', 'empty' => 'NULL'],
        ['id' => '389', 'kana' => 'こ', 'num' => '96', 'empty' => 'NULL'],
        ['id' => '390', 'kana' => 'る', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '391', 'kana' => 'も', 'num' => '97', 'empty' => 'NULL'],
        ['id' => '392', 'kana' => 'ひ', 'num' => '10', 'empty' => 'NULL'],
        ['id' => '393', 'kana' => 'は', 'num' => '59', 'empty' => 'NULL'],
        ['id' => '394', 'kana' => 'ろ', 'num' => '12', 'empty' => 'NULL'],
        ['id' => '395', 'kana' => 'あ', 'num' => '47', 'empty' => 'NULL'],
        ['id' => '396', 'kana' => 'け', 'num' => '1', 'empty' => 'NULL'],
        ['id' => '397', 'kana' => 'き', 'num' => '53', 'empty' => 'NULL'],
        ['id' => '398', 'kana' => 'は', 'num' => '95', 'empty' => 'NULL'],
        ['id' => '399', 'kana' => 'む', 'num' => '66', 'empty' => 'NULL'],
        ['id' => '400', 'kana' => 'は', 'num' => '78', 'empty' => 'NULL'],
        ['id' => '401', 'kana' => 'ゆ', 'num' => '18', 'empty' => 'NULL'],
        ['id' => '402', 'kana' => 'よ', 'num' => '47', 'empty' => 'NULL'],
        ['id' => '403', 'kana' => 'し', 'num' => '23', 'empty' => 'NULL'],
        ['id' => '404', 'kana' => 'て', 'num' => '58', 'empty' => 'NULL'],
        ['id' => '405', 'kana' => 'も', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '406', 'kana' => 'こ', 'num' => '27', 'empty' => 'NULL'],
        ['id' => '407', 'kana' => 'わ', 'num' => '6', 'empty' => 'NULL'],
        ['id' => '408', 'kana' => 'こ', 'num' => '13', 'empty' => 'NULL'],
        ['id' => '409', 'kana' => 'お', 'num' => '36', 'empty' => 'NULL'],
        ['id' => '410', 'kana' => 'え', 'num' => '67', 'empty' => 'NULL'],
        ['id' => '411', 'kana' => 'る', 'num' => '41', 'empty' => 'NULL'],
        ['id' => '412', 'kana' => 'い', 'num' => '98', 'empty' => 'NULL'],
        ['id' => '413', 'kana' => 'た', 'num' => '79', 'empty' => 'NULL'],
        ['id' => '414', 'kana' => 'そ', 'num' => '25', 'empty' => 'NULL'],
        ['id' => '415', 'kana' => 'ゆ', 'num' => '63', 'empty' => 'NULL'],
        ['id' => '416', 'kana' => 'へ', 'num' => '38', 'empty' => 'NULL'],
        ['id' => '417', 'kana' => 'か', 'num' => '56', 'empty' => 'NULL'],
        ['id' => '418', 'kana' => 'き', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '419', 'kana' => 'な', 'num' => '79', 'empty' => 'NULL'],
        ['id' => '420', 'kana' => 'を', 'num' => '52', 'empty' => 'NULL'],
        ['id' => '421', 'kana' => 'ぬ', 'num' => '55', 'empty' => 'NULL'],
        ['id' => '422', 'kana' => 'き', 'num' => '65', 'empty' => 'NULL'],
        ['id' => '423', 'kana' => 'に', 'num' => '75', 'empty' => 'NULL'],
        ['id' => '424', 'kana' => 'ま', 'num' => '36', 'empty' => 'NULL'],
        ['id' => '425', 'kana' => 'な', 'num' => '89', 'empty' => 'NULL'],
        ['id' => '426', 'kana' => 'う', 'num' => '96', 'empty' => 'NULL'],
        ['id' => '427', 'kana' => 'す', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '428', 'kana' => 'め', 'num' => '15', 'empty' => 'NULL'],
        ['id' => '429', 'kana' => 'あ', 'num' => '57', 'empty' => 'NULL'],
        ['id' => '430', 'kana' => 'れ', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '431', 'kana' => 'ぬ', 'num' => '36', 'empty' => 'NULL'],
        ['id' => '432', 'kana' => 'え', 'num' => '4', 'empty' => 'NULL'],
        ['id' => '433', 'kana' => 'ひ', 'num' => '46', 'empty' => 'NULL'],
        ['id' => '434', 'kana' => 'け', 'num' => '69', 'empty' => 'NULL'],
        ['id' => '435', 'kana' => 'た', 'num' => '31', 'empty' => 'NULL'],
        ['id' => '436', 'kana' => 'き', 'num' => '65', 'empty' => 'NULL'],
        ['id' => '437', 'kana' => 'れ', 'num' => '51', 'empty' => 'NULL'],
        ['id' => '438', 'kana' => 'わ', 'num' => '100', 'empty' => 'NULL'],
        ['id' => '439', 'kana' => 'い', 'num' => '17', 'empty' => 'NULL'],
        ['id' => '440', 'kana' => 'つ', 'num' => '3', 'empty' => 'NULL'],
        ['id' => '441', 'kana' => 'ひ', 'num' => '26', 'empty' => 'NULL'],
        ['id' => '442', 'kana' => 'ひ', 'num' => '15', 'empty' => 'NULL'],
        ['id' => '443', 'kana' => 'お', 'num' => '76', 'empty' => 'NULL'],
        ['id' => '444', 'kana' => 'ま', 'num' => '56', 'empty' => 'NULL'],
        ['id' => '445', 'kana' => 'ち', 'num' => '23', 'empty' => 'NULL'],
        ['id' => '446', 'kana' => 'せ', 'num' => '89', 'empty' => 'NULL'],
        ['id' => '447', 'kana' => 'も', 'num' => '59', 'empty' => 'NULL'],
        ['id' => '448', 'kana' => 'か', 'num' => '20', 'empty' => 'NULL'],
        ['id' => '449', 'kana' => 'は', 'num' => '21', 'empty' => 'NULL'],
        ['id' => '450', 'kana' => 'え', 'num' => '44', 'empty' => 'NULL'],
        ['id' => '451', 'kana' => 'う', 'num' => '100', 'empty' => 'NULL'],
        ['id' => '452', 'kana' => 'と', 'num' => '31', 'empty' => 'NULL'],
        ['id' => '453', 'kana' => 'ね', 'num' => '91', 'empty' => 'NULL'],
        ['id' => '454', 'kana' => 'ふ', 'num' => '17', 'empty' => 'NULL'],
        ['id' => '455', 'kana' => 'せ', 'num' => '83', 'empty' => 'NULL'],
        ['id' => '456', 'kana' => 'こ', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '457', 'kana' => 'わ', 'num' => '35', 'empty' => 'NULL'],
        ['id' => '458', 'kana' => 'の', 'num' => '42', 'empty' => 'NULL'],
        ['id' => '459', 'kana' => 'ひ', 'num' => '31', 'empty' => 'NULL'],
        ['id' => '460', 'kana' => 'や', 'num' => '41', 'empty' => 'NULL'],
        ['id' => '461', 'kana' => 'は', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '462', 'kana' => 'も', 'num' => '87', 'empty' => 'NULL'],
        ['id' => '463', 'kana' => 'こ', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '464', 'kana' => 'つ', 'num' => '28', 'empty' => 'NULL'],
        ['id' => '465', 'kana' => 'み', 'num' => '45', 'empty' => 'NULL'],
        ['id' => '466', 'kana' => 'ひ', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '467', 'kana' => 'ち', 'num' => '43', 'empty' => 'NULL'],
        ['id' => '468', 'kana' => 'け', 'num' => '8', 'empty' => 'NULL'],
        ['id' => '469', 'kana' => 'は', 'num' => '42', 'empty' => 'NULL'],
        ['id' => '470', 'kana' => 'を', 'num' => '98', 'empty' => 'NULL'],
        ['id' => '471', 'kana' => 'つ', 'num' => '66', 'empty' => 'NULL'],
        ['id' => '472', 'kana' => 'に', 'num' => '22', 'empty' => 'NULL'],
        ['id' => '473', 'kana' => 'ほ', 'num' => '38', 'empty' => 'NULL'],
        ['id' => '474', 'kana' => 'の', 'num' => '50', 'empty' => 'NULL'],
        ['id' => '475', 'kana' => 'り', 'num' => '43', 'empty' => 'NULL'],
        ['id' => '476', 'kana' => 'せ', 'num' => '99', 'empty' => 'NULL'],
        ['id' => '477', 'kana' => 'よ', 'num' => '4', 'empty' => 'NULL'],
        ['id' => '478', 'kana' => 'や', 'num' => '22', 'empty' => 'NULL'],
        ['id' => '479', 'kana' => 'る', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '480', 'kana' => 'れ', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '481', 'kana' => 'う', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '482', 'kana' => 'り', 'num' => '38', 'empty' => 'NULL'],
        ['id' => '483', 'kana' => 'な', 'num' => '71', 'empty' => 'NULL'],
        ['id' => '484', 'kana' => 'そ', 'num' => '100', 'empty' => 'NULL'],
        ['id' => '485', 'kana' => 'さ', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '486', 'kana' => 'お', 'num' => '79', 'empty' => 'NULL'],
        ['id' => '487', 'kana' => 'は', 'num' => '58', 'empty' => 'NULL'],
        ['id' => '488', 'kana' => 'ね', 'num' => '48', 'empty' => 'NULL'],
        ['id' => '489', 'kana' => 'ぬ', 'num' => '16', 'empty' => 'NULL'],
        ['id' => '490', 'kana' => 'る', 'num' => '7', 'empty' => 'NULL'],
        ['id' => '491', 'kana' => 'も', 'num' => '25', 'empty' => 'NULL'],
        ['id' => '492', 'kana' => 'め', 'num' => '49', 'empty' => 'NULL'],
        ['id' => '493', 'kana' => 'う', 'num' => '69', 'empty' => 'NULL'],
        ['id' => '494', 'kana' => 'か', 'num' => '53', 'empty' => 'NULL'],
        ['id' => '495', 'kana' => 'お', 'num' => '2', 'empty' => 'NULL'],
        ['id' => '496', 'kana' => 'ひ', 'num' => '59', 'empty' => 'NULL'],
        ['id' => '497', 'kana' => 'い', 'num' => '68', 'empty' => 'NULL'],
        ['id' => '498', 'kana' => 'む', 'num' => '42', 'empty' => 'NULL'],
        ['id' => '499', 'kana' => 'ほ', 'num' => '7', 'empty' => 'NULL'],
        ['id' => '500', 'kana' => 'に', 'num' => '12', 'empty' => 'NULL'],
        ['id' => '501', 'kana' => 'か', 'num' => '11', 'empty' => 'NULL'],
        ['id' => '502', 'kana' => 'ろ', 'num' => '28', 'empty' => 'NULL'],
        ['id' => '503', 'kana' => 'わ', 'num' => '77', 'empty' => 'NULL'],
        ['id' => '504', 'kana' => 'ら', 'num' => '62', 'empty' => 'NULL'],
        ['id' => '505', 'kana' => 'ゆ', 'num' => '58', 'empty' => 'NULL'],
        ['id' => '506', 'kana' => 'ゆ', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '507', 'kana' => 'お', 'num' => '54', 'empty' => 'NULL'],
        ['id' => '508', 'kana' => 'そ', 'num' => '13', 'empty' => 'NULL'],
        ['id' => '509', 'kana' => 'み', 'num' => '71', 'empty' => 'NULL'],
        ['id' => '510', 'kana' => 'て', 'num' => '71', 'empty' => 'NULL'],
        ['id' => '511', 'kana' => 'か', 'num' => '36', 'empty' => 'NULL'],
        ['id' => '512', 'kana' => 'あ', 'num' => '74', 'empty' => 'NULL'],
        ['id' => '513', 'kana' => 'す', 'num' => '1', 'empty' => 'NULL'],
        ['id' => '514', 'kana' => 'す', 'num' => '11', 'empty' => 'NULL'],
        ['id' => '515', 'kana' => 'も', 'num' => '46', 'empty' => 'NULL'],
        ['id' => '516', 'kana' => 'い', 'num' => '66', 'empty' => 'NULL'],
        ['id' => '517', 'kana' => 'て', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '518', 'kana' => 'み', 'num' => '26', 'empty' => 'NULL'],
        ['id' => '519', 'kana' => 'る', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '520', 'kana' => 'こ', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '521', 'kana' => 'た', 'num' => '89', 'empty' => 'NULL'],
        ['id' => '522', 'kana' => 'そ', 'num' => '3', 'empty' => 'NULL'],
        ['id' => '523', 'kana' => 'ゆ', 'num' => '67', 'empty' => 'NULL'],
        ['id' => '524', 'kana' => 'こ', 'num' => '93', 'empty' => 'NULL'],
        ['id' => '525', 'kana' => 'ら', 'num' => '55', 'empty' => 'NULL'],
        ['id' => '526', 'kana' => 'ひ', 'num' => '68', 'empty' => 'NULL'],
        ['id' => '527', 'kana' => 'み', 'num' => '50', 'empty' => 'NULL'],
        ['id' => '528', 'kana' => 'け', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '529', 'kana' => 'ひ', 'num' => '46', 'empty' => 'NULL'],
        ['id' => '530', 'kana' => 'や', 'num' => '79', 'empty' => 'NULL'],
        ['id' => '531', 'kana' => 'き', 'num' => '35', 'empty' => 'NULL'],
        ['id' => '532', 'kana' => 'ね', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '533', 'kana' => 'を', 'num' => '21', 'empty' => 'NULL'],
        ['id' => '534', 'kana' => 'を', 'num' => '1', 'empty' => 'NULL'],
        ['id' => '535', 'kana' => 'も', 'num' => '15', 'empty' => 'NULL'],
        ['id' => '536', 'kana' => 'は', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '537', 'kana' => 'す', 'num' => '40', 'empty' => 'NULL'],
        ['id' => '538', 'kana' => 'け', 'num' => '91', 'empty' => 'NULL'],
        ['id' => '539', 'kana' => 'せ', 'num' => '55', 'empty' => 'NULL'],
        ['id' => '540', 'kana' => 'に', 'num' => '21', 'empty' => 'NULL'],
        ['id' => '541', 'kana' => 'へ', 'num' => '4', 'empty' => 'NULL'],
        ['id' => '542', 'kana' => 'ゆ', 'num' => '52', 'empty' => 'NULL'],
        ['id' => '543', 'kana' => 'の', 'num' => '52', 'empty' => 'NULL'],
        ['id' => '544', 'kana' => 'あ', 'num' => '69', 'empty' => 'NULL'],
        ['id' => '545', 'kana' => 'ひ', 'num' => '38', 'empty' => 'NULL'],
        ['id' => '546', 'kana' => 'も', 'num' => '36', 'empty' => 'NULL'],
        ['id' => '547', 'kana' => 'は', 'num' => '69', 'empty' => 'NULL'],
        ['id' => '548', 'kana' => 'は', 'num' => '70', 'empty' => 'NULL'],
        ['id' => '549', 'kana' => 'も', 'num' => '74', 'empty' => 'NULL'],
        ['id' => '550', 'kana' => 'む', 'num' => '47', 'empty' => 'NULL'],
        ['id' => '551', 'kana' => 'れ', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '552', 'kana' => 'ね', 'num' => '6', 'empty' => 'NULL'],
        ['id' => '553', 'kana' => 'に', 'num' => '17', 'empty' => 'NULL'],
        ['id' => '554', 'kana' => 'せ', 'num' => '97', 'empty' => 'NULL'],
        ['id' => '555', 'kana' => 'わ', 'num' => '49', 'empty' => 'NULL'],
        ['id' => '556', 'kana' => 'く', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '557', 'kana' => 'る', 'num' => '18', 'empty' => 'NULL'],
        ['id' => '558', 'kana' => 'と', 'num' => '47', 'empty' => 'NULL'],
        ['id' => '559', 'kana' => 'え', 'num' => '60', 'empty' => 'NULL'],
        ['id' => '560', 'kana' => 'め', 'num' => '74', 'empty' => 'NULL'],
        ['id' => '561', 'kana' => 'と', 'num' => '97', 'empty' => 'NULL'],
        ['id' => '562', 'kana' => 'た', 'num' => '69', 'empty' => 'NULL'],
        ['id' => '563', 'kana' => 'に', 'num' => '81', 'empty' => 'NULL'],
        ['id' => '564', 'kana' => 'さ', 'num' => '30', 'empty' => 'NULL'],
        ['id' => '565', 'kana' => 'わ', 'num' => '39', 'empty' => 'NULL'],
        ['id' => '566', 'kana' => 'う', 'num' => '68', 'empty' => 'NULL'],
        ['id' => '567', 'kana' => 'め', 'num' => '49', 'empty' => 'NULL'],
        ['id' => '568', 'kana' => 'る', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '569', 'kana' => 'こ', 'num' => '50', 'empty' => 'NULL'],
        ['id' => '570', 'kana' => 'か', 'num' => '68', 'empty' => 'NULL'],
        ['id' => '571', 'kana' => 'お', 'num' => '41', 'empty' => 'NULL'],
        ['id' => '572', 'kana' => 'を', 'num' => '32', 'empty' => 'NULL'],
        ['id' => '573', 'kana' => 'に', 'num' => '36', 'empty' => 'NULL'],
        ['id' => '574', 'kana' => 'に', 'num' => '35', 'empty' => 'NULL'],
        ['id' => '575', 'kana' => 'め', 'num' => '2', 'empty' => 'NULL'],
        ['id' => '576', 'kana' => 'い', 'num' => '43', 'empty' => 'NULL'],
        ['id' => '577', 'kana' => 'さ', 'num' => '10', 'empty' => 'NULL'],
        ['id' => '578', 'kana' => 'し', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '579', 'kana' => 'と', 'num' => '84', 'empty' => 'NULL'],
        ['id' => '580', 'kana' => 'そ', 'num' => '31', 'empty' => 'NULL'],
        ['id' => '581', 'kana' => 'き', 'num' => '83', 'empty' => 'NULL'],
        ['id' => '582', 'kana' => 'そ', 'num' => '83', 'empty' => 'NULL'],
        ['id' => '583', 'kana' => 'ぬ', 'num' => '31', 'empty' => 'NULL'],
        ['id' => '584', 'kana' => 'わ', 'num' => '54', 'empty' => 'NULL'],
        ['id' => '585', 'kana' => 'ち', 'num' => '61', 'empty' => 'NULL'],
        ['id' => '586', 'kana' => 'み', 'num' => '14', 'empty' => 'NULL'],
        ['id' => '587', 'kana' => 'れ', 'num' => '55', 'empty' => 'NULL'],
        ['id' => '588', 'kana' => 'ほ', 'num' => '85', 'empty' => 'NULL'],
        ['id' => '589', 'kana' => 'わ', 'num' => '8', 'empty' => 'NULL'],
        ['id' => '590', 'kana' => 'ひ', 'num' => '18', 'empty' => 'NULL'],
        ['id' => '591', 'kana' => 'も', 'num' => '62', 'empty' => 'NULL'],
        ['id' => '592', 'kana' => 'く', 'num' => '48', 'empty' => 'NULL'],
        ['id' => '593', 'kana' => 'け', 'num' => '57', 'empty' => 'NULL'],
        ['id' => '594', 'kana' => 'こ', 'num' => '32', 'empty' => 'NULL'],
        ['id' => '595', 'kana' => 'ね', 'num' => '56', 'empty' => 'NULL'],
        ['id' => '596', 'kana' => 'か', 'num' => '25', 'empty' => 'NULL'],
        ['id' => '597', 'kana' => 'の', 'num' => '4', 'empty' => 'NULL'],
        ['id' => '598', 'kana' => 'も', 'num' => '34', 'empty' => 'NULL'],
        ['id' => '599', 'kana' => 'て', 'num' => '24', 'empty' => 'NULL'],
        ['id' => '600', 'kana' => 'す', 'num' => '11', 'empty' => 'NULL'],
        ['id' => '601', 'kana' => 'と', 'num' => '70', 'empty' => 'NULL'],
        ['id' => '602', 'kana' => 'こ', 'num' => '9', 'empty' => 'NULL'],
        ['id' => '603', 'kana' => 'せ', 'num' => '53', 'empty' => 'NULL'],
        ['id' => '604', 'kana' => 'わ', 'num' => '4', 'empty' => 'NULL'],
        ['id' => '605', 'kana' => 'る', 'num' => '12', 'empty' => 'NULL'],
        ['id' => '606', 'kana' => 'り', 'num' => '53', 'empty' => 'NULL'],
        ['id' => '607', 'kana' => 'に', 'num' => '88', 'empty' => 'NULL'],
        ['id' => '608', 'kana' => 'こ', 'num' => '63', 'empty' => 'NULL'],
        ['id' => '609', 'kana' => 'も', 'num' => '66', 'empty' => 'NULL'],
        ['id' => '610', 'kana' => 'れ', 'num' => '25', 'empty' => 'NULL'],
        ['id' => '611', 'kana' => 'ぬ', 'num' => '88', 'empty' => 'NULL'],
        ['id' => '612', 'kana' => 'そ', 'num' => '2', 'empty' => 'NULL'],
        ['id' => '613', 'kana' => 'ぬ', 'num' => '99', 'empty' => 'NULL'],
        ['id' => '614', 'kana' => 'る', 'num' => '61', 'empty' => 'NULL'],
        ['id' => '615', 'kana' => 'ゆ', 'num' => '63', 'empty' => 'NULL'],
        ['id' => '616', 'kana' => 'た', 'num' => '92', 'empty' => 'NULL'],
        ['id' => '617', 'kana' => 'は', 'num' => '37', 'empty' => 'NULL'],
        ['id' => '618', 'kana' => 'ち', 'num' => '73', 'empty' => 'NULL'],
        ['id' => '619', 'kana' => 'か', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '620', 'kana' => 'れ', 'num' => '23', 'empty' => 'NULL'],
        ['id' => '621', 'kana' => 'る', 'num' => '97', 'empty' => 'NULL'],
        ['id' => '622', 'kana' => 'に', 'num' => '53', 'empty' => 'NULL'],
        ['id' => '623', 'kana' => 'う', 'num' => '49', 'empty' => 'NULL'],
        ['id' => '624', 'kana' => 'な', 'num' => '39', 'empty' => 'NULL'],
        ['id' => '625', 'kana' => 'ぬ', 'num' => '10', 'empty' => 'NULL'],
        ['id' => '626', 'kana' => 'う', 'num' => '68', 'empty' => 'NULL'],
        ['id' => '627', 'kana' => 'す', 'num' => '87', 'empty' => 'NULL'],
        ['id' => '628', 'kana' => 'る', 'num' => '12', 'empty' => 'NULL'],
        ['id' => '629', 'kana' => 'た', 'num' => '4', 'empty' => 'NULL'],
        ['id' => '630', 'kana' => 'ふ', 'num' => '14', 'empty' => 'NULL'],
        ['id' => '631', 'kana' => 'へ', 'num' => '1', 'empty' => 'NULL'],
        ['id' => '632', 'kana' => 'あ', 'num' => '54', 'empty' => 'NULL'],
        ['id' => '633', 'kana' => 'み', 'num' => '9', 'empty' => 'NULL'],
        ['id' => '634', 'kana' => 'い', 'num' => '67', 'empty' => 'NULL'],
        ['id' => '635', 'kana' => 'そ', 'num' => '78', 'empty' => 'NULL'],
        ['id' => '636', 'kana' => 'そ', 'num' => '24', 'empty' => 'NULL'],
        ['id' => '637', 'kana' => 'ろ', 'num' => '61', 'empty' => 'NULL'],
        ['id' => '638', 'kana' => 'ら', 'num' => '99', 'empty' => 'NULL'],
        ['id' => '639', 'kana' => 'る', 'num' => '59', 'empty' => 'NULL'],
        ['id' => '640', 'kana' => 'つ', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '641', 'kana' => 'く', 'num' => '58', 'empty' => 'NULL'],
        ['id' => '642', 'kana' => 'な', 'num' => '39', 'empty' => 'NULL'],
        ['id' => '643', 'kana' => 'ま', 'num' => '90', 'empty' => 'NULL'],
        ['id' => '644', 'kana' => 'し', 'num' => '66', 'empty' => 'NULL'],
        ['id' => '645', 'kana' => 'す', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '646', 'kana' => 'き', 'num' => '46', 'empty' => 'NULL'],
        ['id' => '647', 'kana' => 'め', 'num' => '100', 'empty' => 'NULL'],
        ['id' => '648', 'kana' => 'よ', 'num' => '23', 'empty' => 'NULL'],
        ['id' => '649', 'kana' => 'そ', 'num' => '96', 'empty' => 'NULL'],
        ['id' => '650', 'kana' => 'け', 'num' => '55', 'empty' => 'NULL'],
        ['id' => '651', 'kana' => 'め', 'num' => '22', 'empty' => 'NULL'],
        ['id' => '652', 'kana' => 'お', 'num' => '16', 'empty' => 'NULL'],
        ['id' => '653', 'kana' => 'く', 'num' => '88', 'empty' => 'NULL'],
        ['id' => '654', 'kana' => 'ろ', 'num' => '93', 'empty' => 'NULL'],
        ['id' => '655', 'kana' => 'ほ', 'num' => '60', 'empty' => 'NULL'],
        ['id' => '656', 'kana' => 'た', 'num' => '42', 'empty' => 'NULL'],
        ['id' => '657', 'kana' => 'け', 'num' => '14', 'empty' => 'NULL'],
        ['id' => '658', 'kana' => 'た', 'num' => '46', 'empty' => 'NULL'],
        ['id' => '659', 'kana' => 'う', 'num' => '84', 'empty' => 'NULL'],
        ['id' => '660', 'kana' => 'え', 'num' => '59', 'empty' => 'NULL'],
        ['id' => '661', 'kana' => 'き', 'num' => '96', 'empty' => 'NULL'],
        ['id' => '662', 'kana' => 'の', 'num' => '60', 'empty' => 'NULL'],
        ['id' => '663', 'kana' => 'り', 'num' => '40', 'empty' => 'NULL'],
        ['id' => '664', 'kana' => 'に', 'num' => '69', 'empty' => 'NULL'],
        ['id' => '665', 'kana' => 'に', 'num' => '15', 'empty' => 'NULL'],
        ['id' => '666', 'kana' => 'め', 'num' => '30', 'empty' => 'NULL'],
        ['id' => '667', 'kana' => 'わ', 'num' => '93', 'empty' => 'NULL'],
        ['id' => '668', 'kana' => 'れ', 'num' => '86', 'empty' => 'NULL'],
        ['id' => '669', 'kana' => 'た', 'num' => '65', 'empty' => 'NULL'],
        ['id' => '670', 'kana' => 'よ', 'num' => '52', 'empty' => 'NULL'],
        ['id' => '671', 'kana' => 'を', 'num' => '8', 'empty' => 'NULL'],
        ['id' => '672', 'kana' => 'ら', 'num' => '39', 'empty' => 'NULL'],
        ['id' => '673', 'kana' => 'な', 'num' => '77', 'empty' => 'NULL'],
        ['id' => '674', 'kana' => 'わ', 'num' => '7', 'empty' => 'NULL'],
        ['id' => '675', 'kana' => 'し', 'num' => '51', 'empty' => 'NULL'],
        ['id' => '676', 'kana' => 'ん', 'num' => '83', 'empty' => 'NULL'],
        ['id' => '677', 'kana' => 'か', 'num' => '57', 'empty' => 'NULL'],
        ['id' => '678', 'kana' => 'ゆ', 'num' => '49', 'empty' => 'NULL'],
        ['id' => '679', 'kana' => 'し', 'num' => '13', 'empty' => 'NULL'],
        ['id' => '680', 'kana' => 'ろ', 'num' => '50', 'empty' => 'NULL'],
        ['id' => '681', 'kana' => 'ひ', 'num' => '40', 'empty' => 'NULL'],
        ['id' => '682', 'kana' => 'を', 'num' => '40', 'empty' => 'NULL'],
        ['id' => '683', 'kana' => 'へ', 'num' => '11', 'empty' => 'NULL'],
        ['id' => '684', 'kana' => 'く', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '685', 'kana' => 'ろ', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '686', 'kana' => 'え', 'num' => '20', 'empty' => 'NULL'],
        ['id' => '687', 'kana' => 'え', 'num' => '68', 'empty' => 'NULL'],
        ['id' => '688', 'kana' => 'と', 'num' => '70', 'empty' => 'NULL'],
        ['id' => '689', 'kana' => 'う', 'num' => '79', 'empty' => 'NULL'],
        ['id' => '690', 'kana' => 'ひ', 'num' => '80', 'empty' => 'NULL'],
        ['id' => '691', 'kana' => 'す', 'num' => '61', 'empty' => 'NULL'],
        ['id' => '692', 'kana' => 'わ', 'num' => '27', 'empty' => 'NULL'],
        ['id' => '693', 'kana' => 'ち', 'num' => '93', 'empty' => 'NULL'],
        ['id' => '694', 'kana' => 'る', 'num' => '88', 'empty' => 'NULL'],
        ['id' => '695', 'kana' => 'ま', 'num' => '73', 'empty' => 'NULL'],
        ['id' => '696', 'kana' => 'も', 'num' => '88', 'empty' => 'NULL'],
        ['id' => '697', 'kana' => 'ふ', 'num' => '97', 'empty' => 'NULL'],
        ['id' => '698', 'kana' => 'み', 'num' => '23', 'empty' => 'NULL'],
        ['id' => '699', 'kana' => 'ね', 'num' => '28', 'empty' => 'NULL'],
        ['id' => '700', 'kana' => 'て', 'num' => '26', 'empty' => 'NULL'],
        ['id' => '701', 'kana' => 'と', 'num' => '61', 'empty' => 'NULL'],
        ['id' => '702', 'kana' => 'こ', 'num' => '56', 'empty' => 'NULL'],
        ['id' => '703', 'kana' => 'め', 'num' => '10', 'empty' => 'NULL'],
        ['id' => '704', 'kana' => 'い', 'num' => '43', 'empty' => 'NULL'],
        ['id' => '705', 'kana' => 'る', 'num' => '52', 'empty' => 'NULL'],
        ['id' => '706', 'kana' => 'そ', 'num' => '46', 'empty' => 'NULL'],
        ['id' => '707', 'kana' => 'こ', 'num' => '30', 'empty' => 'NULL'],
        ['id' => '708', 'kana' => 'ぬ', 'num' => '11', 'empty' => 'NULL'],
        ['id' => '709', 'kana' => 'か', 'num' => '100', 'empty' => 'NULL'],
        ['id' => '710', 'kana' => 'む', 'num' => '96', 'empty' => 'NULL'],
        ['id' => '711', 'kana' => 'ゆ', 'num' => '13', 'empty' => 'NULL'],
        ['id' => '712', 'kana' => 'ね', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '713', 'kana' => 'と', 'num' => '64', 'empty' => 'NULL'],
        ['id' => '714', 'kana' => 'う', 'num' => '31', 'empty' => 'NULL'],
        ['id' => '715', 'kana' => 'ほ', 'num' => '16', 'empty' => 'NULL'],
        ['id' => '716', 'kana' => 'む', 'num' => '77', 'empty' => 'NULL'],
        ['id' => '717', 'kana' => 'く', 'num' => '69', 'empty' => 'NULL'],
        ['id' => '718', 'kana' => 'る', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '719', 'kana' => 'へ', 'num' => '96', 'empty' => 'NULL'],
        ['id' => '720', 'kana' => 'し', 'num' => '96', 'empty' => 'NULL'],
        ['id' => '721', 'kana' => 'と', 'num' => '4', 'empty' => 'NULL'],
        ['id' => '722', 'kana' => 'を', 'num' => '30', 'empty' => 'NULL'],
        ['id' => '723', 'kana' => 'わ', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '724', 'kana' => 'ち', 'num' => '56', 'empty' => 'NULL'],
        ['id' => '725', 'kana' => 'ね', 'num' => '46', 'empty' => 'NULL'],
        ['id' => '726', 'kana' => 'ん', 'num' => '15', 'empty' => 'NULL'],
        ['id' => '727', 'kana' => 'り', 'num' => '61', 'empty' => 'NULL'],
        ['id' => '728', 'kana' => 'ひ', 'num' => '57', 'empty' => 'NULL'],
        ['id' => '729', 'kana' => 'こ', 'num' => '49', 'empty' => 'NULL'],
        ['id' => '730', 'kana' => 'を', 'num' => '68', 'empty' => 'NULL'],
        ['id' => '731', 'kana' => 'こ', 'num' => '24', 'empty' => 'NULL'],
        ['id' => '732', 'kana' => 'て', 'num' => '84', 'empty' => 'NULL'],
        ['id' => '733', 'kana' => 'す', 'num' => '99', 'empty' => 'NULL'],
        ['id' => '734', 'kana' => 'か', 'num' => '25', 'empty' => 'NULL'],
        ['id' => '735', 'kana' => 'お', 'num' => '45', 'empty' => 'NULL'],
        ['id' => '736', 'kana' => 'く', 'num' => '7', 'empty' => 'NULL'],
        ['id' => '737', 'kana' => 'え', 'num' => '16', 'empty' => 'NULL'],
        ['id' => '738', 'kana' => 'は', 'num' => '12', 'empty' => 'NULL'],
        ['id' => '739', 'kana' => 'こ', 'num' => '67', 'empty' => 'NULL'],
        ['id' => '740', 'kana' => 'み', 'num' => '79', 'empty' => 'NULL'],
        ['id' => '741', 'kana' => 'に', 'num' => '77', 'empty' => 'NULL'],
        ['id' => '742', 'kana' => 'を', 'num' => '60', 'empty' => 'NULL'],
        ['id' => '743', 'kana' => 'り', 'num' => '8', 'empty' => 'NULL'],
        ['id' => '744', 'kana' => 'ろ', 'num' => '47', 'empty' => 'NULL'],
        ['id' => '745', 'kana' => 'る', 'num' => '27', 'empty' => 'NULL'],
        ['id' => '746', 'kana' => 'け', 'num' => '56', 'empty' => 'NULL'],
        ['id' => '747', 'kana' => 'ほ', 'num' => '52', 'empty' => 'NULL'],
        ['id' => '748', 'kana' => 'た', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '749', 'kana' => 'も', 'num' => '23', 'empty' => 'NULL'],
        ['id' => '750', 'kana' => 'な', 'num' => '65', 'empty' => 'NULL'],
        ['id' => '751', 'kana' => 'れ', 'num' => '51', 'empty' => 'NULL'],
        ['id' => '752', 'kana' => 'な', 'num' => '48', 'empty' => 'NULL'],
        ['id' => '753', 'kana' => 'も', 'num' => '28', 'empty' => 'NULL'],
        ['id' => '754', 'kana' => 'さ', 'num' => '44', 'empty' => 'NULL'],
        ['id' => '755', 'kana' => 'も', 'num' => '61', 'empty' => 'NULL'],
        ['id' => '756', 'kana' => 'そ', 'num' => '34', 'empty' => 'NULL'],
        ['id' => '757', 'kana' => 'し', 'num' => '4', 'empty' => 'NULL'],
        ['id' => '758', 'kana' => 'ひ', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '759', 'kana' => 'け', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '760', 'kana' => 'き', 'num' => '2', 'empty' => 'NULL'],
        ['id' => '761', 'kana' => 'は', 'num' => '93', 'empty' => 'NULL'],
        ['id' => '762', 'kana' => 'ね', 'num' => '73', 'empty' => 'NULL'],
        ['id' => '763', 'kana' => 'い', 'num' => '69', 'empty' => 'NULL'],
        ['id' => '764', 'kana' => 'め', 'num' => '85', 'empty' => 'NULL'],
        ['id' => '765', 'kana' => 'は', 'num' => '100', 'empty' => 'NULL'],
        ['id' => '766', 'kana' => 'ち', 'num' => '73', 'empty' => 'NULL'],
        ['id' => '767', 'kana' => 'ま', 'num' => '78', 'empty' => 'NULL'],
        ['id' => '768', 'kana' => 'り', 'num' => '22', 'empty' => 'NULL'],
        ['id' => '769', 'kana' => 'こ', 'num' => '77', 'empty' => 'NULL'],
        ['id' => '770', 'kana' => 'さ', 'num' => '22', 'empty' => 'NULL'],
        ['id' => '771', 'kana' => 'ゆ', 'num' => '87', 'empty' => 'NULL'],
        ['id' => '772', 'kana' => 'と', 'num' => '15', 'empty' => 'NULL'],
        ['id' => '773', 'kana' => 'ふ', 'num' => '16', 'empty' => 'NULL'],
        ['id' => '774', 'kana' => 'う', 'num' => '23', 'empty' => 'NULL'],
        ['id' => '775', 'kana' => 'と', 'num' => '54', 'empty' => 'NULL'],
        ['id' => '776', 'kana' => 'ん', 'num' => '28', 'empty' => 'NULL'],
        ['id' => '777', 'kana' => 'ぬ', 'num' => '97', 'empty' => 'NULL'],
        ['id' => '778', 'kana' => 'を', 'num' => '61', 'empty' => 'NULL'],
        ['id' => '779', 'kana' => 'き', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '780', 'kana' => 'き', 'num' => '30', 'empty' => 'NULL'],
        ['id' => '781', 'kana' => 'よ', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '782', 'kana' => 'せ', 'num' => '14', 'empty' => 'NULL'],
        ['id' => '783', 'kana' => 'な', 'num' => '43', 'empty' => 'NULL'],
        ['id' => '784', 'kana' => 'ら', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '785', 'kana' => 'み', 'num' => '14', 'empty' => 'NULL'],
        ['id' => '786', 'kana' => 'れ', 'num' => '54', 'empty' => 'NULL'],
        ['id' => '787', 'kana' => 'す', 'num' => '15', 'empty' => 'NULL'],
        ['id' => '788', 'kana' => 'う', 'num' => '98', 'empty' => 'NULL'],
        ['id' => '789', 'kana' => 'こ', 'num' => '40', 'empty' => 'NULL'],
        ['id' => '790', 'kana' => 'わ', 'num' => '27', 'empty' => 'NULL'],
        ['id' => '791', 'kana' => 'む', 'num' => '84', 'empty' => 'NULL'],
        ['id' => '792', 'kana' => 'え', 'num' => '90', 'empty' => 'NULL'],
        ['id' => '793', 'kana' => 'ゆ', 'num' => '83', 'empty' => 'NULL'],
        ['id' => '794', 'kana' => 'ほ', 'num' => '91', 'empty' => 'NULL'],
        ['id' => '795', 'kana' => 'わ', 'num' => '10', 'empty' => 'NULL'],
        ['id' => '796', 'kana' => 'ろ', 'num' => '45', 'empty' => 'NULL'],
        ['id' => '797', 'kana' => 'ま', 'num' => '18', 'empty' => 'NULL'],
        ['id' => '798', 'kana' => 'み', 'num' => '7', 'empty' => 'NULL'],
        ['id' => '799', 'kana' => 'れ', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '800', 'kana' => 'よ', 'num' => '77', 'empty' => 'NULL'],
        ['id' => '801', 'kana' => 'ね', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '802', 'kana' => 'に', 'num' => '40', 'empty' => 'NULL'],
        ['id' => '803', 'kana' => 'ろ', 'num' => '62', 'empty' => 'NULL'],
        ['id' => '804', 'kana' => 'め', 'num' => '46', 'empty' => 'NULL'],
        ['id' => '805', 'kana' => 'う', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '806', 'kana' => 'む', 'num' => '69', 'empty' => 'NULL'],
        ['id' => '807', 'kana' => 'さ', 'num' => '26', 'empty' => 'NULL'],
        ['id' => '808', 'kana' => 'し', 'num' => '17', 'empty' => 'NULL'],
        ['id' => '809', 'kana' => 'け', 'num' => '10', 'empty' => 'NULL'],
        ['id' => '810', 'kana' => 'み', 'num' => '89', 'empty' => 'NULL'],
        ['id' => '811', 'kana' => 'ぬ', 'num' => '87', 'empty' => 'NULL'],
        ['id' => '812', 'kana' => 'く', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '813', 'kana' => 'や', 'num' => '18', 'empty' => 'NULL'],
        ['id' => '814', 'kana' => 'ん', 'num' => '87', 'empty' => 'NULL'],
        ['id' => '815', 'kana' => 'う', 'num' => '30', 'empty' => 'NULL'],
        ['id' => '816', 'kana' => 'に', 'num' => '12', 'empty' => 'NULL'],
        ['id' => '817', 'kana' => 'る', 'num' => '57', 'empty' => 'NULL'],
        ['id' => '818', 'kana' => 'め', 'num' => '28', 'empty' => 'NULL'],
        ['id' => '819', 'kana' => 'も', 'num' => '82', 'empty' => 'NULL'],
        ['id' => '820', 'kana' => 'く', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '821', 'kana' => 'を', 'num' => '80', 'empty' => 'NULL'],
        ['id' => '822', 'kana' => 'り', 'num' => '43', 'empty' => 'NULL'],
        ['id' => '823', 'kana' => 'お', 'num' => '78', 'empty' => 'NULL'],
        ['id' => '824', 'kana' => 'こ', 'num' => '54', 'empty' => 'NULL'],
        ['id' => '825', 'kana' => 'り', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '826', 'kana' => 'け', 'num' => '35', 'empty' => 'NULL'],
        ['id' => '827', 'kana' => 'た', 'num' => '40', 'empty' => 'NULL'],
        ['id' => '828', 'kana' => 'へ', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '829', 'kana' => 'に', 'num' => '4', 'empty' => 'NULL'],
        ['id' => '830', 'kana' => 'お', 'num' => '59', 'empty' => 'NULL'],
        ['id' => '831', 'kana' => 'そ', 'num' => '28', 'empty' => 'NULL'],
        ['id' => '832', 'kana' => 'へ', 'num' => '14', 'empty' => 'NULL'],
        ['id' => '833', 'kana' => 'ひ', 'num' => '55', 'empty' => 'NULL'],
        ['id' => '834', 'kana' => 'り', 'num' => '73', 'empty' => 'NULL'],
        ['id' => '835', 'kana' => 'に', 'num' => '14', 'empty' => 'NULL'],
        ['id' => '836', 'kana' => 'さ', 'num' => '46', 'empty' => 'NULL'],
        ['id' => '837', 'kana' => 'め', 'num' => '24', 'empty' => 'NULL'],
        ['id' => '838', 'kana' => 'そ', 'num' => '86', 'empty' => 'NULL'],
        ['id' => '839', 'kana' => 'か', 'num' => '91', 'empty' => 'NULL'],
        ['id' => '840', 'kana' => 'の', 'num' => '82', 'empty' => 'NULL'],
        ['id' => '841', 'kana' => 'お', 'num' => '59', 'empty' => 'NULL'],
        ['id' => '842', 'kana' => 'む', 'num' => '64', 'empty' => 'NULL'],
        ['id' => '843', 'kana' => 'ふ', 'num' => '80', 'empty' => 'NULL'],
        ['id' => '844', 'kana' => 'り', 'num' => '58', 'empty' => 'NULL'],
        ['id' => '845', 'kana' => 'ま', 'num' => '64', 'empty' => 'NULL'],
        ['id' => '846', 'kana' => 'を', 'num' => '61', 'empty' => 'NULL'],
        ['id' => '847', 'kana' => 'ら', 'num' => '86', 'empty' => 'NULL'],
        ['id' => '848', 'kana' => 'も', 'num' => '70', 'empty' => 'NULL'],
        ['id' => '849', 'kana' => 'し', 'num' => '47', 'empty' => 'NULL'],
        ['id' => '850', 'kana' => 'さ', 'num' => '30', 'empty' => 'NULL'],
        ['id' => '851', 'kana' => 'り', 'num' => '8', 'empty' => 'NULL'],
        ['id' => '852', 'kana' => 'な', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '853', 'kana' => 'め', 'num' => '89', 'empty' => 'NULL'],
        ['id' => '854', 'kana' => 'つ', 'num' => '47', 'empty' => 'NULL'],
        ['id' => '855', 'kana' => 'と', 'num' => '36', 'empty' => 'NULL'],
        ['id' => '856', 'kana' => 'ち', 'num' => '92', 'empty' => 'NULL'],
        ['id' => '857', 'kana' => 'し', 'num' => '3', 'empty' => 'NULL'],
        ['id' => '858', 'kana' => 'り', 'num' => '71', 'empty' => 'NULL'],
        ['id' => '859', 'kana' => 'し', 'num' => '56', 'empty' => 'NULL'],
        ['id' => '860', 'kana' => 'て', 'num' => '91', 'empty' => 'NULL'],
        ['id' => '861', 'kana' => 'の', 'num' => '77', 'empty' => 'NULL'],
        ['id' => '862', 'kana' => 'さ', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '863', 'kana' => 'と', 'num' => '12', 'empty' => 'NULL'],
        ['id' => '864', 'kana' => 'け', 'num' => '73', 'empty' => 'NULL'],
        ['id' => '865', 'kana' => 'も', 'num' => '53', 'empty' => 'NULL'],
        ['id' => '866', 'kana' => 'つ', 'num' => '14', 'empty' => 'NULL'],
        ['id' => '867', 'kana' => 'し', 'num' => '6', 'empty' => 'NULL'],
        ['id' => '868', 'kana' => 'ち', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '869', 'kana' => 'め', 'num' => '65', 'empty' => 'NULL'],
        ['id' => '870', 'kana' => 'ゆ', 'num' => '75', 'empty' => 'NULL'],
        ['id' => '871', 'kana' => 'に', 'num' => '35', 'empty' => 'NULL'],
        ['id' => '872', 'kana' => 'り', 'num' => '66', 'empty' => 'NULL'],
        ['id' => '873', 'kana' => 'を', 'num' => '51', 'empty' => 'NULL'],
        ['id' => '874', 'kana' => 'ち', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '875', 'kana' => 'ら', 'num' => '55', 'empty' => 'NULL'],
        ['id' => '876', 'kana' => 'ぬ', 'num' => '78', 'empty' => 'NULL'],
        ['id' => '877', 'kana' => 'え', 'num' => '98', 'empty' => 'NULL'],
        ['id' => '878', 'kana' => 'か', 'num' => '8', 'empty' => 'NULL'],
        ['id' => '879', 'kana' => 'め', 'num' => '89', 'empty' => 'NULL'],
        ['id' => '880', 'kana' => 'と', 'num' => '43', 'empty' => 'NULL'],
        ['id' => '881', 'kana' => 'こ', 'num' => '25', 'empty' => 'NULL'],
        ['id' => '882', 'kana' => 'ひ', 'num' => '1', 'empty' => 'NULL'],
        ['id' => '883', 'kana' => 'か', 'num' => '44', 'empty' => 'NULL'],
        ['id' => '884', 'kana' => 'ん', 'num' => '74', 'empty' => 'NULL'],
        ['id' => '885', 'kana' => 'ひ', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '886', 'kana' => 'わ', 'num' => '47', 'empty' => 'NULL'],
        ['id' => '887', 'kana' => 'ん', 'num' => '83', 'empty' => 'NULL'],
        ['id' => '888', 'kana' => 'ち', 'num' => '6', 'empty' => 'NULL'],
        ['id' => '889', 'kana' => 'ら', 'num' => '77', 'empty' => 'NULL'],
        ['id' => '890', 'kana' => 'や', 'num' => '97', 'empty' => 'NULL'],
        ['id' => '891', 'kana' => 'け', 'num' => '62', 'empty' => 'NULL'],
        ['id' => '892', 'kana' => 'つ', 'num' => '58', 'empty' => 'NULL'],
        ['id' => '893', 'kana' => 'め', 'num' => '78', 'empty' => 'NULL'],
        ['id' => '894', 'kana' => 'な', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '895', 'kana' => 'き', 'num' => '32', 'empty' => 'NULL'],
        ['id' => '896', 'kana' => 'く', 'num' => '48', 'empty' => 'NULL'],
        ['id' => '897', 'kana' => 'ま', 'num' => '45', 'empty' => 'NULL'],
        ['id' => '898', 'kana' => 'た', 'num' => '41', 'empty' => 'NULL'],
        ['id' => '899', 'kana' => 'と', 'num' => '71', 'empty' => 'NULL'],
        ['id' => '900', 'kana' => 'め', 'num' => '47', 'empty' => 'NULL'],
        ['id' => '901', 'kana' => 'れ', 'num' => '67', 'empty' => 'NULL'],
        ['id' => '902', 'kana' => 'う', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '903', 'kana' => 'わ', 'num' => '14', 'empty' => 'NULL'],
        ['id' => '904', 'kana' => 'ゆ', 'num' => '53', 'empty' => 'NULL'],
        ['id' => '905', 'kana' => 'り', 'num' => '47', 'empty' => 'NULL'],
        ['id' => '906', 'kana' => 'ほ', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '907', 'kana' => 'へ', 'num' => '100', 'empty' => 'NULL'],
        ['id' => '908', 'kana' => 'か', 'num' => '89', 'empty' => 'NULL'],
        ['id' => '909', 'kana' => 'り', 'num' => '74', 'empty' => 'NULL'],
        ['id' => '910', 'kana' => 'に', 'num' => '88', 'empty' => 'NULL'],
        ['id' => '911', 'kana' => 'か', 'num' => '24', 'empty' => 'NULL'],
        ['id' => '912', 'kana' => 'か', 'num' => '95', 'empty' => 'NULL'],
        ['id' => '913', 'kana' => 'ほ', 'num' => '27', 'empty' => 'NULL'],
        ['id' => '914', 'kana' => 'や', 'num' => '29', 'empty' => 'NULL'],
        ['id' => '915', 'kana' => 'ね', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '916', 'kana' => 'し', 'num' => '28', 'empty' => 'NULL'],
        ['id' => '917', 'kana' => 'ん', 'num' => '32', 'empty' => 'NULL'],
        ['id' => '918', 'kana' => 'も', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '919', 'kana' => 'き', 'num' => '22', 'empty' => 'NULL'],
        ['id' => '920', 'kana' => 'ほ', 'num' => '40', 'empty' => 'NULL'],
        ['id' => '921', 'kana' => 'せ', 'num' => '65', 'empty' => 'NULL'],
        ['id' => '922', 'kana' => 'く', 'num' => '4', 'empty' => 'NULL'],
        ['id' => '923', 'kana' => 'れ', 'num' => '20', 'empty' => 'NULL'],
        ['id' => '924', 'kana' => 'も', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '925', 'kana' => 'い', 'num' => '37', 'empty' => 'NULL'],
        ['id' => '926', 'kana' => 'ね', 'num' => '72', 'empty' => 'NULL'],
        ['id' => '927', 'kana' => 'ひ', 'num' => '55', 'empty' => 'NULL'],
        ['id' => '928', 'kana' => 'え', 'num' => '25', 'empty' => 'NULL'],
        ['id' => '929', 'kana' => 'な', 'num' => '71', 'empty' => 'NULL'],
        ['id' => '930', 'kana' => 'わ', 'num' => '96', 'empty' => 'NULL'],
        ['id' => '931', 'kana' => 'ふ', 'num' => '76', 'empty' => 'NULL'],
        ['id' => '932', 'kana' => 'も', 'num' => '41', 'empty' => 'NULL'],
        ['id' => '933', 'kana' => 'る', 'num' => '38', 'empty' => 'NULL'],
        ['id' => '934', 'kana' => 'た', 'num' => '67', 'empty' => 'NULL'],
        ['id' => '935', 'kana' => 'む', 'num' => '96', 'empty' => 'NULL'],
        ['id' => '936', 'kana' => 'せ', 'num' => '19', 'empty' => 'NULL'],
        ['id' => '937', 'kana' => 'い', 'num' => '12', 'empty' => 'NULL'],
        ['id' => '938', 'kana' => 'ら', 'num' => '51', 'empty' => 'NULL'],
        ['id' => '939', 'kana' => 'く', 'num' => '42', 'empty' => 'NULL'],
        ['id' => '940', 'kana' => 'き', 'num' => '94', 'empty' => 'NULL'],
        ['id' => '941', 'kana' => 'く', 'num' => '49', 'empty' => 'NULL'],
        ['id' => '942', 'kana' => 'め', 'num' => '97', 'empty' => 'NULL'],
        ['id' => '943', 'kana' => 'す', 'num' => '77', 'empty' => 'NULL'],
        ['id' => '944', 'kana' => 'よ', 'num' => '74', 'empty' => 'NULL'],
        ['id' => '945', 'kana' => 'せ', 'num' => '26', 'empty' => 'NULL'],
        ['id' => '946', 'kana' => 'く', 'num' => '16', 'empty' => 'NULL'],
        ['id' => '947', 'kana' => 'は', 'num' => '20', 'empty' => 'NULL'],
        ['id' => '948', 'kana' => 'し', 'num' => '7', 'empty' => 'NULL'],
        ['id' => '949', 'kana' => 'め', 'num' => '99', 'empty' => 'NULL'],
        ['id' => '950', 'kana' => 'め', 'num' => '63', 'empty' => 'NULL'],
        ['id' => '951', 'kana' => 'り', 'num' => '75', 'empty' => 'NULL'],
        ['id' => '952', 'kana' => 'さ', 'num' => '91', 'empty' => 'NULL'],
        ['id' => '953', 'kana' => 'た', 'num' => '24', 'empty' => 'NULL'],
        ['id' => '954', 'kana' => 'ん', 'num' => '54', 'empty' => 'NULL'],
        ['id' => '955', 'kana' => 'て', 'num' => '2', 'empty' => 'NULL'],
        ['id' => '956', 'kana' => 'く', 'num' => '64', 'empty' => 'NULL'],
        ['id' => '957', 'kana' => 'へ', 'num' => '20', 'empty' => 'NULL'],
        ['id' => '958', 'kana' => 'も', 'num' => '37', 'empty' => 'NULL'],
        ['id' => '959', 'kana' => 'け', 'num' => '49', 'empty' => 'NULL'],
        ['id' => '960', 'kana' => 'ゆ', 'num' => '97', 'empty' => 'NULL'],
        ['id' => '961', 'kana' => 'い', 'num' => '73', 'empty' => 'NULL'],
        ['id' => '962', 'kana' => 'と', 'num' => '39', 'empty' => 'NULL'],
        ['id' => '963', 'kana' => 'は', 'num' => '34', 'empty' => 'NULL'],
        ['id' => '964', 'kana' => 'の', 'num' => '8', 'empty' => 'NULL'],
        ['id' => '965', 'kana' => 'つ', 'num' => '95', 'empty' => 'NULL'],
        ['id' => '966', 'kana' => 'ち', 'num' => '3', 'empty' => 'NULL'],
        ['id' => '967', 'kana' => 'へ', 'num' => '48', 'empty' => 'NULL'],
        ['id' => '968', 'kana' => 'く', 'num' => '74', 'empty' => 'NULL'],
        ['id' => '969', 'kana' => 'ろ', 'num' => '99', 'empty' => 'NULL'],
        ['id' => '970', 'kana' => 'い', 'num' => '75', 'empty' => 'NULL'],
        ['id' => '971', 'kana' => 'ひ', 'num' => '63', 'empty' => 'NULL'],
        ['id' => '972', 'kana' => 'も', 'num' => '22', 'empty' => 'NULL'],
        ['id' => '973', 'kana' => 'あ', 'num' => '40', 'empty' => 'NULL'],
        ['id' => '974', 'kana' => 'せ', 'num' => '25', 'empty' => 'NULL'],
        ['id' => '975', 'kana' => 'て', 'num' => '49', 'empty' => 'NULL'],
        ['id' => '976', 'kana' => 'さ', 'num' => '54', 'empty' => 'NULL'],
        ['id' => '977', 'kana' => 'も', 'num' => '91', 'empty' => 'NULL'],
        ['id' => '978', 'kana' => 'も', 'num' => '42', 'empty' => 'NULL'],
        ['id' => '979', 'kana' => 'し', 'num' => '44', 'empty' => 'NULL'],
        ['id' => '980', 'kana' => 'を', 'num' => '88', 'empty' => 'NULL'],
        ['id' => '981', 'kana' => 'わ', 'num' => '44', 'empty' => 'NULL'],
        ['id' => '982', 'kana' => 'め', 'num' => '87', 'empty' => 'NULL'],
        ['id' => '983', 'kana' => 'す', 'num' => '7', 'empty' => 'NULL'],
        ['id' => '984', 'kana' => 'る', 'num' => '73', 'empty' => 'NULL'],
        ['id' => '985', 'kana' => 'ん', 'num' => '32', 'empty' => 'NULL'],
        ['id' => '986', 'kana' => 'ね', 'num' => '52', 'empty' => 'NULL'],
        ['id' => '987', 'kana' => 'よ', 'num' => '99', 'empty' => 'NULL'],
        ['id' => '988', 'kana' => 'ろ', 'num' => '52', 'empty' => 'NULL'],
        ['id' => '989', 'kana' => 'れ', 'num' => '90', 'empty' => 'NULL'],
        ['id' => '990', 'kana' => 'ら', 'num' => '43', 'empty' => 'NULL'],
        ['id' => '991', 'kana' => 'え', 'num' => '83', 'empty' => 'NULL'],
        ['id' => '992', 'kana' => 'る', 'num' => '55', 'empty' => 'NULL'],
        ['id' => '993', 'kana' => 'や', 'num' => '71', 'empty' => 'NULL'],
        ['id' => '994', 'kana' => 'つ', 'num' => '35', 'empty' => 'NULL'],
        ['id' => '995', 'kana' => 'い', 'num' => '56', 'empty' => 'NULL'],
        ['id' => '996', 'kana' => 'て', 'num' => '48', 'empty' => 'NULL'],
        ['id' => '997', 'kana' => 'ゆ', 'num' => '44', 'empty' => 'NULL'],
        ['id' => '998', 'kana' => 'か', 'num' => '33', 'empty' => 'NULL'],
        ['id' => '999', 'kana' => 'た', 'num' => '52', 'empty' => 'NULL'],
        ['id' => '1000', 'kana' => 'て', 'num' => '6', 'empty' => 'NULL'],
        ['id' => '1001', 'kana' => 'な', 'num' => '84', 'empty' => 'NULL'],
        ['id' => '1002', 'kana' => 'ち', 'num' => '10', 'empty' => 'NULL'],
        ['id' => '1003', 'kana' => 'た', 'num' => '44', 'empty' => 'NULL'],
        ['id' => '1004', 'kana' => 'せ', 'num' => '24', 'empty' => 'NULL'],
        ['id' => '1005', 'kana' => 'た', 'num' => '5', 'empty' => 'NULL'],
        ['id' => '1006', 'kana' => 'や', 'num' => '39', 'empty' => 'NULL'],
        ['id' => '1007', 'kana' => 'の', 'num' => '46', 'empty' => 'NULL'],
        ['id' => '1008', 'kana' => 'つ', 'num' => '36', 'empty' => 'NULL'],
        ['id' => '1009', 'kana' => 'め', 'num' => '69', 'empty' => 'NULL'],
        ['id' => '1010', 'kana' => 'も', 'num' => '33', 'empty' => 'NULL']
    ];
}