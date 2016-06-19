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
 * Record
 *
 * 生成するデータのレコードを扱う。
 * 処理中のDBレコードを保持したいため、シングルトンとした。
 *
 * @package    TestDataGenerator
 */
class Record
{
    /** @var string[] レコードのデータ([フィールド名 => フィールド値]) */
    private $_data = [];

    /** @var array 保持用DBレコード */
    private $_db_records = [];

    /** @var int 保持用DBレコードインデックス */
    private $_db_index = NAN;

    /** @var array プロパティ初期値 */
    private static $_initial_props = [];

    /** @var Record インスタンス */
    private static $_instance = NULL;


    /**
     * __construct
     *
     * シングルトン
     */
    private function __construct()
    {

    }


    /**
     * init
     *
     * $_initial_propsが空だったっら、プロパティの初期値を格納。
     * 空でなかったら、プロパティの初期値をリストア。
     */
    private static function init()
    {
        if (!count(self::$_initial_props))
        {
            self::$_instance = new self;
            self::$_initial_props = get_object_vars(self::$_instance);
        }
        else
        {
            foreach (self::$_initial_props as $name => $value)
            {
                self::$_instance->$name = $value;
            }
        }
    }


    /**
     * generate
     *
     * レコードの生成を行う。
     * 引数の$iは生成データのインデックスである。
     *
     * @param int $now_index
     * @param int $num_data
     * @param PDO $db
     * @param string $sql
     * @param int $num_records_per_sql
     * @param Rule[] $record_rules
     * @param bool $need_stdout
     */
    public static function generate($now_index, $num_data, $db, $sql, $num_records_per_sql,
        $record_rules, $need_stdout)
    {
        // init()が呼ばれていなかった時の対応。
        if (!count(self::$_initial_props))
        {
            self::$_instance = new self;
            self::$_initial_props = get_object_vars(self::$_instance);
        }

        if (!$now_index)
        {
            if ($need_stdout) Util::println('"' . implode('","', array_keys($record_rules)) . '"');
        }

        // $num_records_per_sql単位でDBからレコードを取得。
        self::$_instance->_from_db($now_index, $num_data, $db, $sql, $num_records_per_sql);

        // レコード内フィールド値に変換。
        self::$_instance->_to_data($now_index, $record_rules, $need_stdout);
    }


    /**
     * get_data
     *
     * レコードのデータを取得する。
     *
     * @return string[]
     */
    public static function get_data()
    {
        return self::$_instance->_data;
    }


    /**
     * _from_db
     *
     * DBレコードをSQL実行により$num_records_per_sql単位で取得する。
     * DBインスタンスまたはSQLが無ければ、実行されない。
     *
     * @param int $now_index
     * @param int $num_data
     * @param PDO $db
     * @param string $sql
     * @param int $num_records_per_sql
     */
    private function _from_db($now_index, $num_data, $db, $sql, $num_records_per_sql)
    {
        if (is_null($db) || !strlen($sql))
        {
            return;
        }

        self::$_instance->_db_index = $now_index % $num_records_per_sql;

        if (self::$_instance->_db_index)
        {
            return;
        }

        $limit_num = $num_records_per_sql;
        if ($num_data - $now_index < $num_records_per_sql)
        {
            $limit_num = $num_data % $num_records_per_sql;
        }
        $limit_sql = preg_replace('/;\s*$/', " LIMIT {$now_index},{$limit_num};", $sql);

        try
        {
            $stmt = $db->query($limit_sql);
            while ($row = $stmt->fetch())
            {
                self::$_instance->_db_records[] = $row;
            }
        }
        catch (\PDOException $pe)
        {
            throw new TDGE(TDGE::MESSEAGE_INVALID_SQL);
        }
    }


    /**
     * _to_data
     *
     * フィールド値の変換をして、レコードを返す。
     * Fieldのvalueプロパティが変換後の値となる。
     * DBレコードがない場合は、ルールに即したフィールド値を生成する。
     *
     * @param int $now_index
     * @param Rule[] $record_rules
     * @param bool $need_stdout
     */
    private function _to_data($now_index, $record_rules, $need_stdout)
    {
        $new_record = [];
        $fields = [];

        foreach ($record_rules as $field_name => $rules)
        {
            $field = NULL;
            if (!count(self::$_instance->_db_records))
            {
                // DBからレコード取得しなかった場合
                $field = new Field($now_index, $field_name, $rules);
            }
            else
            {
                $db_record = self::$_instance->_db_records[self::$_instance->_db_index];
                $field = new Field($now_index, $field_name, $rules, $db_record);
            }
            $fields[$field_name] = $field;
            $new_record[$field_name] = $field->get_value();
        }

        // ルールがcodeだった場合、レコード内の他フィールド(自分より後にくるフィールドも)を参照するため、
        // 一度codeのフィールドを除くフィールド値を出してから、再度codeのフィールドのために
        // ループを回すという二度手間をしている…
        foreach ($fields as $field_name => $field)
        {
            if (!count(self::$_instance->_db_records))
            {
                $field->set_value($now_index, $field_name, $record_rules[$field_name], [], $new_record);
            }
            else
            {
                $db_record = self::$_instance->_db_records[self::$_instance->_db_index];
                $field->set_value($now_index, $field_name, $record_rules[$field_name], $db_record, $new_record);
            }
            $new_record[$field_name] = $field->get_value();
        }

        if (count($new_record))
        {
            self::$_instance->_data = $new_record;
            if ($need_stdout) Util::println('"' . implode('","', self::$_instance->_data) . '"');
        }
    }
}
