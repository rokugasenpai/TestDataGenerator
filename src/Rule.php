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


/**
 * Rule
 *
 * 生成されるデータのレコード単位でのルールを扱う。
 * ルール値は_の付かないプロパティに格納する。
 * 数値範囲・日時範囲・パターンについては、WeightedArrayクラスを使い重み付けを行う。
 *
 * @package    TestDataGenerator
 */
class Rule extends TDGBase
{
    const MESSAGE_NOT_ARRAY = '配列(オブジェクト)ではありません。';
    const MESSAGE_MALFORMED_ARRAY = '配列(オブジェクト)の形式が正しくありません。';
    const MESSAGE_INVALID_LENGTH = '文字列の長さは正の整数で指定してください。';
    const MESSAGE_NOT_UINT = '正の整数を指定してください。';
    const MESSAGE_INVALID_WEIGHT = '数値で重み付けを指定してください。';
    const MESSAGE_INVALID_RANGE = '範囲の値は直前の値より大きくなるよう指定してください。';
    const MESSAGE_NOT_UINT_RANGE = '正の整数で範囲の値を指定してください。';
    const MESSAGE_NOT_NUMERIC_RANGE = '数値で範囲の値を指定してください。';
    const MESSAGE_NOT_TIMESTAMP_RANGE
        = 'DateTimeクラスで扱える日付文字列またはUNIXタイムスタンプで扱える数値の文字列で範囲の値を指定してください。';
    const MESSAGE_INVALID_PATTERN_NAME = 'Patternクラスに存在するパターン配列名を指定してください。';
    const MESSAGE_NOT_STRING = '文字列を指定してください。';
    const MESSAGE_INVALID_FIELD_NAME = '存在するフィールド名を指定してください。';
    const MESSAGE_INVALID_STATEMENTS
        = 'コードで問題が発生しました。構文とreturnで値を返しているかを確認してください。';
    const MESSAGE_INVALID_TOTAL_LENGTH = '全体の文字数はパターン配列の文字数の合計以上にしてください。';

    const TYPE_UINT = 'uint';
    const TYPE_UINT_RANGE = 'uint_range';
    const TYPE_NUMERIC_RANGE = 'numeric_range';
    const TYPE_DATETIME_RANGE = 'datetime_range';
    const TYPE_PATTERN = 'pattern';
    const TYPE_DB_COLUMN = 'db_column';
    const TYPE_CODE = 'code';

    const IDX_LOWER = 0;
    const IDX_HIGHER = 1;
    const IDX_WEIGHT = 2;
    const IDX_INTERVAL = 3;
    const IDX_PATTERN = 0;
    const IDX_LENGTH = 1;
    const IDX_PATTERN_ELEMENT = 0;
    const IDX_PATTERN_WEIGHT = 1;

    const KEY_LOWER = 'lower';
    const KEY_HIGHER = 'higher';
    const KEY_WEIGHT = 'weight';
    const KEY_INTERVAL = 'interval';
    const KEY_PATTERN = 'pattern';
    const KEY_LENGTH = 'length';
    const KEY_PATTERN_ELEMENT = 'element';
    const KEY_PATTERN_WEIGHT = 'weight';


    /** @var int フィールド値シーケンス初期値 */
    protected $seq = NULL;

    /** @var array フィールド値数値範囲 */
    protected $number = NULL;

    /** @var array フィールド値日時範囲 */
    protected $datetime = NULL;

    /** @var array フィールド値タイムスタンプ範囲 */
    protected $timestamp = NULL;

    /** @var array フィールド値文字数範囲 */
    protected $length = NULL;

    /** @var array フィールド値パターン配列 */
    protected $pattern = NULL;

    /** @var string フィールド値対応DBカラム名 */
    protected $db_column = NULL;

    /** @var string フィールド値対応PHPコード */
    protected $code = NULL;

    /** @var string[] レコードルール */
    protected $_rules = [];
    // まず、引数として渡されるConfigのルールを格納し、
    // _check_and_set_props()以降でデータ生成のための調整をしたルールを格納する。

    /** @var string[] 全フィールド名 */
    protected $_field_names = [];
    // 今のところ、_check_and_set_code()のみで使われる。


