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
 * Record
 *
 * 生成するデータのフィールドを扱う。
 *
 * @package    TestDataGenerator
 */
class Field
{
    const MAX_RETRY = 100;

    /** @var string フィールド値 */
    private $_value = NULL;


    /**
     * __construct
     *
     * フィールド値の生成とセットを行う。丸々set_value()に渡す。
     * 引数の$weighted_mastersは_master()、$new_recordは_code()のためのもの。
     *
     * @param int $now_index
     * @param string $name
     * @param Rule $rules
     * @param WeightedArray[] $weighted_masters (optional)
     * @param array $new_record (optional)
     */
    public function __construct($now_index, $name, $rules, $weighted_masters=[], $new_record=[])
    {
        $this->set_value($now_index, $name, $rules, $weighted_masters, $new_record);
    }


    /**
     * set_value
     *
     * フィールド値の生成とセットを行う。
     * 引数の$weighted_mastersは_master()、$new_recordは_code()のためのもの。
     *
     * @param int $now_index
     * @param string $name
     * @param Rule[] $rules
     * @param WeightedArray[] $weighted_masters (optional)
     * @param array $new_record (optional)
     */
    public function set_value($now_index, $name, $rules, $weighted_masters=[], $new_record=[])
    {
        if (!is_null($this->_value)) return;
        $breaker = 0;
        while ($breaker < self::MAX_RETRY)
        {
            foreach ($rules->get_values() as $rule_name => $rule_value)
            {
                if (is_null($rule_value)) continue;
                $method_name = "_{$rule_name}";
                if (method_exists($this, $method_name))
                {
                    $this->$method_name($now_index, $rule_value, $weighted_masters, $new_record);
                }
            }

            if ($this->_check_value($rules))
            {
                break;
            }
            else
            {
                $this->_value = NULL;
            }

            $breaker++;
        }

        if ($breaker == self::MAX_RETRY)
        {
            throw new TDGE(TDGE::MESSEAGE_OUT_OF_RANGE_FIELD_LENGTH,
                "{$name} => " . Util::json_encode($rules->length, JSON_UNESCAPED_UNICODE));
        }
    }


    /**
     * get_value
     *
     * フィールド値を返す。
     *
     * @return string
     */
    public function get_value()
    {
        return $this->_value;
    }


    /**
     * _check_value
     *
     * フィールド値がルールで指定された文字数の範囲内かチェックする。
     *
     * @param Rule[] $rules
     * @return bool
     */
    private function _check_value($rules)
    {
        if (is_null($rules->length)
            || (is_nan($rules->length[Rule::KEY_LOWER])
            || mb_strlen($this->_value, Util::UTF8) >= $rules->length[Rule::KEY_LOWER])
            &&
            (is_nan($rules->length[Rule::KEY_HIGHER])
            || mb_strlen($this->_value, Util::UTF8) <= $rules->length[Rule::KEY_HIGHER]))
        {
            return TRUE;
        }

        return FALSE;
    }


    /**
     * _seq
     *
     * シーケンス値の生成とセットを行う。
     *
     * @param int $now_index
     * @param Rule $rule
     * @param WeightedArray[] $weighted_masters
     * @param array $new_record
     */
    private function _seq($now_index, $rule, $weighted_masters, $new_record)
    {
        $this->_value .= $now_index + $rule;
    }


    /**
     * _number
     *
     * 数値の生成とセットを行う。
     *
     * @param int $now_index
     * @param Rule $rule
     * @param WeightedArray[] $weighted_masters
     * @param array $new_record
     */
    private function _number($now_index, $rule, $weighted_masters, $new_record)
    {
        $rule = $rule->rand();
        $lower = $rule[Rule::KEY_LOWER];
        $higher = $rule[Rule::KEY_HIGHER];
        $interval = $rule[Rule::KEY_INTERVAL];

        if (is_nan($interval))
        {
            $this->_value .= mt_rand($lower, $higher);
        }
        else
        {
            $this->_value .= mt_rand(intval(ceil($lower / $interval)),
                intval(floor($higher / $interval))) * $interval;
        }
    }


    /**
     * _datetime
     *
     * 日付の生成とセットを行う。
     *
     * @param int $now_index
     * @param Rule $rule
     * @param WeightedArray[] $weighted_masters
     * @param array $new_record
     */
    private function _datetime($now_index, $rule, $weighted_masters, $new_record)
    {
        $this->_value .= $this->_common_datetime_timestamp($rule);
    }


    /**
     * _timestamp
     *
     * タイムスタンプの生成とセットを行う。
     *
     * @param int $now_index
     * @param Rule $rule
     * @param WeightedArray[] $weighted_masters
     * @param array $new_record
     */
    private function _timestamp($now_index, $rule, $weighted_masters, $new_record)
    {
        $this->_value .= $this->_common_datetime_timestamp($rule);
    }


