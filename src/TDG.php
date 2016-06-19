<?php
/**
 * TestDataGenerator
 *
 * Copyright 2016 rokugasenpai
 *
 * https://opensource.org/licenses/MIT The MIT License (MIT)
 * 本ツールのライセンスはMITライセンスです。
 * 無保証であることを承諾できる場合のみ、自由に複製・配布・修正してください。
 *
 * 本ツールはドキュメントを含め、利用者が日本語を扱えることを前提に開発されています。
 * This tool incluing documents is asuumed that user knows Japanese!
 *
 * 本ツールは、データベースに投入するテストデータを簡単に作成することを目的としています。
 * YAMLもしくはJSONの設定ファイルを元に、テストデータ用のCSVを出力します。
 * テストデータ出力の前後に、SQLもしくはCSVファイルによるSQLを実行できます。
 *
 * 使用できるデータベースはMySQL(MariaDB)のみです。
 * PHPは5.4以上、OSはWindows(7および10)、Linux(Centos6)で動作確認しています。
 *
 * 設定ファイルの書き方は下記を参照してください。
 * https://github.com/rokugasenpai/TestDataGenerator#設定ファイルの書き方
 */

namespace rokugasenpai\TestDataGenerator;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use rokugasenpai\TestDataGenerator\TDGException as TDGE;