    /**
     * __construct
     *
     * 引数で渡されたレコードルールと、あり得るルールの組み合わせを突き合わせる。
     * レコードルールは、キーにルール名、値にルール値が入った連想配列である。
     *
     * @param array $rules
     * @param array $field_names
     */
    public function __construct($rules, $field_names)
    {
        parent::__construct($this);
        $this->_rules = $rules;
        $this->_field_names = $field_names;

        $this->_check_and_set_props([
            'seq' => self::TYPE_UINT
        ]);
        $this->_check_and_set_props([
            'number' => self::TYPE_NUMERIC_RANGE
        ]);
        $this->_check_and_set_props([
            'datetime' => self::TYPE_DATETIME_RANGE
        ]);
        $this->_check_and_set_props([
            'timestamp' => self::TYPE_DATETIME_RANGE
        ]);
        $this->_check_and_set_props([
            'pattern' => self::TYPE_PATTERN
        ]);
        $this->_check_and_set_props([
            'db_column' => self::TYPE_DB_COLUMN
        ]);
        $this->_check_and_set_props([
            'code' => self::TYPE_CODE
        ]);

        $this->_check_and_set_props([
            'seq' => self::TYPE_UINT,
            'pattern' => self::TYPE_PATTERN
        ]);
        $this->_check_and_set_props([
            'seq' => self::TYPE_UINT,
            'db_column' => self::TYPE_DB_COLUMN
        ]);
        $this->_check_and_set_props([
            'number' => self::TYPE_NUMERIC_RANGE,
            'pattern' => self::TYPE_PATTERN
        ]);
        $this->_check_and_set_props([
            'number' => self::TYPE_NUMERIC_RANGE,
            'db_column' => self::TYPE_DB_COLUMN
        ]);
        $this->_check_and_set_props([
            'pattern' => self::TYPE_PATTERN,
            'length' => self::TYPE_UINT_RANGE
        ]);
        $this->_check_and_set_props([
            'pattern' => self::TYPE_PATTERN,
            'db_column' => self::TYPE_DB_COLUMN
        ]);

        $this->_check_and_set_props([
            'seq' => self::TYPE_UINT,
            'pattern' => self::TYPE_PATTERN,
            'length' => self::TYPE_UINT_RANGE
        ]);
        $this->_check_and_set_props([
            'seq' => self::TYPE_UINT,
            'pattern' => self::TYPE_PATTERN,
            'db_column' => self::TYPE_DB_COLUMN
        ]);
        $this->_check_and_set_props([
            'seq' => self::TYPE_UINT,
            'db_column' => self::TYPE_DB_COLUMN,
            'length' => self::TYPE_UINT_RANGE
        ]);
        $this->_check_and_set_props([
            'number' => self::TYPE_NUMERIC_RANGE,
            'pattern' => self::TYPE_PATTERN,
            'length' => self::TYPE_UINT_RANGE
        ]);
        $this->_check_and_set_props([
            'number' => self::TYPE_NUMERIC_RANGE,
            'pattern' => self::TYPE_PATTERN,
            'db_column' => self::TYPE_DB_COLUMN
        ]);
        $this->_check_and_set_props([
            'number' => self::TYPE_NUMERIC_RANGE,
            'db_column' => self::TYPE_DB_COLUMN,
            'length' => self::TYPE_UINT_RANGE
        ]);
        $this->_check_and_set_props([
            'pattern' => self::TYPE_PATTERN,
            'db_column' => self::TYPE_DB_COLUMN,
            'length' => self::TYPE_UINT_RANGE
        ]);

        $this->_after_check_props();
    }


    /**
     * get_values
     *
     * 全ルールの名前と値を順番を保ったまま配列で返す。
     */
    public function get_values()
    {
        return $this->_rules;
    }


