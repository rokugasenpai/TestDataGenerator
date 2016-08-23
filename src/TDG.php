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
 *
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
 * データベースはConfigクラスで生成接続したPDOを使用する。
 *
 * @package    TestDataGenerator
 */
class TDG
{
    const DEFAULT_JSON_CONFIG_FILEPATH = './config/tdg.json';
    const DEFAULT_YML_CONFIG_FILEPATH = './config/tdg.yml';

    const MESSEAGE_WEIGHTED_MASTERS_START = '重み付けマスタの作成を開始します。';
    const MESSEAGE_WEIGHTED_MASTERS_FINISH = '重み付けマスタの作成が完了しました。';
    const MESSEAGE_DATA_START = 'テストデータの生成を開始します。';
    const MESSEAGE_DATA_FINISH = 'テストデータの生成が完了しました。';
    const MESSEAGE_POST_PROC_START = '後処理を実行します。しばらくお待ちください。';
    const MESSEAGE_POST_PROC_FINISH = '後処理が完了しました。';

    /** @var Config 設定 */
    private $_config = NULL;

    /** @var string ベンチマーク用ストップウォッチ */
    private $_benchmark = '';

    /** @var string 呼び出し元 */
    private $_caller = '';

    /** @var WeightedArray[] 重み付けマスタ群 */
    private $_weighted_masters = [];

    /** @var resource データ出力用ファイルポインタ */
    private $_fp = NULL;


    /**
     * __construct
     *
     * 引数$config_filepathより設定をセットする。
     *
     * @param string $config_filepath (optional)
     */
    public function __construct($config_filepath='')
    {
        if (!is_string($config_filepath))
        {
            throw new TDGE(TDGE::MESSEAGE_INVALID_CONSTRUCTOR);
        }

        // ベンチマークで使われる。
        $bktr = debug_backtrace();
        $this->_caller = basename($bktr[0]['file']);
        if (isset($bktr[1]['file'])) $this->_caller = basename($bktr[1]['file']);
        if (isset($bktr[1]['function'])) $this->_caller = $bktr[1]['function'];
        if (strpos(PHP_OS, 'WIN') === 0)
        {
            $this->_caller = mb_convert_encoding($this->_caller, Util::SJIS, Util::UTF8);
        }

        $this->_check_and_set_env();

        // $configはファイルパスを想定。
        $this->_config = new Config($this->get_config($config_filepath));

        ini_set('memory_limit', $this->_config->memory_limit);
    }


    /**
     * __destruct
     */
    public function __destruct()
    {
        try
        {
            if (!is_null($this->_fp)) fclose($this->_fp);
        }
        catch (Exception $e)
        {
            // 何もしない。
        }
    }