set_error_handler(
    function ($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
);


/**
 * TDG
 *
 * 設定ファイルを読み込み、その情報を元にテストデータの生成、CSVファイルの出力を行う。
 * データベースを参照する必要がある場合は、本クラスで接続する。
 *
 * @package    TestDataGenerator
 */
class TDG
{
    const DEFAULT_JSON_CONFIG_FILEPATH = './config/tdg.json';
    const DEFAULT_YML_CONFIG_FILEPATH = './config/tdg.yml';

    const MESSEAGE_PRE_PROC_START = '前処理を実行します。しばらくお待ちください。';
    const MESSEAGE_PRE_PROC_FINISH = '前処理が完了しました。';
    const MESSEAGE_GENERATION_START = 'テストデータの生成を開始します。';
    const MESSEAGE_GENERATION_FINISH = 'テストデータの生成が完了しました。';
    const MESSEAGE_POST_PROC_START = '後処理を実行します。しばらくお待ちください。';
    const MESSEAGE_POST_PROC_FINISH = '後処理が完了しました。';

    /** @var Config 設定 */
    public $config = NULL;

    /** @var string[] 生成データ */
    public $data = [];


    /**
     * __construct
     *
     * 設定をファイルから読み取りセットする。
     *
     * @param string $config_filepath (optional)
     */
    public function __construct($config_filepath='', $benchmark=FALSE)
    {
        $this->_check_and_set_env();

        $this->config = new Config($this->get_config($config_filepath));

        if (!is_null($this->config->db))
        {
            $this->_check_env_db();
        }
    }


    /**
     * main
     *
     * データの生成とファイル出力を行う。
     * 必要があれば前・後処理を行う。
     *
     * @param string $config_filepath (optional)
     */
    public function main($is_silent=FALSE)
    {
        if (count($this->config->pre_proc))
        {
            if (isset($GLOBALS['benchmark']) && $GLOBALS['benchmark'])
            {
                $GLOBALS['benchmark']->lap('', $GLOBALS['test_case'] . '::' . $GLOBALS['test_method']);
            }

            if (!$is_silent) Util::println(self::MESSEAGE_PRE_PROC_START);
            $this->execute_pre_proc($this->config->pre_proc);
            if (!$is_silent) Util::println(self::MESSEAGE_PRE_PROC_FINISH);
        }

        if (isset($GLOBALS['benchmark']) && $GLOBALS['benchmark'])
        {
            $GLOBALS['benchmark']->lap('', $GLOBALS['test_case'] . '::' . $GLOBALS['test_method']);
        }

        if (!$is_silent) Util::println(self::MESSEAGE_GENERATION_START);
        $this->generate_data();

        if (isset($GLOBALS['benchmark']) && $GLOBALS['benchmark'])
        {
            $GLOBALS['benchmark']->lap('', $GLOBALS['test_case'] . '::' . $GLOBALS['test_method']);
        }

        $this->save_data();
        if (!$is_silent) Util::println(self::MESSEAGE_GENERATION_FINISH);

        if (isset($GLOBALS['benchmark']) && $GLOBALS['benchmark'])
        {
            $GLOBALS['benchmark']->lap('', $GLOBALS['test_case'] . '::' . $GLOBALS['test_method']);
        }

        if (count($this->config->post_proc))
        {
            if (!$is_silent) Util::println(self::MESSEAGE_POST_PROC_START);
            $this->execute_post_proc($this->config->post_proc);
            if (!$is_silent) Util::println(self::MESSEAGE_POST_PROC_FINISH);
            if (isset($GLOBALS['benchmark']) && $GLOBALS['benchmark'])
            {
                $GLOBALS['benchmark']->lap('', $GLOBALS['test_case'] . '::' . $GLOBALS['test_method']);
            }
        }

        if (isset($GLOBALS['benchmark']) && $GLOBALS['benchmark'])
        {
            $this->_output_benchmark($GLOBALS['benchmark'], $GLOBALS['test_case'],
                $GLOBALS['test_method'], $GLOBALS['benchmark_filepath'], $GLOBALS['spec_filepath']);
        }
    }


    /**
     * get_config
     *
     * 引数よりファイルから取得した設定を配列で返す。
     * JSONとYAMLに対応しており、JSON > YAMLの優先度で取得する。
     * 引数無しでtdg.jsonとtdg.ymlがあった場合は、JSONの設定が使われる。
     *
     * @param string $config_filepath (optional)
     * @return mixed[]
     */
    protected function get_config($config_filepath='')
    {
        $config = [];

        if ($config_filepath === '')
        {
            if (is_file(self::DEFAULT_JSON_CONFIG_FILEPATH))
            {
                $config = Util::get_data_by_json_file(self::DEFAULT_JSON_CONFIG_FILEPATH);
            }
            else if (is_file(self::DEFAULT_YML_CONFIG_FILEPATH))
            {
                $config = Util::get_data_by_yml_file(self::DEFAULT_YML_CONFIG_FILEPATH);
            }
            else
            {
                throw new TDGE(TDGE::MESSEAGE_MISSING_CONFIG);
            }
        }
        else
        {
            if (is_file($config_filepath))
            {
                if (Util::check_ext($config_filepath, Util::JSON_EXT))
                {
                    $config = Util::get_data_by_json_file($config_filepath);
                }
                else if (Util::check_ext($config_filepath, Util::YML_EXT))
                {
                    $config = Util::get_data_by_yml_file($config_filepath);
                }
                else
                {
                    throw new TDGE(TDGE::MESSEAGE_INVALID_CONFIG, $config_filepath);
                }
            }
            else
            {
                throw new TDGE(TDGE::MESSEAGE_MISSING_CONFIG, $config_filepath);
            }
        }

        if (is_string($config))
        {
            throw new TDGE(TDGE::MESSEAGE_INVALID_CONFIG, "{$config_filepath} => {$config}");
        }

        return $config;
    }


    /**
     * execute_pre_proc
     *
     * 指定された前処理SQLを実行する。
     *
     * @param string[] $procs
     */
    protected function execute_pre_proc($procs)
    {
        $this->execute_proc($procs, TDGE::MESSEAGE_INVALID_PRE_PROC_FILE, TDGE::MESSEAGE_INVALID_PRE_PROC_SQL);
    }


    /**
     * execute_post_proc
     *
     * 指定された後処理SQLを実行する。
     *
     * @param string[] $procs
     */
    protected function execute_post_proc($procs)
    {
        $this->execute_proc($procs, TDGE::MESSEAGE_INVALID_POST_PROC_FILE, TDGE::MESSEAGE_INVALID_POST_PROC_SQL);
    }


    /**
     * execute_proc
     *
     * 指定された前・後処理SQLを実行する。
     * 引数よりSQLまたはCSVのファイルパスを取得し、
     * CSVだったら、重み付けの処理をしてバルクインサートするSQLへ変換する。
     * mysqlコマンドを呼び出すことでSQLを実行する。
     * 引数に、前・後処理に対応するエラー文言を渡す。
     *
     * @param string[] $procs
     * @param string $error_message_file
     * @param string $error_message_sql
     */
    protected function execute_proc($procs, $error_message_file, $error_message_sql)
    {
        foreach ($procs as $proc)
        {
            $filepath = $proc[Config::IDX_PROC_FILEPATH];
            $unique_columns = [];
            $sum_columns = [];

            if (Util::check_ext($filepath, Util::CSV_EXT))
            {
                if (array_key_exists(Config::IDX_PROC_WEIGHT_COLUMN, $proc)
                    && !is_array($proc[Config::IDX_PROC_WEIGHT_COLUMN]))
                {
                    copy($filepath, "{$filepath}.bak");
                    $weight_column = $proc[Config::IDX_PROC_WEIGHT_COLUMN];
                    $weighted_file = Util::create_weighted_csv(
                        $this->config->num_data, $filepath, '', $weight_column, 10000);

                    if (!$weighted_file)
                    {
                        throw new TDGE($error_message_file, " => {$filepath}");
                    }
                }
                else
                {
                    if (array_key_exists(Config::IDX_PROC_UNIQUE_COLUMNS, $proc))
                    {
                        $unique_columns = $proc[Config::IDX_PROC_UNIQUE_COLUMNS];
                    }
                    if (array_key_exists(Config::IDX_PROC_SUM_COLUMNS, $proc))
                    {
                        $sum_columns = $proc[Config::IDX_PROC_SUM_COLUMNS];
                    }
                }

                $sql_file = Util::csv_to_bulk_insert($weighted_file, '', '', [],
                    TRUE, $this->config->need_null, $this->config->proc_null_value,
                    $this->config->eol, $this->config->num_records_per_sql,
                    $unique_columns, $sum_columns,
                    $this->config->proc_head_sql, $this->config->proc_tail_sql);

                if (!$sql_file)
                {
                    throw new TDGE($error_message_file, " => {$weighted_file}");
                }
            }
            else
            {
                $sql_file = $filepath;
            }

            // mysqlコマンドにパスワードが入ってると標準エラーが出る場合があるので対応。
            $stderr_to_null = '2> /dev/null';
            if (strpos(PHP_OS, 'WIN') === 0) $stderr_to_null = '2> nul';
            exec("mysql -h {$this->config->db_host} -P {$this->config->db_port} "
                . "-u {$this->config->db_user} -p\"{$this->config->db_pass}\" {$this->config->db_name} "
                . "< {$sql_file} {$stderr_to_null}", $discard, $code);
            if ($code)
            {
                throw new TDGE($error_message_sql, "{$sql_file} => {$output}");
            }
        }
    }


    /**
     * generate_data
     *
     * データを行(レコード)単位で生成し、$dataプロパティに格納する。
     */
    protected function generate_data()
    {
        for ($now_index = 0; $now_index < $this->config->num_data; $now_index++)
        {
            Record::generate($now_index, $this->config->num_data,
                $this->config->db, $this->config->sql, $this->config->num_records_per_sql,
                $this->config->record_rules, $this->config->need_stdout);

            $this->data[] = Record::get_data();
        }

        if (!count($this->data))
        {
            throw new TDGE(TDGE::MESSEAGE_MISSING_DATA);
        }
    }


    /**
     * save_data
     *
     * $dataプロパティの生成データを、ファイルに保存する。
     * I/O負荷を減らすため、メモリに1行毎にフラッシュする。
     * 設定により文字コードの変換も行う。
     */
    protected function save_data()
    {
        $fp = NULL;
        try
        {
            if (Util::check_ext($this->config->output_filepath, Util::CSV_EXT))
            {
                // まずはメモリを確保
                $fp = fopen("php://temp/maxmemory:{$this->config->output_memory_limit}", 'r+');

                // 必要に応じてヘッダ書き出し
                if ($this->config->need_header)
                {
                    Util::fputcsv($fp, array_keys($this->data[0]), $this->config->need_null, $this->config->eol,
                        $this->config->charset, Util::UTF8);
                }

                // レコードごとに書き出し
                foreach ($this->data as $record)
                {
                    if ($this->config->charset != Util::UTF8)
                    {
                        Util::fputcsv($fp, $record, $this->config->need_null, $this->config->eol,
                            $this->config->charset, Util::UTF8);
                    }
                    else
                    {
                        Util::fputcsv($fp, $record, $this->config->need_null, $this->config->eol);
                    }
                }

                rewind($fp);
                // 最終的に、ファイル出力する。
                file_put_contents($this->config->output_filepath, stream_get_contents($fp));
                @fclose($fp);
            }
            else if (Util::check_ext($this->config->output_filepath, Util::SQL_EXT))
            {
                Util::csv_to_bulk_insert($this->data, $this->config->output_filepath,
                    basename($this->config->output_filepath, Util::SQL_EXT), [],
                    FALSE, $this->config->need_null, $this->config->proc_null_value,
                    $this->config->eol, $this->config->num_records_per_sql);
            }
        }
        catch (\ErrorException $ee)
        {
            @fclose($fp);
            throw new TDGE(TDGE::MESSEAGE_FILE_OUTPUT, $ee->getMessage());
        }
    }


    /**
     * _check_and_set_env
     */
    private function _check_and_set_env()
    {
        // 必要なPHPモジュールが読み込まれているか。
        $require_modules = ['mbstring', 'pdo_mysql', 'bcmath'];
        foreach ($require_modules as $reqmod)
        {
            if (!extension_loaded($reqmod))
            {
                throw new TDGE(TDGE::MESSEAGE_MISSING_MODULE . $reqmod);
            }
        }

        bcscale(6);

        ini_set('date.timezone', 'Asia/Tokyo');
        ini_set('mbstring.language', 'neutral');
        ini_set('memory_limit', '1G');

        if (isset($GLOBALS['benchmark']) && $GLOBALS['benchmark'])
        {
            $GLOBALS['benchmark'] = new Stopwatch();
            $GLOBALS['benchmark']->start('', $GLOBALS['test_case'] . '::' . $GLOBALS['test_method']);
        }
    }


    /**
     * _check_env_db
     */
    private function _check_env_db()
    {
        // MySQLの文字コード設定が正しいか。
        $mysql_setting_normal = [
            'character_set_server' => 'utf8',
            'collation_server' => 'utf8_general_ci'
        ];
        $mysql_setting_error = [];
        $stderr_to_null = '2> /dev/null';
        if (strpos(PHP_OS, 'WIN') === 0) $stderr_to_null = '2> nul';
        foreach ($mysql_setting_normal as $k => $v)
        {
            $result = exec("mysql -h {$this->config->db_host} -P {$this->config->db_port} "
                . "-u {$this->config->db_user} -p\"{$this->config->db_pass}\" {$this->config->db_name} "
                . "-e\"show variables like '{$k}'\" {$stderr_to_null}", $discard, $code);
            if (strpos($result, $v) === FALSE)
            {
                $mysql_setting_error[] = "{$k} = {$v}";
            }
        }
        if ($code || count($mysql_setting_error))
        {
            throw new TDGE(TDGE::MESSEAGE_INVALID_MYSQL_SETTING, implode(PHP_EOL, $mysql_setting_error));
        }
    }


    /**
     * _output_benchmark
     * 
     * @param StopWatch $benchmark
     * @param string $test_case
     * @param string $test_method
     * @param string $benchmark_filepath
     * @param string $spec_filepath
     */
    private function _output_benchmark($benchmark, $test_case, $test_method, $benchmark_filepath, $spec_filepath)
    {
        $event = $benchmark->stop('', $test_case . '::' . $test_method);
        $serializer = new Serializer([new ObjectNormalizer()], [new XmlEncoder()]);
        file_put_contents($benchmark_filepath, $serializer->serialize($event, 'xml'));

        // ベンチマークの指標としてPHPバージョン・マシンスペック情報を収集する。
        $specs = [];
        exec('php -v', $output);
        foreach ($output as $phpver)
        {
            $specs[] = $phpver;
        }
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $names = [
                'OS 名',
                'OS バージョン',
                'システム製造元',
                'システム モデル',
                'システムの種類',
                'プロセッサ',
                'Intel',
                'AMD',
                'メモリ'
            ];
            exec('systeminfo', $output);
            foreach ($output as $spec)
            {
                if (strpos(PHP_OS, 'WIN') === 0 && strpos(exec('chcp'), '932') !== FALSE)
                {
                    $spec = mb_convert_encoding($spec, Util::UTF8, Util::SJIS);
                }
                foreach ($names as $name)
                {
                    if (strpos($spec, $name) !== FALSE)
                    {
                        $specs[] = $spec;
                    }
                }
            }
        }
        else
        {
            $files = [
                '/proc/cpuinfo' => [
                    'processor',
                    'model name'
                ],
                '/proc/meminfo' => [
                    'MemTotal',
                    'MemFree',
                    'Buffers',
                    'Cached',
                    'SwapTotal',
                    'SwapFree'
                ],
                '/etc/issue' => [
                    'release'
                ]
            ];
            foreach ($files as $file => $names)
            {
                exec("cat {$file}", $output);
                foreach ($output as $spec)
                {
                    foreach ($names as $name)
                    {
                        if (strpos($spec, $name) !== FALSE)
                        {
                            $specs[] = $spec;
                        }
                    }
                }
            }
        }
        file_put_contents($spec_filepath, implode(PHP_EOL, $specs));
    }
}