    /**
     * _check_and_set_props
     *
     * 引数のあり得るルールの組み合わせと実際のルールの組み合わせが一致したら、
     * 指定したプロパティにルールの値を処理してセットする。
     *
     * @param array $props
     */
    protected function _check_and_set_props($props)
    {
        // 検査対象となるレコードルールとキーと順不同で一致したら処理をすすめる。
        $rule_names = array_keys($this->_rules);
        $prop_names = array_keys($props);
        $bingo_names = array_filter($prop_names, function($v) use ($rule_names) {
            return in_array($v, $rule_names);
        });
        if (count($bingo_names) !== count($prop_names) || count($bingo_names) !== count($rule_names))
        {
            return;
        }

        foreach ($this->_rules as $prop => $rule)
        {
            // 動的にメソッドを呼び出す。
            if (array_key_exists($prop, $props))
            {
                $method = '_check_and_set_' . $props[$prop];
                $this->{$method}($prop, $rule);
                $this->_rules[$prop] = $this->{$prop};
            }
        }
    }


    /**
     * _after_check_props
     *
     * _check_and_set_props()の後で行うチェック。
     */
    private function _after_check_props()
    {
        if (is_null($this->length))
        {
            return;
        }

        $length = $this->length->get_array_without_weight();

        if (!is_array($length) || count($length) != 1)
        {
            return;
        }

        $this->length = $length[0];

        if (!is_array($this->length) || !array_key_exists(self::KEY_HIGHER, $this->length))
        {
            return;
        }

        $total_pattern_length = 0;
        foreach ($this->pattern as $pattern)
        {
            if (array_key_exists(self::KEY_LENGTH, $pattern) && Util::is_numeric_uint($pattern[self::KEY_LENGTH]))
            {
                $total_pattern_length += intval($pattern[self::KEY_LENGTH]);
            }
            else if (Util::is_numeric_uint(end($pattern)))
            {
                $total_pattern_length += intval(end($pattern));
            }
        }

        // 全体の文字列の長さが各パターンの文字列の長さの合計より短くないかをチェック。
        if ($this->length[self::KEY_HIGHER] < $total_pattern_length)
        {
             $this->_set_error(self::MESSAGE_INVALID_TOTAL_LENGTH . ' length => '
                 . Util::json_encode($this->length, JSON_UNESCAPED_UNICODE));
        }
    }


    /**
     * _check_and_set_uint
     *
     * ルールの値が正の整数だったら、指定されたプロパティに値をセットする。
     *
     * @param string $prop
     * @param mixed $rule
     */
    private function _check_and_set_uint($prop, $rule)
    {
        if (!Util::is_numeric_uint($rule))
        {
            $this->_set_error(self::MESSAGE_NOT_UINT . " {$prop} => {$rule}");
            return;
        }

        $this->{$prop} = intval($rule);
    }


    /**
     * _check_and_set_uint_range
     *
     * ルールの値が正の整数で範囲が指定された配列だったら、指定されたプロパティに配列をセットする。
     * 値が漸増しているかもチェックしている。
     *
     * @param string $prop
     * @param mixed $rule
     */
    private function _check_and_set_uint_range($prop, $rule)
    {
        $this->_common_uint_range_numeric_range($prop, $rule);
    }


    /**
     * _check_and_set_numeric_range
     *
     * ルールの値が数値で範囲が指定された配列だったら、指定されたプロパティに配列をセットする。
     * 値が漸増しているかもチェックしている。
     *
     * @param string $prop
     * @param mixed $rule
     */
    private function _check_and_set_numeric_range($prop, $rule)
    {
        $this->_common_uint_range_numeric_range($prop, $rule);
    }


