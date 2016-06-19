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
     * キーと重み付けの値を追加する。
     * $divisorは重み付け合計値がintを超えるような時に割る値。
     *
     * @param mixed $key
     * @param int $weight (optional)
     * @param int $divisor (optional)
     */
    public function append($key, $weight=1, $divisor=1)
    {
        try
        {
            $weight = intval($weight / $divisor);
            $this->offsetSet($key, $weight);
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
     * rand
     *
     * 重み付けに応じたランダム要素を返す。
     * キーがJSONだったら連想配列に変換する。
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
            $sum = 0;
            foreach ($this as $key => $weight)
            {
                $sum += $weight;
                if ($sum >= $rand)
                {
                    return unserialize($key);
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
     * get_array_from_keys
     *
     * 配列のキーを配列に格納して返す。
     * キーがシリアライズ文字列なので元に戻す。
     *
     * @return array
     */
    public function get_array_from_keys()
    {
        try
        {
            $keys = [];
            foreach ($this as $key => $weight)
            {
                $keys[] = unserialize($key);
            }
            return $keys;
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
     * offsetExists
     *
     * ArrayObject::offsetExists()をオーバーライド。
     *
     * @return bool
     */
    public function offsetExists($idx)
    {
        try
        {
            reset($this);
            $now = 0;
            do
            {
                if ($now == $idx) return TRUE;
                $now++;
            }
            while (next($this));
            return FALSE;
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
     * offsetGet
     *
     * ArrayObject::offsetGet()をオーバーライド。
     *
     * @return mixed
     */
    public function offsetGet($idx)
    {
        try
        {
            reset($this);
            $now = 0;
            foreach ($this as $key => $weight)
            {
                if ($now == $idx) return unserialize($key);
                $now++;
            }
            throw new \Exception(self::MESSEAGE_ERROR_WEIGHTED_ARRAY);
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
     * @return mixed
     */
    public function offsetSet($key, $value)
    {
        try
        {
            parent::offsetSet(serialize($key), $value);
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
     * offsetUnset
     *
     * ArrayObject::offsetUnset()をオーバーライド。
     *
     * @return mixed
     */
    public function offsetUnset($key)
    {
        try
        {
            parent::offsetUnset(serialize($key));
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