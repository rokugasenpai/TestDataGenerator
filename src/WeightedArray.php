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


/**
 * WeightedArray
 *
 * 重み付けを扱えるArrayObject。
 *
 * @package TestDataGenerator
 */
class WeightedArray extends \ArrayObject
{
    const MESSEAGE_ERROR_WEIGHTED_ARRAY = 'WeightedArrayエラー。';

    const ERROR_HANDLING_RETURN_FALSE = 0;
    const ERROR_HANDLING_EXCEPTION = 1;

    /** @var int エラーハンドリングフラグ */
    private $_error_handling = 0;

    /** @var int 重み付け合計値 */
    private $_total_weight = 0;

    /** @var array インデックス */
    private $_index = [];

    /**
     * __construct
     *
     * @param int $error_handling
     */
    public function __construct($error_handling=0)
    {
        try
        {
            if (!is_int($error_handling)) throw new \Exception(self::MESSEAGE_ERROR_WEIGHTED_ARRAY);

            $this->_error_handling = $error_handling;
            parent::__construct([]);
        }
        catch (Exception $e)
        {
            if ($this->_error_handling == self::ERROR_HANDLING_RETURN_FALSE)
            {
                return FALSE;
            }

            throw new \Exception(self::MESSEAGE_ERROR_WEIGHTED_ARRAY, $e);
        }
    }


    /**
     * append
     *
     * 値と重み付けの値を追加する。
     * $divisorは重み付け合計値がintを超えるような時に割る値。
     *
     * @param mixed $value
     * @param int $weight (optional)
     * @param int $divisor (optional)
     */
    public function append($value, $weight=1, $divisor=1)
    {
        try
        {
            $weight = intval($weight / $divisor);
            $this->offsetSet($value, $weight);
            $this->_total_weight += $weight;
        }
        catch (Exception $e)
        {
            if ($this->_error_handling == self::ERROR_HANDLING_RETURN_FALSE)
            {
                return FALSE;
            }

            throw new \Exception(self::MESSEAGE_ERROR_WEIGHTED_ARRAY, $e);
        }
    }


    /**
     * index
     *
     * rand()の処理速度を上げるためにインデックスを付ける。
     *
     * @param int $num_index (optional)
     */
    public function index($num_index=0)
    {
        try
        {
            $this->_index = [];
            $len = $this->count();
            if (!$num_index) $num_index = intval(sqrt($len));
            $now_index = 0;
            $bingo_index = $now_index * intval($len / $num_index);
            $sum = 0;
            $i = 0;
            foreach ($this as $i => $elem)
            {
                $value = key($elem);
                if (is_null($value))
                {
                    throw new \Exception(self::MESSEAGE_ERROR_WEIGHTED_ARRAY);
                }
                if ($i == $bingo_index)
                {
                    $this->_index[$i] = $sum;
                    $now_index++;
                    $bingo_index = $now_index * intval($len / $num_index);
                }
                $sum += $elem[$value];
            }
        }
        catch (Exception $e)
        {
            if ($this->_error_handling == self::ERROR_HANDLING_RETURN_FALSE)
            {
                return FALSE;
            }

            throw new \Exception(self::MESSEAGE_ERROR_WEIGHTED_ARRAY, $e);
        }
    }


    /**
     * rand
     *
     * 重み付けに応じた値を返す。
     * シリアライズ文字列なので元に戻す。
     *
     * @return mixed
     */
    public function rand()
    {
        try
        {
            $rand = mt_rand(0, $this->_total_weight);
            if ($this->_total_weight <= 0)
            {
                throw new \Exception(self::MESSEAGE_ERROR_WEIGHTED_ARRAY);
            }
            $start_index = 0;
            $finish_index = $this->count() - 1;
            $sum = 0;
            foreach ($this->_index as $i => $index_sum)
            {
                if ($index_sum > $rand)
                {
                    $finish_index = $i - 1;
                    break;
                }
                $start_index = $i;
                $sum = $index_sum;
            }
            for ($i = $start_index; $i <= $finish_index; $i++)
            {
                $elem = $this->offsetGet($i);
                $value = key($elem);
                if (is_null($value))
                {
                    throw new \Exception(self::MESSEAGE_ERROR_WEIGHTED_ARRAY);
                }
                $sum += $elem[$value];
                if ($sum >= $rand)
                {
                    return unserialize($value);
                }
            }
        }
        catch (Exception $e)
        {
            if ($this->_error_handling == self::ERROR_HANDLING_RETURN_FALSE)
            {
                return FALSE;
            }

            throw new \Exception(self::MESSEAGE_ERROR_WEIGHTED_ARRAY, $e);
        }
    }


    /**
     * get_column_names
     *
     * @return string[]
     */
    public function get_column_names()
    {
        try
        {
            $column_names = [];
            if ($this->offsetExists(0))
            {
                $elem = $this->offsetGet(0);
                $record = unserialize(key($elem));
                $column_names = array_keys($record);
            }
            return $column_names;
        }
        catch (Exception $e)
        {
            if ($this->_error_handling == self::ERROR_HANDLING_RETURN_FALSE)
            {
                return FALSE;
            }

            throw new \Exception(self::MESSEAGE_ERROR_WEIGHTED_ARRAY, $e);
        }
    }


    /**
     * get_array_without_weight
     *
     * 値を配列に格納して返す。
     * シリアライズ文字列なので元に戻す。
     *
     * @return array
     */
    public function get_array_without_weight()
    {
        try
        {
            $values = [];
            foreach ($this as $elem)
            {
                $value = key($elem);
                if (is_null($value))
                {
                    throw new \Exception(self::MESSEAGE_ERROR_WEIGHTED_ARRAY);
                }
                $values[] = unserialize($value);
            }
            return $values;
        }
        catch (Exception $e)
        {
            if ($this->_error_handling == self::ERROR_HANDLING_RETURN_FALSE)
            {
                return FALSE;
            }

            throw new \Exception(self::MESSEAGE_ERROR_WEIGHTED_ARRAY, $e);
        }
    }


    /**
     * offsetSet
     *
     * ArrayObject::offsetSet()をオーバーライド。
     *
     * @param mixed $value
     * @param mixed $weight
     */
    public function offsetSet($value, $weight)
    {
        try
        {
            parent::offsetSet($this->count(), [serialize($value) => $weight]);
        }
        catch (Exception $e)
        {
            if ($this->_error_handling == self::ERROR_HANDLING_RETURN_FALSE)
            {
                return FALSE;
            }

            throw new \Exception(self::MESSEAGE_ERROR_WEIGHTED_ARRAY, $e);
        }
    }
}