    /**
     * _common_datetime_timestamp
     *
     * _datetime()と_timestamp()の共通処理。
     *
     * @param Rule $rule
     */
    private function _common_datetime_timestamp($rule)
    {
        $caller = debug_backtrace()[1]['function'];
        $rule = $rule->rand();
        $lower = $rule[Rule::KEY_LOWER];
        $higher = $rule[Rule::KEY_HIGHER];

        $dt_time = '';
        $format = '';

        // 上限・下限のいずれかが小数まであれば、マイクロ秒まで出力する。
        if ($lower->format('u') !== '000000' || $higher->format('u') !== '000000')
        {
            // mt_rand()もDateTime::add()もfloatを受け付けない上、
            // 単純に小数桁分乗じてもintの範囲を超えるため、bcmathでゴニョゴニョしてる。(面倒くさい…)
            $diff = bcmul(bcsub($higher->format('U.u'), $lower->format('U.u')), bcpow('10', '6'));
            $diff_s = (strlen($diff) > strlen(PHP_INT_MAX)) ? strlen($diff) : strlen(PHP_INT_MAX);
            $diff_x = bcdiv($diff, strval(PHP_INT_MAX), $diff_s);
            $plus = bcdiv(bcmul(mt_rand(), $diff_x, $diff_s), bcpow('10', '6'));
            $plus_sec = strval(intval($plus));
            $plus_usec = substr(bcsub($plus, strval($plus_sec)), 1);
            $dt = new \DateTime($lower->format('Y-m-d H:i:s'));
            $dt->add(new \DateInterval('PT' . $plus_sec . 'S'));
            $dt = new \DateTime($dt->format('Y-m-d H:i:s') . $plus_usec);
            $dt_time = $dt->format('U.u');
            if ($caller == '_datetime')
            {
                $format = 'Y-m-d H:i:s.u';
            }
        }
        else
        {
            $diff = intval($higher->format('U')) - intval($lower->format('U'));
            $plus_sec = mt_rand(0, $diff);
            $dt = new \DateTime($lower->format('Y-m-d H:i:s'));
            $dt->add(new \DateInterval('PT' . $plus_sec . 'S'));
            $dt_time = $dt->format('U');
            if ($caller == '_datetime')
            {
                $format = 'Y-m-d H:i:s';
            }
        }

        if (!strlen($format))
        {
            // タイムスタンプの場合。
            return $dt_time;
        }
        else
        {
            // 日時の場合。
            return $dt->format($format);
        }
    }


    /**
     * _pattern
     *
     * ルールのパターン配列よりフィールド値の生成とセットを行う。
     *
     * @param int $now_index
     * @param Rule $rule
     * @param WeightedArray[] $weighted_masters
     * @param array $new_record
     */
    public function _pattern($now_index, $rule, $weighted_masters, $new_record)
    {
        $value = NULL;
        foreach ($rule as $pattern)
        {
            $weighted = $pattern[Rule::KEY_PATTERN];

            if (!is_nan($pattern[Rule::KEY_LENGTH]))
            {
                $tmp = '';
                $breaker = 0;

                // 指定文字数での生成に指定回数失敗したら諦める。
                while ($breaker < self::MAX_RETRY)
                {
                    $tmp .= $weighted->rand()[Rule::KEY_PATTERN_ELEMENT];
                    if (mb_strlen($tmp, Util::UTF8) < $pattern[Rule::KEY_LENGTH])
                    {
                        $breaker++;
                        continue;
                    }
                    else
                    {
                        $value .= $tmp;
                        break;
                    }
                }
            }
            else
            {
                $value .= $weighted->rand()[Rule::KEY_PATTERN_ELEMENT];
            }
        }

        if (is_null($value))
        {
            $this->_value = NULL;
        }
        else
        {
            $this->_value .= $value;
        }
    }


    /**
     * _master
     *
     * 重みづけマスタからランダムにフィールド値の生成とセットを行う。
     *
     * @param int $now_index
     * @param Rule $rule
     * @param WeightedArray[] $weighted_masters
     * @param array $new_record
     */
    private function _master($now_index, $rule, $weighted_masters, $new_record)
    {
        foreach ($rule as $master_name => $column_name)
        {
            $weighted_master = $weighted_masters[$master_name];
            $value = $weighted_master->rand()[$column_name];

            // NULLをNULLのままフィールド値としたいため
            if (is_null($value))
            {
                $this->_value = NULL;
            }
            else
            {
                $this->_value .= $value;
            }
        }
    }


    /**
     * _code
     *
     * PHPコードを実行することでフィールド値の生成とセットを行う。
     *
     * @param int $now_index
     * @param Rule $rule
     * @param WeightedArray[] $weighted_masters
     * @param array $new_record
     */
    private function _code($now_index, $rule, $weighted_masters, $new_record)
    {
        if (!count($new_record))
        {
            $this->_value = NULL;
            return;
        }

        $this->_value .= eval($rule);
    }
}