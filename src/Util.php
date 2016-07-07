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

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use React\EventLoop;
use React\Promise;

/**
 * Util
 *
 * 文字コード、CSV、SQLなどを扱うユーティリティ関数群。
 * 独立性の高さ(requireはWeightedArrayのみ)と
 * オプション引数に適当にNULL渡してもなんとかしてくれる融通な設計がウリ。
 *
 * @package    TestDataGenerator
 */
class Util
{
    const UTF8 = 'UTF-8';
    const SJIS = 'SJIS-win';
    const ASCII = 'ASCII';
    const NORMALIZE_CHARSET_REGEX = '{"UTF-8": "@^utf-?8$@i", "SJIS": "@^s(hift)?[-_]?jis$@i", "SJIS-win": "@^(s(hift)?[-_]?jis[-_]?win|cp932|ms932)$@i", "EUC-JP": "@^(euc[-_]?jp|ujis)$@i", "eucJP-win": "@^euc[-_]?jp[-_]?(win|ms)$@i"}';
    const MYSQL_NORMALIZE_CHARSET_REGEX = '{"utf8": "@^utf-?8$@i", "sjis": "@^s(hift)?[-_]?jis$@i", "cp932": "@^(s(hift)?[-_]?jis[-_]?win|cp932|ms932)$@i", "ujis": "@^(euc[-_]?jp|ujis)$@i", "eucjpms": "@^euc[-_]?jp[-_]?(win|ms)$@i"}';
    const JSON_EXT = '.json';
    const YML_EXT = '.yml';
    const SQL_EXT = '.sql';
    const CSV_EXT = '.csv';


    /**
     * is_numeric_uint
     *
     * 正の整数かチェックする。
     *
     * @param int|string $param
     * @return bool
     */
    public static function is_numeric_uint($param)
    {
        return (is_numeric($param) && $param - abs(intval($param)) === 0) ? TRUE : FALSE;
    }


    /**
     * is_flexible_bool
     *
     * ブールに相当するかかチェックする。
     * TRUE, FALSE, 1, 0, "1", "0", $allow_yes_noが真なら"yes", "no", $allow_y_nが真なら"y", "n"を認める。
     *
     * @param mixed $param
     * @param bool $allow_yes_no (optional)
     * @param bool $allow_y_n (optional)
     * @return bool
     */
    public static function is_flexible_bool($param, $allow_yes_no=FALSE, $allow_y_n=FALSE)
    {
        if ($param === TRUE || $param === FALSE || $param === 1 || $param === 0 || $param === '1' || $param === '0')
        {
            return TRUE;
        }
        else
        {
            if ($allow_yes_no)
            {
                if (strtolower($param) === 'yes' || strtolower($param) === 'no')
                {
                    return TRUE;
                }
            }

            if ($allow_y_n)
            {
                if (strtolower($param) === 'y' || strtolower($param) === 'n')
                {
                    return TRUE;
                }
            }

            return FALSE;
        }
    }


    /**
     * array_depth
     *
     * 配列の次元数を返す。
     *
     * @param array $a
     * @param int $c (optional)
     * @return int
     */
    public static function array_depth($a, $c=0)
    {
        if (is_array($a) && count($a))
        {
            ++$c;
            $_c = array($c);
            foreach ($a as $v)
            {
                if (is_array($v) && count($v))
                {
                    $_c[] = Util::array_depth($v, $c);
                }
            }
            return max($_c);
        }
        return $c;
    }


    /**
     * s_to_hms
     *
     * 秒をh時間m分s秒に変換して返す。
     * 各々0の場合はスキップする。
     *
     * @param int|float $s
     * @param int $scale (optional)
     * @return string|bool
     */
     public static function s_to_hms($s, $scale=3)
     {
        if (!is_numeric($s) || $s < 0)
        {
            return FALSE;
        }

        $h = intval($s / 3600);
        $m = intval(($s - $h * 3600) / 60);
        $s = floatval(bcsub($s, $h * 3600 + $m * 60, $scale));

        $hms = '';
        if ($h) $hms .= $h . '時間';
        if ($m) $hms .= $m . '分';
        if ($s) $hms .= $s . '秒';

        return $hms;
     }


    /**
     * json_last_error_msg
     *
     * json_last_error_msg()の無いPHP5.4に対応させる。
     *
     * @return string
     */
    public static function json_last_error_msg()
    {
        $error_code = json_last_error();

        switch ($error_code)
        {
            case 1:
                return 'スタックの深さの最大値を超えました。';

            case 2:
                return 'JSON の形式が無効、あるいは壊れています。';

            case 3:
                return '制御文字エラー。おそらくエンコーディングが違います。';

            case 4:
                return '構文エラー。';

            case 5:
                return '正しくエンコードされていないなど、不正な形式の UTF-8 文字。';

            case 6:
                return 'エンコード対象の値に再帰参照が含まれています。';

            case 7:
                return 'エンコード対象の値に NAN あるいは INF が含まれています。';

            case 8:
                return 'エンコード不可能な型の値が渡されました。';

            default:
                return '';
        }
    }