    /**
     * _common_uint_range_numeric_range
     *
     * _check_and_set_uint_range()と_check_and_set_numeric_range()の共通処理。
     *
     * @param string $prop
     * @param mixed $rule
     */
    private function _common_uint_range_numeric_range($prop, $rule)
    {
        $caller = debug_backtrace()[1]['function'];

        if (!is_array($rule))
        {
            $temp = [];
            $temp[0][] = $rule;
            $temp[0][] = $rule;
            $rule = $temp;
        }

        $temp = [];
        foreach ($rule as $range)
        {
            if (!is_array($range))
            {
                if (!count($temp)) $temp[] = [];
                $temp[0][] = $range;
            }
            else if (count($temp))
            {
                $this->_set_error(self::MESSAGE_MALFORMED_ARRAY . " {$prop} => "
                    . Util::json_encode($rule, JSON_UNESCAPED_UNICODE));
                return;
            }
        }
        if (count($temp)) $rule = $temp;

        $formed = [];
        foreach ($rule as $range)
        {
            if (count($range) == 1)
            {
                $temp = [$range[0], $range[0]];
                $range = $temp;
            }

            if (count($range) > 4)
            {
                $this->_set_error(self::MESSAGE_MALFORMED_ARRAY . " {$prop} => "
                    . Util::json_encode($range, JSON_UNESCAPED_UNICODE));
                return;
            }

            $id_lower = NULL;
            $id_higher = NULL;
            $id_weight = NULL;
            $id_interval = NULL;

            if (array_key_exists(self::KEY_LOWER, $range))
            {
                $id_lower = self::KEY_LOWER;
                $id_higher = self::KEY_HIGHER;
                $id_weight = self::KEY_WEIGHT;
                $id_interval = self::KEY_INTERVAL;
            }
            else
            {
                $id_lower = self::IDX_LOWER;
                $id_higher = self::IDX_HIGHER;
                $id_weight = self::IDX_WEIGHT;
                $id_interval = self::IDX_INTERVAL;
            }

            $lower = NAN;
            $higher = NAN;
            $weight = 1;
            $interval = 1;

            if (!(array_key_exists($id_lower, $range) && array_key_exists($id_higher, $range))
                || (count($range) >= 3 && !array_key_exists($id_weight, $range))
                || (count($range) == 4 && !array_key_exists($id_interval, $range)))
            {
                $this->_set_error(self::MESSAGE_MALFORMED_ARRAY . " {$prop} => "
                    . Util::json_encode($range, JSON_UNESCAPED_UNICODE));
                return;
            }

            $lower = $range[$id_lower];
            $higher = $range[$id_higher];
            if (array_key_exists($id_weight, $range)) $weight = $range[$id_weight];
            if (array_key_exists($id_interval, $range)) $interval = $range[$id_interval];

            if (!Util::is_numeric_uint($weight))
            {
                $this->_set_error(self::MESSAGE_INVALID_WEIGHT . " {$prop} => "
                    . Util::json_encode($range, JSON_UNESCAPED_UNICODE));
                return;
            }

            $is_error = FALSE;
            if (Util::is_numeric_uint($lower)) $lower = intval($lower);
            else if (is_numeric($lower)) $lower = floatval($lower);
            else $is_error = TRUE;
            if (Util::is_numeric_uint($higher)) $higher = intval($higher);
            else if (is_numeric($higher)) $higher = floatval($higher);
            else $is_error = TRUE;
            if (Util::is_numeric_uint($interval)) $interval = intval($interval);
            else if (is_numeric($interval)) $interval = floatval($interval);
            else $is_error = TRUE;

            if ($is_error)
            {
                if ($caller == '_check_and_set_uint_range')
                {
                    $this->_set_error(self::MESSAGE_NOT_UINT_RANGE . " {$prop} => "
                        . Util::json_encode($range, JSON_UNESCAPED_UNICODE));
                    return;
                }

                if ($caller == '_check_and_set_numeric_range')
                {
                    $this->_set_error(self::MESSAGE_NOT_NUMERIC_RANGE . " {$prop} => "
                        . Util::json_encode($range, JSON_UNESCAPED_UNICODE));
                    return;
                }
            }

            if (!is_nan($lower) && !is_nan($higher) && $lower > $higher)
            {
                $this->_set_error(self::MESSAGE_INVALID_RANGE . " {$prop} => "
                    . Util::json_encode($rule, JSON_UNESCAPED_UNICODE));
                return;
            }

            $last = count($formed) - 1;
            $formed[$last] = [];
            $formed[$last][self::KEY_LOWER] = $lower;
            $formed[$last][self::KEY_HIGHER] = $higher;
            $formed[$last][self::KEY_INTERVAL] = $interval;
            $formed[$last][self::KEY_WEIGHT] = $weight;
        }

        $weighted = $this->_weight($formed);

        $this->{$prop} = $weighted;
    }


