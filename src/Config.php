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

use rokugasenpai\TestDataGenerator\TDGException as TDGE;


/**
 * Config
 *
 * 設定値の検証と提供を目的としたクラス。
 *
 * @package    TestDataGenerator
 */
class Config extends TDGBase
{
    const DEFAULT_PROC_DIR = './proc/';
    const DEFAULT_OUTPUT_FILEPATH = 'tdg.csv';
    const IDX_PROC_FILEPATH = 0;
    const IDX_PROC_WEIGHT_COLUMN = 1;
    const IDX_PROC_WEIGHT_DIVISOR = 2;
    const IDX_PROC_UNIQUE_COLUMNS = 1;
    const IDX_PROC_SUM_COLUMNS = 2;

    /** @var int 生成データ数 */
    protected $num_data;

    /** @var Rule[] レコードルール */
    protected $record_rules;
    // キーがフィールド名、値がルールの連想配列

    /** @var string 出力ファイルパス */
    protected $output_filepath = self::DEFAULT_OUTPUT_FILEPATH;

    /** @var string DBパスワード */
    protected $db_pass = '';

    /** @var string SQL */
    protected $sql = '';

    /** @var bool NULLフィールド値フラグ */
    protected $need_null = FALSE;
    // NULLを許可するか、空文字に変換するか

    /** @var int 1SQLあたりの件数 */
    protected $num_records_per_sql = 1000;
    // 前・後処理のバルクインサート数、およびデータ生成時の1回のクエリで取得する件数

    /** @var string[] 前処理用ファイル */
    protected $pre_proc = [];

    /** @var string[] 後処理用ファイル */
    protected $post_proc = [];

    /** @var string 前・後処理用ファイル内NULL値 */
    protected $proc_null_value = 'NULL';
    // 文字列データとして"NULL"があり得る場合は、ユニークな値を指定する。

    /** @var string 前・後処理用先頭SQL */
    protected $proc_head_sql = '';
    // SET文などを先頭に挿入するのが目的。
    // CSVファイルによるバルクインサート時のみ有効。

    /** @var string 前・後処理用末尾SQL */
    protected $proc_tail_sql = '';
    // OPTIMIZE TABLE文などを末尾に挿入するのが目的。
    // CSVファイルによるバルクインサート時のみ有効。

    /** @var string DB名 */
    protected $db_name = 'tdg';

    /** @var string DBユーザー名 */
    protected $db_user = 'root';

    /** @var string DBホストアドレス */
    protected $db_host = 'localhost';

    /** @var string DBホストポート */
    protected $db_port = 3306;

    /** @var bool ヘッダ出力フラグ */
    protected $need_header = FALSE;

    /** @var bool 標準出力フラグ */
    protected $need_stdout = FALSE;

    /** @var string 出力ファイル改行コード */
    protected $eol = PHP_EOL;

    /** @var string 出力ファイル文字コード */
    protected $charset = Util::UTF8;
    // 単位はMB、Util::fputcsv()で負荷対策のためメモリに書き込む時に使用する。

    /** @var int php.iniのmemory_limit */
    protected $memory_limit = '1G';


    /**
     * __construct
     *
     * @param array $config
     */
    public function __construct($config)
    {
        parent::__construct($this);

        $this->_check_and_set_props($config);
    }


    /**
     * _check_and_set_props
     *
     * 引数の設定の配列を順次チェックしプロパティにセットする。
     * 設定に問題があったエラー文言を貯めこみ、途中で止めずに最後に例外を投げる。
     *
     * @param array $config
     */
    protected function _check_and_set_props($config)
    {

        if (!array_key_exists('num_data', $config) || !array_key_exists('record_rules', $config))
        {
            throw new TDGE(TDGE::MESSEAGE_INVALID_CONFIG, '生成データ数とレコードルールは必須です。');
        }

        foreach ($config as $name => $value)
        {
            $method = '_check_and_set_' . $name;
            // 動的にメソッドを呼び出す。
            $this->{$method}($value);
        }

        if (strlen($this->sql) || count($this->pre_proc) || count($this->post_proc))
        {
            if (!strlen($this->db_host) || !strlen($this->db_port)|| !strlen($this->db_name)
                || !strlen($this->db_user) || !strlen($this->db_pass))
            {
                throw new TDGE(TDGE::MESSEAGE_INVALID_CONFIG, 'DB接続情報が不足しています。');
            }
        }

        if ($this->_exists_error())
        {
            throw new TDGE(TDGE::MESSEAGE_INVALID_CONFIG, PHP_EOL . implode(PHP_EOL, $this->_get_errors()));
        }
    }