    /**
     * json_encode
     *
     * jsonの仕様に合わせ、NANを文字列の'NAN'、INFを文字列の'INF'に変換した上で
     * json_encode()をかける。
     * スカラ値は、エラーとする。
     * エラーとなった場合は、空文字を返す。
     *
     * @param array $data
     * @param int $option (optional)
     * @return string
     */
    public static function json_encode($data, $option=0)
    {
        if (!$option) $option = 0;
        if (!is_array($data) && !is_object($data))
        {
            return '';
        }

        $formed = [];
        foreach ($data as $k => $v)
        {
            if (is_float($k) && is_nan($k)) $k = 'NAN';
            if (is_float($k) && is_infinite($k)) $k = 'INF';
            if (is_float($v) && is_nan($v)) $v = 'NAN';
            if (is_float($v) && is_infinite($v)) $v = 'INF';
            $formed[$k] = $v;
        }

        $json = json_encode($formed, $option);

        if (!$json) return '';
        else return $json;
    }


    /**
     * json_decode
     *
     * デフォルトで連想配列で返すようにした。
     * エラーとなった場合は、エラー文言を返す。
     *
     * @param string $json
     * @param bool $is_assoc (optional)
     * @return array|object
     */
    public static function json_decode($json, $is_assoc=TRUE)
    {
        if ($is_assoc) $is_assoc = TRUE;
        else $is_assoc = FALSE;

        $data = json_decode($json, $is_assoc);

        $error_msg = Util::json_last_error_msg();

        if (strlen($error_msg)) return $error_msg;
        else return $data;
    }


    /**
     * normalize_charset
     *
     * いい加減な文字コード名をmb_list_encodings()にある正規の名称に変換する。
     *
     * @param string $charset
     * @param bool $mysql_flg (optional)
     * @return string
     */
    public static function normalize_charset($charset, $mysql_flg=FALSE)
    {
        if ($mysql_flg) $mysql_flg = TRUE;
        else $mysql_flg = FALSE;

        $regex_arr = [];
        if (!$mysql_flg)
        {
            $regex_arr = json_decode(Util::NORMALIZE_CHARSET_REGEX, TRUE);
        }
        else
        {
            $regex_arr = json_decode(Util::MYSQL_NORMALIZE_CHARSET_REGEX, TRUE);
        }
        foreach ($regex_arr as $normalized => $regex)
        {
            if (preg_match($regex, $charset))
            {
                return $normalized;
            }
        }
        return FALSE;
    }


    /**
     * check_ext
     *
     * ファイルパスのファイルの拡張子が指定されたものかチェックする。
     *
     * @param string $filepath
     * @param string $ext
     * @return bool
     */
    public static function check_ext($filepath, $ext)
    {
        if ($ext[0] !== '.') $ext = '.' . $ext;
        if (strrpos($filepath, $ext) === strlen($filepath) - strlen($ext))
        {
            return TRUE;
        }
        return FALSE;
    }


    /**
     * get_data_by_json_file
     *
     * ファイルパスで指定されたjsonを配列で返す。
     * PHP5.4に対応するため、エラーだった場合は、
     * json_last_error_msg()相当のエラーメッセージを返す。
     *
     * @param string $filepath
     * @return mixed
     */
    public static function get_data_by_json_file($filepath)
    {
        return Util::json_decode(file_get_contents($filepath), TRUE);
    }


    /**
     * get_data_by_yml_file
     *
     * ファイルパスで指定されたyamlを配列で返す。
     * エラーだった場合は、エラーメッセージを返す。
     *
     * @param string $filepath
     * @return mixed
     */
    public static function get_data_by_yml_file($filepath)
    {
        $yml = new Parser();
        $data = [];

        try
        {
            $data = $yml->parse(file_get_contents($filepath), TRUE);
        }
        catch (ParseException $pe)
        {
            return $pe->getMessage();
        }

        return $data;
    }