    /**
     * _check_and_set_datetime_range
     *
     * ルールの値が日付文字列だったら指定されたプロパティに配列をセットする。
     * 値が漸増しているかもチェックする。
     *
     * @param string $prop
     * @param mixed $rule
     */
    private function _check_and_set_datetime_range($prop, $rule)
    {
        if (!is_array($rule))
        {
            $temp = [];
            $temp[] = [$rule, $rule];
            $rule = $temp;
        }

        $formed = [];
        foreach ($rule as $range)
        {
            // 値が1つだった場合、それを[同時刻の上限, 同時刻の下限]とする。
            if (!is_array($range))
            {
                $temp = [$range, $range];
                $range = $temp;
            }

            // 値が1つだった場合、それを[同時刻の上限, 同時刻の下限]とする。
            if (count($range) == 1)
            {
                $temp = [$range[0], $range[0]];
                $range = $temp;
            }

            if (count($range) > 3)
            {
                $this->_set_error(self::MESSAGE_MALFORMED_ARRAY . " {$prop} => "
                    . Util::json_encode($range, JSON_UNESCAPED_UNICODE));
                return;
            }

            $id_lower = NULL;
            $id_higher = NULL;
            $id_weight = NULL;

            if (array_key_exists(self::KEY_LOWER, $range))
            {
                $id_lower = self::KEY_LOWER;
                $id_higher = self::KEY_HIGHER;
                $id_weight = self::KEY_WEIGHT;
            }
            else
            {
                $id_lower = self::IDX_LOWER;
                $id_higher = self::IDX_HIGHER;
                $id_weight = self::IDX_WEIGHT;

            }

            $lower = NAN;
            $higher = NAN;
            $weight = 1;

            if (!(array_key_exists($id_lower, $range) && array_key_exists($id_higher, $range))
                || (count($range) == 3 && !array_key_exists($id_weight, $range)))
            {
                $this->_set_error(MESSAGE_MALFORMED_ARRAY . " {$prop} => "
                    . Util::json_encode($range, JSON_UNESCAPED_UNICODE));
                return;
            }

            $lower = $range[$id_lower];
            $higher = $range[$id_higher];
            if (array_key_exists($id_weight, $range)) $weight = $range[$id_weight];

            if (!Util::is_numeric_uint($weight))
            {
                $this->_set_error(self::MESSAGE_INVALID_WEIGHT . " {$prop} => "
                    . Util::json_encode($range, JSON_UNESCAPED_UNICODE));
                return;
            }

            $formed[] = [];
            $last = count($formed) - 1;
            $lower_dt = $this->_check_and_convert_datetime($lower);
            $higher_dt = $this->_check_and_convert_datetime($higher);

            // FALSEが返ってきたらエラーなので…
            if (!$lower_dt || !$higher_dt)
            {
                $this->_set_error(self::MESSAGE_NOT_TIMESTAMP_RANGE . " {$prop} => "
                    . Util::json_encode($range, JSON_UNESCAPED_UNICODE));
                return;
            }

            if ($lower_dt > $higher_dt)
            {
                $this->_set_error(self::MESSAGE_INVALID_RANGE . " {$prop} => "
                    . Util::json_encode($range, JSON_UNESCAPED_UNICODE));
                return;
            }

            $formed[$last][self::KEY_LOWER] = $lower_dt;
            $formed[$last][self::KEY_HIGHER] = $higher_dt;
            $formed[$last][self::KEY_WEIGHT] = $weight;
        }

        $weighted = $this->_weight($formed);

        $this->{$prop} = $weighted;
    }


