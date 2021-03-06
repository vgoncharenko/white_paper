<?php

namespace App\Document\Data\Parser;

/**
 * Created by PhpStorm.
 * User: vgoncharenko
 * Date: 4/5/18
 * Time: 11:20 AM
 */
class Jtl
{
    public function parse($path, $config = []): array
    {
//        $content = file_get_contents($path);
//        $arr1 = $this->parseCsv($content);
//        $arr2 = $this->parseCsv2($path);

        return $this->parseCsv2($path, $config);
    }

    function parseCsv($csv_string, $delimiter = ",", $skip_empty_lines = true, $trim_fields = true)
    {
        $enc = preg_replace('/(?<!")""/', '!!Q!!', $csv_string);
        $enc = preg_replace_callback(
            '/"(.*?)"/s',
            function ($field) {
                return urlencode(utf8_encode($field[1]));
            },
            $enc
        );
        $lines = preg_split($skip_empty_lines ? ($trim_fields ? '/( *\R)+/s' : '/\R+/s') : '/\R/s', $enc);
        $headers = str_getcsv(array_shift($lines));
        $count = count($headers);
        return array_filter(
            array_map(
                function ($line) use ($delimiter, $trim_fields, $headers, $count) {
                    $fields = $trim_fields ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line);
                    if (count($fields) < $count) {
                        return [];
                    }
                    $data = [];
                    foreach ($fields as $index => $field) {
                        $data[$headers[$index]] = $field;
                    }
                    return
                        array_map(
                            function ($field) {
                                return str_replace('!!Q!!', '"', utf8_decode(urldecode($field)));
                            },
                            $data
                        );
                },
                $lines
            )
        );
    }

    public function parseCSV2($path, $config = [])
    {
        $lines = [];
        $handle = @fopen($path, "r");
        if ($handle) {
            while (($buffer = fgets($handle)) !== false) {
                $buffer = preg_replace('/(?<!")""/', '!!Q!!', $buffer);
                $buffer = preg_replace_callback(
                    '/"(.*?)"/s',
                    function ($field) {
                        return urlencode(utf8_encode($field[1]));
                    },
                    $buffer
                );

                if (!isset($headers)) {
                    $headers = str_getcsv($buffer);
                    $count = count($headers);
                    continue;
                }

                $fields = array_map('trim', explode(',', $buffer));
                $fields[4] = $fields[8] = '';

                if (count($fields) < $count) {
                    continue;
                }
                $data = [];
                foreach ($fields as $index => $field) {
                    $data[$headers[$index]] = $field;
                }
                if (!empty($config)) {
                    $replace = 'Frontend Pool 1-';
                    $threadId = (int)str_replace($replace, '', $data['threadName']);
                    if ((int)$data[$config['borders']['field']] < (int)$config['borders']['start']
                        || (int)$config['borders']['maxThreadId'] < $threadId
                        || in_array((int)$data[$config['borders']['field']], $config['borders']['excepts'])
                    ) {
                        continue;
                    }
                }
                $lines[] = array_map(
                    function ($field) {
                        return str_replace('!!Q!!', '"', utf8_decode(urldecode($field)));
                    },
                    $data
                );
            }
            if (!feof($handle)) {
                echo "Ошибка: fgets() неожиданно потерпел неудачу\n";
            }
            fclose($handle);
        }

        return $lines;
    }
}