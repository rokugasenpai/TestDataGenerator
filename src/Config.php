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
    const DEFAULT_OUTPUT_FILEPATH = 'tdg.csv';
    const IDX_MASTER_FILEPATH = 0;
    const IDX_MASTER_WEIGHT_COLUMN = 1;
    const IDX_MASTER_WEIGHT_DIVISOR = 2;

    /** @var int 生成データ数 */
    protected $num_data;

    /** @var Rule[] レコードルール */
    protected $record_rules = [];
    // キーがフィールド名、値がルールの連想配列

    /** @var string 出力ファイルパス */
    protected $output_filepath = self::DEFAULT_OUTPUT_FILEPATH;

    /** @var bool NULLフィールド値フラグ */
    protected $need_null = FALSE;
    // NULLを許可するか、空文字に変換するか

    /** @var array マスタ */
    protected $masters = [];

    /** @var string マスタ内NULL値 */
    protected $null_value = 'NULL';
    // 文字列データとして"NULL"があり得る場合は、ユニークな値を指定する。

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
        if (!array_key_exists('num_data', $config))
        {
            throw new TDGE(TDGE::MESSEAGE_INVALID_CONFIG, '生成データ数は必須です。');
        }

        foreach ($config as $name => $value)
        {
            $method = '_check_and_set_' . $name;
            // 動的にメソッドを呼び出す。
            $this->{$method}($value);
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
     * _check_and_set_need_null
     *
     * NULLフィールド値フラグを$need_nullプロパティにセットする。
     * ブールを想定している。
     *
     * @param bool $value
     */
    private function _check_and_set_need_null($value)
    {
        if (!Util::is_flexible_bool($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
        }

        $this->need_null = $value;
    }


    /**
     * _check_and_set_masters
     *
     * マスタを$mastersプロパティにセットする。
     *
     * @param string[] $value
     */
    private function _check_and_set_masters($value)
    {
        $caller = debug_backtrace()[1]['function'];
        if (!is_array($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', $caller));
            return;
        }

        $formed = [];
        foreach ($value as $index => $master)
        {
            $filepath = '';

            if (is_array($master))
            {
                // ファイルパスが無ければ失敗。
                if (!array_key_exists(self::IDX_MASTER_FILEPATH, $master)
                    || !is_string($master[self::IDX_MASTER_FILEPATH])
                    || !Util::check_ext($master[self::IDX_MASTER_FILEPATH], Util::CSV_EXT)
                    || !is_file($master[self::IDX_MASTER_FILEPATH]))
                {
                    $this->_set_error(str_replace('_check_and_set_', '', $caller)
                    . Util::json_encode($master, JSON_UNESCAPED_UNICODE));
                    return;
                }

                $filepath = $master[self::IDX_MASTER_FILEPATH];

                // 重み付けの処理がある場合。
                if (array_key_exists(self::IDX_MASTER_WEIGHT_COLUMN, $master)
                    && !is_array($master[self::IDX_MASTER_WEIGHT_COLUMN]))
                {
                    if (!Util::check_ext($filepath, Util::CSV_EXT)
                        || !strlen($master[self::IDX_MASTER_WEIGHT_COLUMN])
                        || (array_key_exists(self::IDX_MASTER_WEIGHT_DIVISOR, $master)
                        && !Util::is_numeric_uint($master[self::IDX_MASTER_WEIGHT_DIVISOR])))
                    {
                        $this->_set_error(str_replace('_check_and_set_', '', $caller)
                            . Util::json_encode($master, JSON_UNESCAPED_UNICODE));
                        return;
                    }

                    if (!array_key_exists(self::IDX_MASTER_WEIGHT_DIVISOR, $master))
                    {
                        $master[self::IDX_MASTER_WEIGHT_DIVISOR] = 1;
                    }
                }
            }
            else if (is_string($master))
            {
                $filepath = $master;

                if (!is_file($filepath)
                    || (!Util::check_ext($filepath, Util::CSV_EXT)))
                {
                    $this->_set_error(str_replace('_check_and_set_', '', $caller)
                        . " => {$master}");
                    return;
                }

                $master = [];
                $master[self::IDX_MASTER_FILEPATH] = $filepath;
            }
            else
            {
                $this->_set_error(str_replace('_check_and_set_', '', $caller));
                return;
            }

            $formed[$index] = $master;
        }

        $this->masters = $formed;
    }

    /**
     * _check_and_set_null_value
     *
     * 前・後処理用ファイル内NULL値を$null_valueプロパティにセットする。
     * 文字列を想定している。
     *
     * @param string $value
     */
    private function _check_and_set_null_value($value)
    {
        if (!is_string($value) || !strlen($value))
        {
            $this->_set_error(str_replace('_check_and_set_', '', __FUNCTION__));
            return;
        }

        $this->null_value = $value;
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
        if (!Util::is_flexible_bool($value))
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
        if (!Util::is_flexible_bool($value))
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