    /**
     * create_weighted_csv
     *
     * 重み付け(頻度)を考慮したCSVを生成し上書きする。
     * 配列が渡された場合は、重み付けしたデータの配列を返す。
     * 主キーとなるidカラムが無かったら追加され、あったらシーケンス値が振り直される。
     *
     * @param int $num
     * @param string|array $input
     * @param string $outputs (optional)
     * @param string $weight_column (optional)
     * @param int $divisor (optional)
     * @param bool $is_header (optional)
     * @param bool $need_new_id (optional)
     * @param string $delimiter (optional)
     * @param string $enclosure (optional)
     * @param int $max_records (optional)
     * @return mixed
     */
    public static function create_weighted_csv($num, $input, $output='', $weight_column='', $divisor=1,
        $is_header=TRUE, $need_new_id=TRUE, $delimiter=',', $enclosure='"', $max_records=1000000)
    {
        if (!$output) $output = '';
        if (!$weight_column && !is_int($weight_column) && !is_string($weight_column))
        {
            $weight_column='';
        }
        if ($is_header) $is_header = TRUE;
        else $is_header = FALSE;
        if ($need_new_id) $need_new_id = TRUE;
        else $need_new_id = FALSE;
        if (!$delimiter && !is_string($delimiter)) $delimiter = ',';
        if (!$enclosure && !is_string($enclosure)) $enclosure = '"';
        if (!$max_records && !is_int($max_records)) $max_records = 1000000;
        if (!is_int($num) || $num <= 0)
        {
            return FALSE;
        }
        if ($num > $max_records)
        {
            return FALSE;
        }
        $columns = [];

        // 引数のファイルか配列を元にした入力処理。
        if (is_string($input) && is_file($input))
        {
            if (!strlen($output))
            {
                $output = $input;
            }
            $fp = fopen($input, 'r');
            $cnt = 0;
            $input = [];
            while (($record = fgetcsv($fp, 0, $delimiter, $enclosure)) !== FALSE)
            {
                if ($cnt > $max_records)
                {
                    fclose($fp);
                    return FALSE;
                }
                $input[] = $record;
                $cnt++;
            }
            fclose($fp);
        }
        else if (is_array($input))
        {
            if (count($input) > $max_records)
            {
                return FALSE;
            }
        }
        else
        {
            return FALSE;
        }

        // WeightedArrayクラスを使った重み付け処理。
        $weighted = new WeightedArray();
        $data = (strlen($output)) ? '' : [];
        reset($input);
        foreach ($input as $record)
        {
            if (!count($columns))
            {
                if ($is_header && strlen($output))
                {
                    $columns = $record;
                    $data = '"' . implode('","', $record) . '"' . PHP_EOL;
                    continue;
                }
                else
                {
                    $columns = array_keys($record);
                }
            }
            if (count($record) != count($columns))
            {
                return FALSE;
            }
            $temp = [];
            reset($columns);
            foreach ($record as $value)
            {
                $temp[current($columns)] = $value;
                next($columns);
            }
            if (!array_key_exists($weight_column, $temp))
            {
                return FALSE;
            }
            $weighted->append($temp, $temp[$weight_column], $divisor);
        }

        // 重み付けに応じた出力処理。
        $cnt = 0;
        while ($cnt < $num)
        {
            $chosen = $weighted->rand();

            // need_new_idフラグが立っていて、idを追加するか振りなおす。
            if ($is_header && $need_new_id)
            {
                if (!array_key_exists('id', $chosen))
                {
                    if (!$cnt)
                    {
                        array_unshift($columns, 'id');
                        if (strlen($output))
                        {
                            $data = '"id",' . $data;
                        }
                    }
                    $chosen = ['id' => $cnt + 1] + $chosen;
                }
                else
                {
                    $chosen['id'] = $cnt + 1;
                }
            }
            else if (!$is_header && $need_new_id)
            {
                if (!array_key_exists(0, $chosen))
                {
                    if (!$cnt)
                    {
                        $columns[] = count($columns);
                    }
                    array_unshift($chosen, $cnt + 1);
                }
                else
                {
                    $chosen[0] = $cnt + 1;
                }
            }

            if (strlen($output))
            {
                $data .= '"' . implode('","', $chosen) . '"' . PHP_EOL;
            }
            else
            {
                $data[] = $chosen;
            }

            $cnt++;

            // 出力処理がメモリを食うため、出力先がファイルの場合は、1000ずつに区切る。
            if (strlen($output))
            {
                if ($cnt == 1000)
                {
                    if (file_put_contents($output, $data) === FALSE)
                    {
                        return FALSE;
                    }
                    else
                    {
                        $data = '';
                    }
                }
                else if ($cnt % 1000 == 0)
                {
                    if (file_put_contents($output, $data, FILE_APPEND | LOCK_EX) === FALSE)
                    {
                        return FALSE;
                    }
                    else
                    {
                        $data = '';
                    }
                }
            }
        }

        if (strlen($output))
        {
            if ($cnt < 1000)
            {
                if (file_put_contents($output, $data) === FALSE)
                {
                    return FALSE;
                }
                else
                {
                    return $output;
                }
            }
            else
            {
                if (file_put_contents($output, $data, FILE_APPEND | LOCK_EX) === FALSE)
                {
                    return FALSE;
                }
                else
                {
                    return $output;
                }
            }
        }
        else
        {
            return $data;
        }
    }


