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
 * TDGException
 *
 * 発生した例外を整形して投げる。
 * PHPエラーも例外として扱われる。
 *
 * @package TestDataGenerator
 */
class TDGException extends \Exception
{
    const MESSEAGE_INVALID_CONSTRUCTOR = 'コンストラクタの引数には設定のファイルパスを渡してください。';
    const MESSEAGE_INVALID_MAIN = 'mainメソッドはレコードルール(record_rules)の設定が無いと呼び出しできません。';
    const MESSEAGE_MISSING_MODULE = '必要なPHP拡張モジュールが見つかりませんでした。';
    const MESSEAGE_MYSQL = 'MySQLの接続で問題が発生しました。';
    const MESSEAGE_INVALID_MYSQL_SETTING = 'my.iniまたはmy.cnfに次の項目を設定してください。';
    const MESSEAGE_MISSING_CONFIG = '設定ファイルが見つかりませんでした。';
    const MESSEAGE_INVALID_CONFIG = '設定ファイルに問題があります。';
    const MESSEAGE_INVALID_OUTPUT_FILEPATH = '出力ファイルに問題が発生しました。';
    const MESSEAGE_INVALID_DB = 'DBに問題が発生しました。';
    const MESSEAGE_INVALID_PRE_PROC_FILE = '前処理用ファイルに問題があります。';
    const MESSEAGE_INVALID_POST_PROC_FILE = '後処理用ファイルに問題があります。';
    const MESSEAGE_INVALID_PRE_PROC_SQL = '前処理SQLに問題があります。';
    const MESSEAGE_INVALID_POST_PROC_SQL = '後処理SQLに問題があります。';
    const MESSEAGE_INVALID_SQL = 'SQLに問題があります。';
    const MESSEAGE_INVALID_COLUMN_NAME
        = 'データの生成時に問題が発生しました。レコードルールのcolumn_nameが合っているか確認してください。';
    const MESSEAGE_INVALID_PATTERN_LENGTH
        = 'データの生成時に問題が発生しました。レコードルールのpatternのlenが適切か確認してください。';
    const MESSEAGE_OUT_OF_RANGE_FIELD_LENGTH
        = '指定された範囲の文字数でフィールドを生成できませんでした。';
    const MESSEAGE_MISSING_DATA = 'データを生成できませんでした。';
    const MESSEAGE_FILE_OUTPUT = 'ファイル出力に問題が発生しました。';


    /**
     * __construct
     *
     * エラー・例外のハンドリング。
     * エラーメッセージの引数は、$messageと$deteil_messageで2つある。
     *
     * @param string $message
     * @param string $deteil_message (optional)
     * @param int $code (optional)
     * @param Exception $previous (optional)
     */
    public function __construct($message, $deteil_message='', $code=0, Exception $previous=NULL)
    {
        if (strrpos($message, PHP_EOL) !== strlen($message) - strlen(PHP_EOL))
        {
            $message .= PHP_EOL;
        }
        if (strlen($deteil_message))
        {
            $message .= " {$deteil_message}";
        }
        if (strrpos($message, PHP_EOL) !== strlen($message) - strlen(PHP_EOL))
        {
            $message .= PHP_EOL;
        }
        if (strpos(PHP_OS, 'WIN') === 0 && strpos(exec('chcp'), '932') !== FALSE)
        {
            $message = mb_convert_encoding($message, Util::SJIS, Util::UTF8);
        }
        parent::__construct($message, $code, $previous);
    }
}