    /**
     * main
     *
     * データの生成とファイル出力を行う。
     * 必要があれば前・後処理を行う。
     *
     * @param bool $need_stdout (optional)
     * @param bool $need_benchmark_file (optional)
     * @param bool $need_spec_file (optional)
     */
    public function main($need_stdout=TRUE, $need_benchmark_file=FALSE, $need_spec_file=FALSE)
    {
        if (!count($this->_config->record_rules))
        {
            throw new TDGE(TDGE::MESSEAGE_INVALID_MAIN);
        }

        $this->_benchmark = new Stopwatch();
        $this->_benchmark->logs = [];
        $event = $this->_benchmark->start($this->_caller);
        $this->_benchmark->logs[] = '開始: '
            . date('Y-m-d H:i:s', intval($event->getOrigin() / 1000));
        if ($need_stdout) Util::println(end($this->_benchmark->logs));

        if (count($this->_config->masters))
        {
            if ($need_stdout) Util::println(self::MESSEAGE_WEIGHTED_MASTERS_START);
            $this->create_weighted_master();
            if ($need_stdout) Util::println(self::MESSEAGE_WEIGHTED_MASTERS_FINISH);
        }

        $event = $this->_benchmark->lap($this->_caller);
        $this->_benchmark->logs[] = 'テストデータ生成前: '
            . date('Y-m-d H:i:s', intval($event->getOrigin() / 1000 + $event->getEndTime() / 1000));
        if ($need_stdout) Util::println(end($this->_benchmark->logs));

        if ($need_stdout) Util::println(self::MESSEAGE_DATA_START);
        $this->generate_data();
        if ($need_stdout) Util::println(self::MESSEAGE_DATA_FINISH);

        $event = $this->_benchmark->stop($this->_caller);
        $this->_benchmark->logs[] = '終了: '
            . date('Y-m-d H:i:s', intval($event->getOrigin() / 1000 + $event->getEndTime() / 1000));
        if ($need_stdout) Util::println(end($this->_benchmark->logs));

        $this->_benchmark->logs[] = '処理時間: ' . Util::s_to_hms($event->getDuration() / 1000);
        if ($need_stdout) Util::println(end($this->_benchmark->logs));

        if ($need_benchmark_file)
        {
            file_put_contents("./benchmark_{$this->_caller}.txt", implode(PHP_EOL, $this->_benchmark->logs));
        }

        if ($need_spec_file)
        {
            $this->_output_spec_file();
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
    public function get_config($config_filepath='')
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
     * create_weighted_master
     *
     * CSVの元マスタから重み付けマスタを作成する。
     */
    public function create_weighted_master()
    {
        foreach ($this->_config->masters as $master)
        {
            $filepath = $master[Config::IDX_MASTER_FILEPATH];
            $weight_column = $master[Config::IDX_MASTER_WEIGHT_COLUMN];
            $divisor = $master[Config::IDX_MASTER_WEIGHT_DIVISOR];
            $name = basename($filepath, Util::CSV_EXT);
            $this->_weighted_masters[$name] = Util::create_weighted_master($filepath, $weight_column, $divisor);

            if (!$this->_weighted_masters[$name])
            {
                throw new TDGE(TDGE::MESSEAGE_INVALID_MASTER, " => {$filepath}");
            }
        }

        foreach ($this->_config->record_rules as $rule)
        {
            if (!is_array($rule->master))
            {
                continue;
            }

            $m = $rule->master;
            $master_name = key($m);
            $column_name = $rule->master[$master_name];

            if (!array_key_exists($master_name, $this->_weighted_masters))
            {
                throw new TDGE(TDGE::MESSEAGE_MISSING_MASTER_NAME,
                    "{$master_name} => "
                    . Util::json_encode(array_keys($this->_weighted_masters), JSON_UNESCAPED_UNICODE));
            }
            $weighted_master = $this->_weighted_masters[$master_name];

            if (!in_array($column_name, $weighted_master->get_column_names()))
            {
                throw new TDGE(TDGE::MESSEAGE_MISSING_COLUMN_NAME,
                    "{$column_name} => "
                    . Util::json_encode($weighted_master->get_column_names(), JSON_UNESCAPED_UNICODE));
            }
        }
    }

    /**
     * generate_data
     *
     * データを行(レコード)単位で生成し、ファイル出力する。
     */
    public function generate_data()
    {
        $this->_fp = fopen($this->_config->output_filepath, 'w');

        for ($now_index = 0; $now_index < $this->_config->num_data; $now_index++)
        {
            $record = new Record(
                $now_index, $this->_config->record_rules, $this->_weighted_masters, $this->_config->need_stdout);

            $record_values = $record->get_values();

            if (!count($record_values))
            {
                throw new TDGE(TDGE::MESSEAGE_MISSING_DATA);
            }

            if (!$now_index && $this->_config->need_header)
            {
                Util::fputcsv($this->_fp, array_keys($record_values), $this->_config->need_null, $this->_config->eol,
                    $this->_config->charset, Util::UTF8);
            }

            if ($this->_config->charset != Util::UTF8)
            {
                Util::fputcsv($this->_fp, $record_values, $this->_config->need_null, $this->_config->eol,
                    $this->_config->charset, Util::UTF8);
            }
            else
            {
                Util::fputcsv($this->_fp, $record_values, $this->_config->need_null, $this->_config->eol);
            }
        }

        fclose($this->_fp);
        $this->_fp = NULL;
    }


    /**
     * _check_and_set_env
     *
     * PHPモジュールの確認、ini_set()など。
     */
    private function _check_and_set_env()
    {
        $require_modules = ['mbstring', 'bcmath'];
        foreach ($require_modules as $reqmod)
        {
            if (!extension_loaded($reqmod))
            {
                throw new TDGE(TDGE::MESSEAGE_MISSING_MODULE . $reqmod);
            }
        }

        setlocale(LC_ALL, 'ja_JP.UTF-8');
        bcscale(6);

        ini_set('date.timezone', 'Asia/Tokyo');
        ini_set('mbstring.language', 'neutral');
    }


    /**
     * _output_spec_file
     */
    private function _output_spec_file()
    {
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
        file_put_contents("./spec_{$this->_caller}.txt", implode(PHP_EOL, $specs));
    }
}