    /**
     * csv_to_bulk_insert
     *
     * CSVファイルの内容を、MySQLを対象としたバルクインサート文に変換し、指定されたファイルパスに出力する。
     * 成功したら出力ファイルパスを返す。失敗したらFALSEを返す。
     * 配列が渡された場合は、INSERT文が格納された配列を返す。
     * パラメータの使われ方など、処理の流れをよく読んで使用すること。
     *
     * @param string|array $input
     * @param string $output (optional)
     * @param string $table (optional)
     * @param string[] $columns (optional)
     * @param bool $is_header (optional)
     * @param bool $need_null (optional)
     * @param string $input_null_value (optional)
     * @param int $eol (optional)
     * @param int $divisor (optional)
     * @param string[] $unique_columns (optional)
     * @param string[] $sum_columns (optional)
     * @param string $head_sql (optional)
     * @param string $tail_sql (optional)
     * @param string $to_charset (optional)
     * @param string $from_charset (optional)
     * @param string $delimiter (optional)
     * @param string $enclosure (optional)
     * @param int $max_records (optional)
     * @return string|bool
     */
    public static function csv_to_bulk_insert($input, $output='', $table='', $columns=[],
        $is_header=TRUE, $need_null=TRUE, $input_null_value='NULL', $eol=PHP_EOL, $divisor=1000,
        $unique_columns=[], $sum_columns=[], $head_sql='', $tail_sql='',
        $to_charset='', $from_charset='', $delimiter=',', $enclosure='"', $max_records=1000000)
    {
        if (!$output) $output = '';
        if (!$table) $table = '';
        if (!$columns) $columns = [];
        if ($is_header) $is_header = TRUE;
        else $is_header = FALSE;
        if ($need_null) $need_null = TRUE;
        else $need_null = FALSE;
        if (!$input_null_value && !is_int($input_null_value) && !is_string($input_null_value))
        {
            $input_null_value = 'NULL';
        }
        if (!$divisor) $divisor = 1000;
        if (!$unique_columns) $unique_columns = [];
        if (!$sum_columns) $sum_columns = [];
        if (!$head_sql) $head_sql = '';
        if (!$tail_sql) $tail_sql = '';
        if (!$to_charset) $to_charset = '';
        if (!$from_charset) $from_charset = '';
        if (!$delimiter && !is_string($delimiter)) $delimiter = ',';
        if (!$enclosure && !is_string($enclosure)) $enclosure = '"';
        if (!$max_records) $max_records = 1000000;

        // CSV内のNULLをどう変換するか。
        $output_null_value = ($need_null) ? "NULL" : "''";

        $cnt = NAN;
        $sql = '';
        $sql_insert = '';
        $original_columns = [];

        if (is_string($input) && is_file($input))
        {
            // 引数で指定が無ければ、CSVファイル名をテーブル名にする。
            if (!strlen($table))
            {
                $table = substr(basename($input), 0, strrpos(basename($input), '.'));
            }
            // 引数で指定が無ければ、同ディレクトリに拡張子を.sqlにしたファイルを出力する。
            if (!strlen($output))
            {
                $output = substr($input, 0, strrpos($input, '.')) . Util::SQL_EXT;
            }
            $contents = file_get_contents($input);
            if (!strlen($from_charset))
            {
                $from_charset = mb_detect_encoding($contents);
            }
            if (!strlen($to_charset))
            {
                if ($from_charset == Util::ASCII)
                {
                    $to_charset = Util::ASCII;
                }
                else
                {
                    $to_charset = Util::UTF8;
                }
            }


            $fp = fopen($input, 'r');
            $input = [];

            // PHP組み込みのfgetcsv()でCSVのレコードを順次取得。
            while (($record = fgetcsv($fp, 0, $delimiter, $enclosure)) !== FALSE)
            {
                if (count($input) > $max_records) return FALSE;
                $input[] = $record;
            }

            fclose($fp);
        }
        else if (is_array($input))
        {
            if (count($input) > $max_records) return FALSE;
            // 引数でテーブル名の指定が無ければ失敗。
            if (!strlen($table))
            {
                return FALSE;
            }
        }
        else
        {
            return FALSE;
        }

        foreach ($input as $record)
        {
            // CSVが空行だった場合
            if (array_key_exists(0, $record) && is_null($record[0]))
            {
                continue;
            }

            // 初回
            if (is_nan($cnt))
            {
                // ヘッダを考慮したカラム名の取得。
                $original_columns = array_keys($record);
                if ($is_header)
                {
                    $original_columns = $record;
                }
                if (!count($columns)) $columns = $original_columns;

                // 配列のキーをユニークカラム名、値をカラム値が入る配列にする。
                $temp = [];
                foreach ($unique_columns as $name)
                {
                    if (!in_array($name, $columns))
                    {
                        return FALSE;
                    }
                    $temp[$name] = [];
                }
                $unique_columns = $temp;

                $temp = [];
                foreach ($sum_columns as $from => $to)
                {
                    // オリジナルに存在しなければ失敗。
                    if (!in_array($from, $original_columns))
                    {
                        return FALSE;
                    }
                    // 出力するカラムに存在しなければ追加。
                    if (!in_array($to, $columns))
                    {
                        $columns[] = $to;
                    }
                    // [0]をオリジナルカラム名、[1]を出力するカラム名、[2]を集計値にする。
                    $temp[] = [$from, $to, 0];
                }
                $sum_columns = $temp;

                if (array_sum(array_map(['self', 'is_numeric_uint'], $columns)) === count($columns))
                {
                    $sql_insert = 'INSERT INTO `' . $table . '` VALUES ';
                }
                else
                {
                    $sql_insert = 'INSERT INTO `' . $table . '` (`' . implode('`, `', $columns) . '`) VALUES ';
                }
                if (strlen($head_sql)) $sql .= $head_sql . $eol;
                $cnt = 0;
                if ($is_header) continue;
            }

            if ($cnt % $divisor === 0)
            {
                $sql .= $sql_insert;
            }

            // $recordを[カラム名 => 値]の連想配列にする。
            $temp = [];
            foreach ($record as $k => $v)
            {
                if (is_int($k)) $temp[$original_columns[$k]] = $v;
                else $temp[$k] = $v;
            }
            $record = $temp;
            $temp = [];
            foreach ($columns as $col)
            {
                if (array_key_exists($col, $record))
                {
                    $temp[$col] = $record[$col];
                }
                else
                {
                    $temp[$col] = NULL;
                }
            }
            $record = $temp;

            $is_repeated = FALSE;
            foreach ($unique_columns as $col => $values)
            {
                if (in_array($record[$col], $values))
                {
                    $is_repeated = TRUE;
                }
                else
                {
                    $unique_columns[$col][] = $record[$col];
                }
            }
            // ユニークでなかったらやり直し。
            if ($is_repeated) continue;

            foreach ($sum_columns as $k => $v)
            {
                // 数値でなかったら失敗。
                if (!is_numeric($record[$v[0]])) return FALSE;
                // 集計
                $record[$v[1]] = $v[2] + $record[$v[0]];
                // 現在の集計値を格納。
                $sum_columns[$k][2] = $record[$v[1]];
            }

            $record = array_map(function($v) use($input_null_value, $output_null_value, $to_charset, $from_charset) {
                if ($v === $input_null_value) {
                    return $output_null_value;
                } else {
                    if ($to_charset == $from_charset) return "'{$v}'";
                    else return mb_convert_encoding("'{$v}'", $to_charset, $from_charset);
                }
            }, $record);

            $sql .= '(' . implode(', ', $record) . ')';
            // $sqlにインサート部分が追加されたらインクリメント
            $cnt++;

            if ($cnt % $divisor)
            {
                $sql .= ', ';
            }
            else
            {
                // $divisor単位でSQLを切る
                $sql .= ';' . $eol;
            }
        }

        if (substr($sql, -2) == ', ')
        {
            $sql = substr($sql, 0, -2) . ';' . $eol;
        }

        if (strlen($tail_sql))
        {
            $sql .= $tail_sql . $eol;
        }

        $sql = substr($sql, 0, -strlen($eol));

        if (strlen($output))
        {
            file_put_contents($output, $sql);
            return $output;
        }
        else
        {
            return explode($eol, $sql);
        }
    }