    /**
     * _check_and_set_pattern
     *
     * ルールの値がPatternクラスにあるプロパティ名だったら、そのパターン配列を、
     * 配列だったら、それをパターン配列として、指定されたプロパティにセットする。
     * JSONでの形式は下記の通り。
     * [
     *   ["Pattern1", "Pattern2", "Pattern3"],
     *   [["common", 75], ["uncommon", 20], ["rare", 5]],
     *   [["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"], 10],
     *   [[["○", 75], ["△", 20], ["×", 5]], 10],
     *   ["Pattern::UPPER_ALPHABET"],
     *   ["Pattern::LOWER_ALPHABET", 10]
     * ]
     * 1パターン目…Pattern1～3のランダム
     * 2パターン目…common，uncommon，rareの順の頻度でランダム
     * 3パターン目…0～9でランダムに10文字
     * 4パターン目…○，△，×の順の頻度でランダムに10文字
     * 5パターン目…Patternクラス内のUPPER_ALPHABETからランダム(アルファベット大文字1文字)
     * 6パターン目…Patternクラス内のLOWER_ALPHABETからランダムに10文字
     *
     * @param string $prop
     * @param mixed $rule
     */
    private function _check_and_set_pattern($prop, $rule)
    {
        if (!is_array($rule))
        {
            $this->_set_error(self::MESSAGE_NOT_ARRAY . " {$prop} => {$rule}");
            return;
        }

        if (!count($rule))
        {
            $this->_set_error(self::MESSAGE_MALFORMED_ARRAY . " {$prop} => "
                . Util::json_encode($pattern, JSON_UNESCAPED_UNICODE));
            return;
        }

        $weighted = [];
        foreach ($rule as $pattern)
        {
            $weight = 1;
            $length = NAN;

            $id_pattern = NULL;
            $id_length = NULL;
            $id_element = NULL;
            $id_weight = NULL;

            if (array_key_exists(self::KEY_PATTERN, $pattern))
            {
                $id_pattern = self::KEY_PATTERN;
                $id_length = self::KEY_LENGTH;
                $id_element = self::KEY_PATTERN_ELEMENT;
                $id_weight = self::KEY_PATTERN_WEIGHT;
            }
            else
            {
                $id_pattern = self::IDX_PATTERN;
                $id_length = self::IDX_LENGTH;
                $id_element = self::IDX_PATTERN_ELEMENT;
                $id_weight = self::IDX_PATTERN_WEIGHT;
            }

            // パターンの長さ抽出。
            if (count($pattern) == 2
                && array_key_exists($id_length, $pattern) && Util::is_numeric_uint($pattern[$id_length]))
            {
                $length = $pattern[$id_length];
            }

            // Patternクラスからパターンの配列を抽出してマージ。
            if (is_string($pattern[$id_pattern]) && strpos($pattern[$id_pattern], 'Pattern::') === 0)
            {
                $name = substr($pattern[$id_pattern], strlen('Pattern::'));
                if (strpos($name, '$') === 0)
                {
                    $name = substr($name, 1);
                }
                $pattern[$id_pattern] = Pattern::get($name);
            }

            // パターンの正規化。
            if (!is_array($pattern[$id_pattern]))
            {
                $temp = $pattern;
                $pattern = [];
                $pattern[$id_pattern] = $temp;
            }

            // パターンの正規化。
            if (count($pattern) > 2
                || (array_key_exists($id_length, $pattern) && !Util::is_numeric_uint($pattern[$id_length])))
            {
                $pattern = [$pattern];
            }

            // 重み付けを抽出。
            foreach ($pattern[$id_pattern] as $ek => $ev)
            {
                if (is_array($ev))
                {
                    // [パターン文字列, 重み付け数値]を想定。
                    if (count($ev) != 2)
                    {
                        $this->_set_error(self::MESSAGE_MALFORMED_ARRAY . " {$prop} => "
                            . Util::json_encode($pattern, JSON_UNESCAPED_UNICODE));
                        return;
                    }

                    if (!array_key_exists($id_element, $ev) || !array_key_exists($id_weight, $ev))
                    {
                        $this->_set_error(self::MESSAGE_MALFORMED_ARRAY . " {$prop} => "
                            . Util::json_encode($pattern, JSON_UNESCAPED_UNICODE));
                        return;
                    }

                    if (!Util::is_numeric_uint($ev[$id_weight]))
                    {
                        $this->_set_error(self::MESSAGE_INVALID_WEIGHT . " {$prop} => "
                            . Util::json_encode($pattern[$id_pattern], JSON_UNESCAPED_UNICODE));
                        return;
                    }

                    $element = $ev[$id_element];
                    $weight = $ev[$id_weight];
                    $pattern[$id_pattern][$ek] = [];

                    $pattern[$id_pattern][$ek][self::KEY_PATTERN_ELEMENT] = $element;
                    $pattern[$id_pattern][$ek][self::KEY_PATTERN_WEIGHT] = $weight;
                }
                else
                {
                    $pattern[$id_pattern][$ek] = [];
                    $pattern[$id_pattern][$ek][self::KEY_PATTERN_ELEMENT] = $ev;
                    $pattern[$id_pattern][$ek][self::KEY_PATTERN_WEIGHT] = $weight;
                }
            }

            $weighted[] = [self::KEY_PATTERN => $this->_weight($pattern[$id_pattern]), self::KEY_LENGTH => $length];
        }

        $this->{$prop} = $weighted;
    }


