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
 * Pattern
 *
 * パターン配列が宣言されている。
 *
 * @package    TestDataGenerator
 */
class Pattern extends ReadOnly
{
    private $NUMBER = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    private $UPPER_ALPHABET = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    private $LOWER_ALPHABET = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
    private $UNDER_SCORE = ['_'];
    private $HARMLESS_SYMBOL = ['_', '-', '.'];
    private $EMAIL_ADDRESS_DOMAIN = ['@gmail.com', '@yahoo.co.jp', '@outlook.jp', '@docomo.ne.jp', '@ezweb.ne.jp', '@softbank.ne.jp'];
    private $MOBILE_PHONE_NUMBER_HEAD = ['080', '090'];

    private static $_instance = NULL;


    private function __construct()
    {

    }


    /**
     * get
     *
     * 引数よりパターン配列を取得する。
     * 小文字だったら大文字にするサービス実施中。
     *
     * @param string $name
     */
    public static function get($name)
    {
        if (is_null(self::$_instance)) self::$_instance = new self;
        $name = strtoupper($name);
        try
        {
            return self::$_instance->{$name};
        }
        catch (\ErrorException $ee)
        {
            return NULL;
        }
    }
}