    /**
     * println
     *
     * 改行を付加して標準出力する。
     *
     * @param string $message
     */
    public static function println($message)
    {
        // strlen(PHP_EOL)は\r\nだった場合を考慮している。
        if (strrpos($message, PHP_EOL) !== strlen($message) - strlen(PHP_EOL))
        {
            $message .= PHP_EOL;
        }
        if (strpos(PHP_OS, 'WIN') === 0 && strpos(exec('chcp'), '932') !== FALSE)
        {
            $message = mb_convert_encoding($message, Util::SJIS, Util::UTF8);
        }
        echo $message;
    }


    /**
     * fputcsv
     *
     * NULLと文字コード変換を考慮したfputcsv()
     * fwrite()した書き込みバイト数を返す。
     *
     * @param resouce $fp
     * @param array $record
     * @param bool $need_header (optional)
     * @param bool $need_null (optional)
     * @param string $eol (optional)
     * @param string $to_charset (optional)
     * @param string $from_charset (optional)
     * @return int
     */
    public static function fputcsv($fp, $record, $need_null=FALSE, $eol=PHP_EOL, $to_charset='', $from_charset='')
    {
        if ($need_null) $need_null = TRUE;
        else $need_null = FALSE;
        if (!$eol) $eol = PHP_EOL;
        if (!$to_charset) $to_charset = '';
        if (!$from_charset) $from_charset = '';

        $line = '';
        $cnt = 0;
        $res = FALSE;
        $last = count($record);

        foreach ($record as $field)
        {
            $cnt++;

            if (!is_null($field) || !$need_null)
            {
                $line .= '"' . preg_replace('/(?<!\\\\)\"/u', '""', $field) . '"';
            }
            else
            {
                $line .= 'NULL';
            }

            if ($cnt != $last)
            {
                $line .= ',';
            }
            else
            {
                $line .= $eol;
            }
        }

        if ($cnt)
        {
            if (strlen($to_charset))
            {
                if (strlen($from_charset))
                {
                    $line = mb_convert_encoding($line, $to_charset, $from_charset);
                }
                else
                {
                    $line = mb_convert_encoding($line, $to_charset);
                }
            }
            $res = fwrite($fp, $line);
        }
        return $res;
    }


