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
 * TDGBase
 *
 * プロパティにセットする値の検証をし易くすることを目的とした基底クラス。
 *
 * @package    TestDataGenerator
 */
abstract class TDGBase extends ReadOnly
{
    /** @var bool プロパティ検証失敗フラグ */
    private $_is_fail_check_props = FALSE;

    /** @var string[] 全エラー文言 */
    private $_errors = [];

    /** @var array プロパティ初期値 */
    private $_initial_props = [];
    // プロパティ検証に失敗した時に、_restore_initial_propsメソッド内で、初期値に戻すために使用。
    // [[TDGBaseのプロパティ], [子クラスのプロパティ]]

    /** @var object 子インスタンス */
    private $_child = NULL;


    /**
     * __construct
     *
     * 各プロパティの初期値を_initial_propsプロパティに格納する。
     * 
     */
    public function __construct($child)
    {
        $this->_initial_props = [get_object_vars($this), get_object_vars($child)];
        $this->_child = $child;
    }


    /**
     * get_values
     *
     * 子クラスのアクセス可能な全プロパティの名前と値を配列で返す。
     */
     public function get_values()
     {
         $values = [];
         foreach (get_object_vars($this->_child) as $name => $value)
         {
             if (strpos($name, '_') !== 0)
             {
                 $values[$name] = $value;
             }
         }
         return $values;
     }


    /**
     * _restore_props
     *
     * 子クラス共々プロパティを初期値に戻す。
     */
    protected function _restore_props($prop)
    {
    	foreach ($this->_initial_props[0] as $prop => $initial_value)
    	{
    		$this->{$prop} = $initial_value;
    	}

        foreach ($this->_initial_props[1] as $prop => $initial_value)
    	{
    		$this->_child->{$prop} = $initial_value;
    	}
    }


    /**
     * _get_errors
     *
     * 全エラー文言を返す。
     *
     * @param string $error_message (optional)
     * @return bool
     */
    protected function _get_errors()
    {
        return $this->_errors;
    }


    /**
     * _exists_error
     *
     * 引数がなかった場合は、単純にエラーの有無を返す。
     * 引数があった場合は、その文字列から始まるエラーの有無を返す。
     *
     * @param string $error_message (optional)
     * @return bool
     */
    protected function _exists_error($error_message=NULL)
    {
        if (is_null($error_message))
        {
            return $this->_is_fail_check_props;
        }

        if (count(array_filter($this->_errors, 
            function ($v) use ($error_message) {
                if (strpos($v, $error_message) === 0) return TRUE;
                else return FALSE;
            })))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }


    /**
     * _set_error
     *
     * エラーありとして、is_fail_check_propsプロパティにTRUEをセットする。
     * 引数があった場合は、errorsプロパティにそのエラー文言を加える。
     *
     * @param string $error_message (optional)
     */
    protected function _set_error($error_message=NULL)
    {
        $this->_is_fail_check_props = TRUE;
        if (!is_null($error_message))
        {
            $this->_errors[] = $error_message;
        }
    }


    /**
     * _check_and_set_props
     *
     * プロパティの検証とセットを継承したクラスで実装する。
     *
     * @param string $param
     */
    abstract protected function _check_and_set_props($param);
}


// constに配列を入れられないため作った、が呼ばれ方がキモイClass::CONST_VAR()ので却下
/*
class StaticTDGBase
{
    public static function __callStatic($name, $arguments)
    {
        // 継承されるクラスなのでget_class()を使う
        if (!property_exists(get_class(), $name))
        {
            throw new \ErrorException(Util::json_encode(func_get_args(), JSON_UNESCAPED_UNICODE),
                0, E_USER_ERROR, __FILE__, __LINE__);
        }
        // 継承されるクラスなのでstatic::を使う
        return static::$$name;
    }
}
*/

/**
 * ReadOnly
 *
 * 先頭にアンダースコア(_)がついていないプロパティの読み取りのみ認める基底クラス。
 *
 * @package    TestDataGenerator
 */
class ReadOnly
{
    /**
     * __get
     *
     * 引数で与えられたプロパティ名の値を返す。
     * 先頭にアンダースコア(_)がついているプロパティ名は、
     * privateなので例外を投げる。
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!property_exists($this, $name))
        {
            throw new \ErrorException(Util::json_encode(func_get_args(), JSON_UNESCAPED_UNICODE),
                0, E_USER_ERROR, __FILE__, __LINE__);
        }

        if (substr($name, 0, 1) === '_')
        {
            throw new \ErrorException(Util::json_encode(func_get_args(), JSON_UNESCAPED_UNICODE),
                0, E_USER_ERROR, __FILE__, __LINE__);
        }

        return $this->$name;
    }


    /**
     * __set
     *
     * 読み取り専用なので例外を投げる。
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        throw new \ErrorException(Util::json_encode(func_get_args(), JSON_UNESCAPED_UNICODE),
            0, E_USER_ERROR, __FILE__, __LINE__);
    }
}