    /**
     * _check_and_set_num_data
     *
     * 生成データ数を$num_dataプロパティにセットする。
     * 正の整数を想定している。
     *
     * @param mixed $value
     */
    private function _check_and_set_num_data($value)
    {
        if (!Util::is_numeric_uint($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $this->num_data = $value;
    }


    /**
     * _check_and_set_record_rules
     *
     * レコードルールを$record_ruleプロパティにセットする。
     * 問題があっても最後まで処理し、エラーを貯めこむ。
     *
     * @param array $value
     */
    private function _check_and_set_record_rules($value)
    {
        if (!is_array($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        foreach ($value as $field_name => $rules)
        {
            if (!is_array($rules) || !is_string($field_name) || !strlen($field_name))
            {
                $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            }
            else
            {
                $rules = new Rule($rules, array_keys($value));
                if ($rules->_exists_error())
                {
                    $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__) . ' => '
                        . PHP_EOL . implode(PHP_EOL, $rules->_get_errors()));
                }
                else
                {
                    // こういう時に破壊的代入ができるのでPHPうれしい
                    $value[$field_name] = $rules;
                }
            }
        }

        // 1つでもエラーがあったらダメ
        if ($this->_exists_error(str_replace('_check_and_set_', '', __FUNCTION__)))
        {
            return;
        }

        $this->record_rules = $value;
    }


    /**
     * _check_and_set_output_filepath
     *
     * 出力ファイルパスを$output_filepathプロパティにセットする。
     * 実際にファイル生成→削除を行い、有効なファイルパスであることを確認する。
     *
     * @param string $value
     */
    private function _check_and_set_output_filepath($value)
    {
        if (!is_string($value) || !strlen($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        try
        {
            touch($value);
            unlink($value);

            $this->output_filepath = $value;
        }
        catch (\ErrorException $ee)
        {
            throw new TDGE(TDGE::MESSEAGE_INVALID_OUTPUT_FILEPATH, $ee->getMessage());
        }
    }


    /**
     * _check_and_set_db_pass
     *
     * DBパスワードを$db_passプロパティにセットする。
     * 文字列を想定している。
     *
     * @param string $value
     */
    private function _check_and_set_db_pass($value)
    {
        if (!is_string($value) || !strlen($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $this->db_pass = $value;
    }


    /**
     * _check_and_set_sql
     *
     * SQLを$sqlプロパティにセットする。
     * SELECT文かをチェックしている。
     *
     * @param string $value
     */
    private function _check_and_set_sql($value)
    {
        if (!is_string($value) || strpos(strtolower($value), 'select') !== 0)
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $this->sql = $value;
    }


    /**
     * _check_and_set_need_null
     *
     * NULLフィールド値フラグを$need_nullプロパティにセットする。
     * ブールを想定している。
     *
     * @param bool $value
     */
    private function _check_and_set_need_null($value)
    {
        if (!is_bool($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
        }

        $this->need_null = $value;
    }


    /**
     * _check_and_set_num_records_per_sql
     *
     * 1SQLあたりの件数を$num_records_per_sqlプロパティにセットする。
     * 生成データ数より多くないかチェックしている。
     *
     * @param mixed $value
     */
    private function _check_and_set_num_records_per_sql($value)
    {
        if (!Util::is_numeric_uint($value) || $value > $this->num_data)
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $this->num_records_per_sql = $value;
    }


    /**
     * _check_and_set_pre_proc
     *
     * 前処理用ファイルを$pre_procプロパティにセットする。
     *
     * @param string[] $value
     */
    private function _check_and_set_pre_proc($value)
    {
        $this->pre_proc = $this->_common_proc($value);
    }


    /**
     * _check_and_set_post_proc
     *
     * 後処理用ファイルを$post_procプロパティにセットする。
     *
     * @param string[] $value
     */
    private function _check_and_set_post_proc($value)
    {
        $this->post_proc = $this->_common_proc($value);
    }


    /**
     * _common_proc
     *
     * 前・後処理用ファイル共通処理。
     * 各ファイルが存在するか、適当な拡張子かをチェックしている。
     * 引数にファイルパス以外の値があれば、
     * 重み付けカラムの数値をもとにしたデータ生成、特定カラムの重複除去・集計
     * といった処理も行う。
     *
     * @param string[] $value
     * @return string[]
     */
    private function _common_proc($value)
    {
        $caller = debug_backtrace()[1]['function'];
        if (!is_array($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', $caller));
            return;
        }

        $formed = [];
        foreach ($value as $index => $proc)
        {
            $filepath = '';
            $unique_columns = [];
            $sum_colums = [];

            if (is_array($proc))
            {
                // ファイルパスが無ければ失敗。
                if (!array_key_exists(self::IDX_PROC_FILEPATH, $proc)
                    || !is_string($proc[self::IDX_PROC_FILEPATH])
                    || (!Util::check_ext($proc[self::IDX_PROC_FILEPATH], Util::CSV_EXT)
                        && !Util::check_ext($proc[self::IDX_PROC_FILEPATH], Util::SQL_EXT))
                    || !is_file($proc[self::IDX_PROC_FILEPATH]))
                {
                    $this->_set_error(str_replace('_check_and_set_', '', $caller)
                    . Util::json_encode($proc, JSON_UNESCAPED_UNICODE));
                    return;
                }

                $filepath = $proc[self::IDX_PROC_FILEPATH];

                // 重み付けの処理がある場合。
                if (array_key_exists(self::IDX_PROC_WEIGHT_COLUMN, $proc)
                    && !is_array($proc[self::IDX_PROC_WEIGHT_COLUMN]))
                {
                    if (!Util::check_ext($filepath, Util::CSV_EXT)
                        || !strlen($proc[self::IDX_PROC_WEIGHT_COLUMN])
                        || (array_key_exists(self::IDX_PROC_WEIGHT_DIVISOR, $proc)
                        && !Util::is_numeric_uint($proc[self::IDX_PROC_WEIGHT_DIVISOR])))
                    {
                        $this->_set_error(str_replace('_check_and_set_', '', $caller)
                            . Util::json_encode($proc, JSON_UNESCAPED_UNICODE));
                        return;
                    }

                    if (!array_key_exists(self::IDX_PROC_WEIGHT_DIVISOR, $proc))
                    {
                        $proc[self::IDX_PROC_WEIGHT_DIVISOR] = 1;
                    }
                }
                // 重複除去・集計の処理がある場合。
                else
                {
                    $unique_columns = [];
                    $sum_colums = [];

                    if (!Util::check_ext($filepath, Util::CSV_EXT)
                        && array_key_exists(self::IDX_PROC_UNIQUE_COLUMNS, $proc)
                        && is_array($proc[self::IDX_PROC_UNIQUE_COLUMNS]))
                    {
                        $unique_columns = $proc[self::IDX_PROC_UNIQUE_COLUMNS];
                    }

                    if (!Util::check_ext($filepath, Util::CSV_EXT)
                        && array_key_exists(self::IDX_PROC_SUM_COLUMNS, $proc)
                        && is_array($proc[self::IDX_PROC_SUM_COLUMNS]))
                    {
                        $sum_colums = $proc[self::IDX_PROC_SUM_COLUMNS];
                    }

                    if (!count($unique_columns) && !count($sum_columns))
                    {
                        $this->_set_error(str_replace('_check_and_set_', '', $caller)
                            . Util::json_encode($proc, JSON_UNESCAPED_UNICODE));
                        return;
                    }

                    $proc = [];
                    $proc[self::IDX_PROC_FILEPATH] = $filepath;
                    $proc[self::IDX_PROC_UNIQUE_COLUMNS] = $unique_columns;
                    $proc[self::IDX_PROC_SUM_COLUMNS] = $sum_colums;
                }
            }
            else if (is_string($proc))
            {
                $filepath = $proc;

                if (!is_file($filepath)
                    || (!Util::check_ext($filepath, Util::SQL_EXT)
                    && !Util::check_ext($filepath, Util::CSV_EXT)))
                {
                    $this->_set_error(str_replace('_check_and_set_', '', $caller)
                        . " => {$proc}");
                    return;
                }

                $proc = [];
                $proc[self::IDX_PROC_FILEPATH] = $filepath;
            }
            else
            {
                $this->_set_error(str_replace('_check_and_set_', '', $caller));
                return;
            }

            $formed[$index] = $proc;
        }

        return $formed;
    }

    /**
     * _check_and_set_proc_null_value
     *
     * 前・後処理用ファイル内NULL値を$proc_null_valueプロパティにセットする。
     * 文字列を想定している。
     *
     * @param string $value
     */
    private function _check_and_set_proc_null_value($value)
    {
        if (!is_string($value) || !strlen($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $this->proc_null_value = $value;
    }


    /**
     * _check_and_set_proc_head_sql
     *
     * 前・後処理用先頭SQLを$proc_head_sqlプロパティにセットする。
     * 文字列を想定している。
     *
     * @param string $value
     */
    private function _check_and_set_proc_head_sql($value)
    {
        if (!is_string($value) || !strlen($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $this->proc_head_sql = $value;
    }


    /**
     * _check_and_set_proc_tail_sql
     *
     * 前・後処理用末尾SQLを$proc_tail_sqlプロパティにセットする。
     * 文字列を想定している。
     *
     * @param string $value
     */
    private function _check_and_set_proc_tail_sql($value)
    {
        if (!is_string($value) || !strlen($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $this->proc_tail_sql = $value;
    }


    /**
     * _check_and_set_db_name
     *
     * DB名を$db_nameプロパティにセットする。
     * 文字列を想定している。
     *
     * @param string $value
     */
    private function _check_and_set_db_name($value)
    {
        if (!is_string($value) || !strlen($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $this->db_name = $value;
    }


    /**
     * _check_and_set_db_user
     *
     * DBユーザー名を$db_userプロパティにセットする。
     * 文字列を想定している。
     *
     * @param string $value
     */
    private function _check_and_set_db_user($value)
    {
        if (!is_string($value) || !strlen($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $this->db_user = $value;
    }


    /**
     * _check_and_set_db_host
     *
     * DBホストアドレスを$db_hostプロパティにセットする。
     * 文字列を想定している。
     *
     * @param string $value
     */
    private function _check_and_set_db_host($value)
    {
        if (!is_string($value) || !strlen($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $this->db_host = $value;
    }


    /**
     * _check_and_set_db_port
     *
     * DBホストポートを$db_portプロパティにセットする。
     * 1～65536の整数かチェックしている。
     *
     * @param mixed $value
     */
    private function _check_and_set_db_port($value)
    {
        if (!Util::is_numeric_uint($value) || $value > 65536)
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $this->db_port = intval($value);
    }


    /**
     * _check_and_set_need_header
     *
     * ヘッダ出力フラグを$need_headerプロパティにセットする。
     * ブールを想定している。
     *
     * @param bool $value
     */
    private function _check_and_set_need_header($value)
    {
        if (!is_bool($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $this->need_header = $value;
    }


    /**
     * _check_and_set_need_stdout
     *
     * 標準出力フラグを$need_stdoutプロパティにセットする。
     * ブールを想定している。
     *
     * @param bool $value
     */
    private function _check_and_set_need_stdout($value)
    {
        if (!is_bool($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
        }

        $this->need_stdout = $value;
    }


    /**
     * _check_and_set_eol
     *
     * 出力ファイル改行コードを$eolプロパティにセットする。
     * 改行コードにあたるかチェックしている。
     *
     * @param string $value
     */
    private function _check_and_set_eol($value)
    {
        if ($value !== "\n" && $value !== "\r\n" && $value !== "\r")
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
        }

        $this->eol = $value;
    }


    /**
     * _check_and_set_charset
     *
     * 出力ファイル文字コードを$charsetプロパティにセットする。
     * Shift-JISみたいないい加減な指定だった場合、修正している。
     * mb_list_encodings()にある文字コードかチェックしている。
     *
     * @param string $value
     */
    private function _check_and_set_charset($value)
    {
        if (!is_string($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $value = Util::normalize_charset($value);
        if (!$value || in_array($value, mb_list_encodings()))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $this->charset = $value;
    }


    /**
     * _check_and_set_memory_limit
     *
     * php.iniのmemory_limitを$memory_limitプロパティにセットする。
     * 2桁までならG、超えるならMとする。
     *
     * @param mixed $value
     */
    private function _check_and_set_memory_limit($value)
    {
        if (intval($value) <= 0)
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $unit = '';
        if (intval($value) < 100) $unit = 'G';
        else $unit = 'M';

        $this->memory_limit = intval($value) . $unit;
    }
}