    /**
     * safely_eval
     *
     * ホワイトリストおよびブラックリストを使い安全性を高めたeval()。
     * リストは[T_SOMETHING => ['token1', 'token2']の形式で指定する。
     * $black_list = [T_STRING => ['exec', 'system'], T_EXIT => ['exit', 'die']];
     * 空の配列を指定することで、全てのトークンで有効となる。
     * $black_list = [T_EXIT => []];
     * ホワイトリストとブラックリストは併用できず、両方指定された場合はホワイトリストのみが有効となる。
     * デフォルトのホワイトリストはforeach以外のループが使えないなど厳し目の設定としている。
     * 失敗時にFALSEを返すため、$codeはブールを返さないようにする必要がある。
     * 引数の$use_default_listがTRUEだったら、デフォルトのホワイトリスト／ブラックリストに、
     * 引数で指定されたホワイトリスト／ブラックリストを加えて処理する。
     * $required_listで必須のトークンを指定できる。
     * 例えばデフォルトの[T_RETURN => []]なら、return文が無いと失敗としてFALSEを返す。
     * 引数$max_code_tokenで最大トークン数を指定できる。
     *
     * @param string $code
     * @param bool $use_default_list (optional)
     * @param array $white_list (optional)
     * @param array $black_list (optional)
     * @param array $required_list (optional)
     * @param int $timeout_ms (optional)
     * @return mixed
     */
    public static function safely_eval($code,
        $use_default_list=TRUE, $white_list=[], $black_list=[], $required_list=[], $max_code_token=1000)
    {
        if ($use_default_list) $use_default_list = TRUE;
        else $use_default_list = FALSE;
        if (!$white_list) $white_list = [];
        if (!$black_list) $black_list = [];
        if (!$required_list) $required_list = [];
        if (!$max_code_token) $max_code_token = 1000;

        $code_tokens = token_get_all('<?php ' . $code . ' ?>');
        if (count($code_tokens) - 2 > $max_code_token) return FALSE;

        $default_white_list = [];
        $default_white_list[T_AND_EQUAL] = [];
        $default_white_list[T_ARRAY] = [];
        $default_white_list[T_AS] = [];
        $default_white_list[T_BOOLEAN_AND] = [];
        $default_white_list[T_BOOLEAN_OR] = [];
        $default_white_list[T_CLOSE_TAG] = [];
        $default_white_list[T_COMMENT] = [];
        $default_white_list[T_CONCAT_EQUAL] = [];
        $default_white_list[T_CONSTANT_ENCAPSED_STRING] = [];
        $default_white_list[T_DEC] = [];
        $default_white_list[T_DIV_EQUAL] = [];
        $default_white_list[T_DNUMBER] = [];
        $default_white_list[T_DOUBLE_ARROW] = [];
        $default_white_list[T_ELSE] = [];
        $default_white_list[T_ELSEIF] = [];
        $default_white_list[T_EMPTY] = [];
        $default_white_list[T_ENCAPSED_AND_WHITESPACE] = [];
        $default_white_list[T_FOREACH] = [];
        $default_white_list[T_IF] = [];
        $default_white_list[T_INC] = [];
        $default_white_list[T_ISSET] = [];
        $default_white_list[T_IS_EQUAL] = [];
        $default_white_list[T_IS_GREATER_OR_EQUAL] = [];
        $default_white_list[T_IS_IDENTICAL] = [];
        $default_white_list[T_IS_NOT_EQUAL] = [];
        $default_white_list[T_IS_NOT_IDENTICAL] = [];
        $default_white_list[T_IS_SMALLER_OR_EQUAL] = [];
        $default_white_list[T_LIST] = [];
        $default_white_list[T_LNUMBER] = [];
        $default_white_list[T_MINUS_EQUAL] = [];
        $default_white_list[T_MOD_EQUAL] = [];
        $default_white_list[T_MUL_EQUAL] = [];
        $default_white_list[T_NEW] = [];
        $default_white_list[T_NUM_STRING] = [];
        $default_white_list[T_OBJECT_OPERATOR] = [];
        $default_white_list[T_OPEN_TAG] = [];
        $default_white_list[T_OR_EQUAL] = [];
        $default_white_list[T_PAAMAYIM_NEKUDOTAYIM] = [];
        $default_white_list[T_PLUS_EQUAL] = [];
        $default_white_list[T_RETURN] = [];
        $default_white_list[T_SL] = [];
        $default_white_list[T_SL_EQUAL] = [];
        $default_white_list[T_SR] = [];
        $default_white_list[T_SR_EQUAL] = [];
        $default_white_list[T_STRING] = [
            'ceil', 'floor', 'log', 'mt_rand', 'mt_srand', 'pow', 'rand', 'sqrt', 'srand',
            'empty', 'floatval', 'intval', 'isset', 'unset',
            'is_array', 'is_bool', 'is_double', 'is_float', 'is_int', 'is_integer',
            'is_long', 'is_null', 'is_numeric', 'is_real', 'is_scalar', 'is_string',
            'array_change_key_case', 'array_chunk', 'array_combine', 'array_count_values',
            'array_diff_assoc', 'array_diff_key', 'array_diff', 'array_flip',
            'array_intersect_assoc', 'array_intersect_key', 'array_intersect',
            'array_key_exists', 'array_keys', 'array_merge_recursive',
            'array_merge', 'array_multisort', 'array_pad', 'array_pop',
            'array_product', 'array_push', 'array_rand', 'array_reverse',
            'array_search', 'array_shift', 'array_slice', 'array_splice',
            'array_sum', 'array_unique', 'array_unshift', 'array_values',
            'array', 'arsort', 'asort', 'compact', 'count', 'current', 'each', 'end',
            'in_array', 'key', 'krsort', 'ksort', 'natcasesort', 'natsort', 'next', 'pos',
            'prev', 'reset', 'rsort', 'shuffle', 'sizeof', 'sort',
            'chop', 'count_chars', 'explode', 'implode', 'join',
            'ltrim', 'number_format', 'rtrim', 'str_getcsv', 'str_ireplace',
            'str_pad', 'str_repeat', 'str_replace', 'str_shuffle', 'str_split',
            'str_word_count', 'strcasecmp', 'strchr', 'strcmp', 'strcspn',
            'stripos', 'stristr', 'strlen', 'strnatcasecmp', 'strnatcmp',
            'strncasecmp', 'strncmp', 'strpbrk', 'strpos', 'strrchr',
            'strrev', 'strripos', 'strrpos', 'strspn', 'strstr',
            'strtolower', 'strtoupper', 'strtr', 'substr_compare', 'substr_count',
            'substr_replace', 'substr', 'trim', 'ucfirst', 'ucwords', 'wordwrap',
            'addslashes', 'bin2hex', 'chr', 'crc32', 'crypt',
            'hex2bin', 'html_entity_decode', 'htmlentities', 'htmlspecialchars',
            'md5', 'nl2br','ord', 'sha1',
            'mb_check_encoding', 'mb_convert_case', 'mb_convert_encoding', 'mb_convert_kana',
            'mb_convert_variables', 'mb_detect_encoding', 'mb_detect_order', 'mb_encoding_aliases',
            'mb_ereg_match', 'mb_ereg_replace_callback', 'mb_ereg_replace', 'mb_ereg_search_getpos',
            'mb_ereg_search_getregs', 'mb_ereg_search_init', 'mb_ereg_search_pos', 'mb_ereg_search_regs',
            'mb_ereg_search_setpos', 'mb_ereg_search', 'mb_ereg', 'mb_eregi_replace',
            'mb_eregi', 'mb_get_info', 'mb_internal_encoding', 'mb_language',
            'mb_list_encodings', 'mb_regex_encoding', 'mb_regex_set_options', 'mb_split',
            'mb_strcut', 'mb_strimwidth', 'mb_stripos', 'mb_stristr',
            'mb_strlen', 'mb_strpos', 'mb_strrchr', 'mb_strrichr',
            'mb_strripos', 'mb_strrpos', 'mb_strstr', 'mb_strtolower',
            'mb_strtoupper', 'mb_strwidth', 'mb_substitute_character', 'mb_substr_count', 'mb_substr',
            'preg_match_all', 'preg_match', 'preg_quote', 'preg_replace',
            'date_add', 'date_create_from_format', 'date_create', 'date_date_set',
            'date_default_timezone_get', 'date_default_timezone_set', 'date_diff', 'date_format',
            'date_get_last_errors', 'date_interval_format', 'date_isodate_set',
            'date_modify', 'date_offset_get', 'date_parse_from_format', 'date_parse',
            'date_sub', 'date_time_set', 'date_timestamp_get', 'date_timestamp_set',
            'date_timezone_get', 'date_timezone_set', 'date', 'getdate',
            'gettimeofday', 'gmdate', 'gmmktime', 'gmstrftime',
            'localtime', 'microtime', 'mktime', 'strftime',
            'strptime', 'strtotime', 'time', 'timezone_abbreviations_list',
            'timezone_identifiers_list', 'timezone_location_get', 'timezone_name_from_abbr', 'timezone_name_get',
            'timezone_offset_get', 'timezone_open', 'timezone_transitions_get', 'timezone_version_get',
            'PHP_OS', 'PHP_EOL', 'DIRECTORY_SEPARATOR', 'TRUE', 'FALSE', 'NULL'
        ];
        $default_white_list[T_VARIABLE] = [];
        $default_white_list[T_WHITESPACE] = [];
        $default_white_list[T_XOR_EQUAL] = [];

        $default_black_list = [];

        $default_required_list = [];
        $default_required_list[T_RETURN] = [];

        if ($use_default_list)
        {
            // 引数のリストをデフォルトのリストに追加していく。
            $temp = $default_white_list;
            foreach ($white_list as $tk => $tvs)
            {
                if (!is_int($tk) || !is_array($tvs))
                {
                    return FALSE;
                }
                if (!array_key_exists($tk, $temp))
                {
                    $temp[$tk] = $tvs;
                }
                else
                {
                    foreach ($tvs as $tv)
                    {
                        $temp[$tk][] = $tv;
                    }
                }
            }
            $white_list = $temp;

            $temp = $default_black_list;
            foreach ($black_list as $tk => $tvs)
            {
                if (!is_int($tk) || !is_array($tvs))
                {
                    return FALSE;
                }
                if (!array_key_exists($tk, $temp))
                {
                    $temp[$tk] = $tvs;
                }
                else
                {
                    foreach ($tvs as $tv)
                    {
                        $temp[$tk][] = $tv;
                    }
                }
            }
            $black_list = $temp;

            $temp = $default_required_list;
            foreach ($required_list as $tk => $tvs)
            {
                if (!is_int($tk) || !is_array($tvs))
                {
                    return FALSE;
                }
                if (!array_key_exists($tk, $temp))
                {
                    $temp[$tk] = $tvs;
                }
                else
                {
                    foreach ($tvs as $tv)
                    {
                        $temp[$tk][] = $tv;
                    }
                }
            }
            $required_list = $temp;
        }

        if (!count($white_list) && !count($black_list))
        {
            return FALSE;
        }

        // 両方あったらホワイトリストを優先。
        if (count($white_list) && count($black_list))
        {
            $black_list = [];
        }

        // 必須リストのトークンがホワイトリストにあるか、ブラックリストに無いか検証。
        foreach ($required_list as $t => $tokens)
        {
            if (count($white_list))
            {
                if (!array_key_exists($t, $white_list))
                {
                    return FALSE;
                }
                foreach ($tokens as $token)
                {
                    if (!in_array($token, $white_list[$t]))
                    {
                        return FALSE;
                    }
                }
            }
            if (count($black_list))
            {
                if (array_key_exists($t, $black_list))
                {
                    return FALSE;
                }
                foreach ($tokens as $token)
                {
                    if (in_array($token, $black_list[$t]))
                    {
                        return FALSE;
                    }
                }
            }
        }

        foreach ($code_tokens as $token)
        {
            if (is_array($token))
            {
                // ホワイトリストに無いヤツ
                if (count($white_list))
                {
                    if (!array_key_exists($token[0], $white_list))
                    {
                        return FALSE;
                    }
                    if (count($white_list[$token[0]]) && !in_array($token[1], $white_list[$token[0]]))
                    {
                        return FALSE;
                    }
                }

                // ブラックリストにあるヤツ
                if (count($black_list))
                {
                    if (array_key_exists($token[0], $black_list))
                    {
                        if (!count($black_list[$token[0]]))
                        {
                            return FALSE;
                        }
                        if (in_array($token[1], $black_list[$token[0]]))
                        {
                            return FALSE;
                        }
                    }
                }

                // 必須なトークンがあったら、リストから消していき…
                if (count($required_list))
                {
                    if (array_key_exists($token[0], $required_list))
                    {
                        if (!count($required_list[$token[0]]))
                        {
                            unset($required_list[$token[0]]);
                        }
                        else
                        {
                            $idx = array_search($token[1], $required_list[$token[0]], TRUE);
                            if ($idx !== FALSE)
                            {
                                unset($required_list[$token[0]][$idx]);
                            }
                        }
                    }
                }
            }
        }

        // 必須リストが全部消えていなかったら失敗。
        if (count($required_list))
        {
            return FALSE;
        }

        $result = @eval($code);
        if (is_null($result))
        {
            $result = FALSE;
        }
        return $result;
    }
}
