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
 * 生成するデータのレコードを扱う。
 *
 * @package    TestDataGenerator
 */
class Record
{
    /** @var string[] レコードのデータ([フィールド名 => フィールド値]) */
    private $_values = [];


    /**
     * __construct
     *
     * 1レコード生成する。
     * レコード内のフィールド値も決定する。
     *
     * @param int $now_index
     * @param Rule[] $record_rules
     * @param WeightedArray[] $weighted_masters
     * @param bool $need_stdout
     */
    public function __construct($now_index, $record_rules, $weighted_masters, $need_stdout)
    {
        if (!$now_index)
        {
            // ヘッダ標準出力
            if ($need_stdout) Util::println('"' . implode('","', array_keys($record_rules)) . '"');
        }

        $new_record = [];
        $fields = [];

        foreach ($record_rules as $field_name => $rules)
        {
            $field = new Field($now_index, $field_name, $rules, $weighted_masters);
            $fields[$field_name] = $field;
            $new_record[$field_name] = $field->get_value();
        }

        // ルールがcodeだった場合、レコード内の他フィールド(自分より後にくるフィールドも)を参照するため、
        // 一度codeのフィールドを除くフィールド値を出してから、再度codeのフィールドのために
        // ループを回すという二度手間をしている…
        foreach ($fields as $field_name => $field)
        {
            $field->set_value($now_index, $field_name, $record_rules[$field_name], $weighted_masters, $new_record);
            $new_record[$field_name] = $field->get_value();
        }

        if (count($new_record))
        {
            $this->_values = $new_record;
            if ($need_stdout) Util::println('"' . implode('","', $this->_values) . '"');
        }
    }

    /**
     * get_values
     *
     * レコードの値を取得する。
     *
     * @return string[]
     */
    public function get_values()
    {
        return $this->_values;
    }
}