    /**
     * _check_and_set_db_column
     *
     * ルールの値が文字列だったら、指定されたプロパティに値をセットする。
     *
     * @param string $prop
     * @param mixed $rule
     */
    private function _check_and_set_db_column($prop, $rule)
    {
        if (!is_string($rule))
        {
            $this->_set_error(self::MESSAGE_NOT_STRING . " {$prop} => {$rule}");
            return;
        }

        $this->{$prop} = $rule;
    }


    /**
     * _check_and_set_code
     *
     * ルールの値が有効なPHPコードとフィールド名だったら、
     * 指定されたプロパティに値をセットする。
     *
     * @param string $prop
     * @param mixed $rule
     */
    private function _check_and_set_code($prop, $rule)
    {
        if (!is_string($rule))
        {
            $this->_set_error(self::MESSAGE_NOT_STRING . " {$prop} => {$rule}");
            return;
        }

        // DBレコードのカラム名を使った変数($colname)が使われていた場合、
        // eval()時のエラーを防ぐために変数の初期化を付加する。
        $code = $rule;
        foreach ($this->_field_names as $field_name)
        {
            if (strpos($code, '$' . $field_name) !== FALSE)
            {
                $code = '$' . $field_name . " = ''; " . $code;
            }
        }

        // 安全のため、限られたPHPトークンしか使用できないeval()にかける。
        $result = Util::safely_eval($code);
        if ($result === FALSE)
        {
            $this->_set_error(self::MESSAGE_INVALID_STATEMENTS . " {$prop} => {$rule}");
            return;
        }

        $code = $rule;
        foreach ($this->_field_names as $field_name)
        {
            if (strpos($code, '$' . $field_name) !== FALSE)
            {
                $code = '$' . $field_name . ' = $new_record["' . $field_name . '"]; ' . $code;
            }
        }

        $this->{$prop} = $code;
    }


    /**
     * _check_and_convert_datetime
     *
     * UNIXタイムスタンプの範囲内かどうか、
     * あるいはDateTime::format()で扱える日付文字列かチェックした後、
     * DateTimeオブジェクトに変換して返す。
     *
     * @param string|int|float $value
     * @param bool $is_range_datetime (optional)
     * @return DateTime|bool
     */
    private function _check_and_convert_datetime($value)
    {
        $dt = NULL;
        $temp = explode('.', $value);
        $u = end($temp);
        $is_u = FALSE;
        if ($u != $value)
        {
            if (!is_numeric($u) || strlen($u) > 6)
            {
                return FALSE;
            }
            $is_u = TRUE;
        }

        try
        {
            if (is_string($value))
            {
                if (preg_match('/^\d+?\.?\d*$/', $value))
                {
                    // タイムスタンプからDateTimeを生成する時、タイムゾーンが考慮されない問題を対応。
                    $temp = new \DateTime();
                    $timezone = $temp->getTimezone();
                    if ($is_u)
                    {
                        $dt = \DateTime::createFromFormat('U.u', strval($value));
                    }
                    else
                    {
                        $dt = \DateTime::createFromFormat('U', strval($value));
                    }
                    $dt->setTimezone($timezone);
                }
                else
                {
                    $dt = new \DateTime($value);
                }
                if (!$dt) return FALSE;
            }
            else
            {
                return FALSE;
            }
        }
        catch (\ErrorException $ee)
        {
            return FALSE;
        }

        if (!$dt->getTimestamp())
        {
            return FALSE;
        }

        return $dt;
    }

    /**
     * _weight
     *
     * ルールの重み付けをして返す。
     *
     * @param array $param
     * @return WeightedArray
     */
    private function _weight($param)
    {
        $weighted = new WeightedArray();
        $weight = 1;
        foreach ($param as $elem)
        {
            if (!is_array($elem))
            {
                $elem = [$elem];
            }
            if (array_key_exists(self::KEY_WEIGHT, $elem))
            {
                $weight = $elem[self::KEY_WEIGHT];
                unset($elem[self::KEY_WEIGHT]);
            }
            $weighted->append($elem, $weight);
        }
        return $weighted;
    }
}
