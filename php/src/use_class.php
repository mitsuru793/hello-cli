#!/usr/bin/env php
<?php
require('vendor/autoload.php');

class App
{
    const PROGNAME = 'hello';
    const VERSION  = '1.0';
    private static $options = [];
    private static $params  = [];

    public static function run($args)
    {
        self::parseOption($args);
        self::requireParams();
        $outString = self::makeOutPut();
        self::output($outString);
        self::$options = [];
        self::$params = [];
    }

    private static function usage()
    {
        $message = implode(PHP_EOL, array(
            'Usage: ' . self::PROGNAME . ' [OPTIONS] FILE',
            '  This script is ~.',
            '',
            'Options:',
            '  -h, --help',
            '      --version',
            '  -t, --to person-name',
            '  -w, --write [file-path]',
            '  -j, --japanese',
            ''
        ));
        throw new ExitException($message);
    }

    private static function requireParams()
    {
        if (empty(self::$params)) {
            $message =  self::PROGNAME . ': too few arguments' . PHP_EOL;
            $message .= "Try '" . self::PROGNAME . " --help' for more information.";
            throw new ExitException($message);
        }
    }

    private static function makeOutPut()
    {
        $outString = '';
        $outString .= isset(self::$options['japanese']) ? 'こんにちは' : 'Hello';
        if (isset(self::$options['to'])) {
            $outString .= ', ' . self::$options['to'] . '. ';
        } else {
            $outString .= '. ';
        }
        $outString .= implode(' ', self::$params);
        return $outString;
    }

    private static function output($str)
    {
        if (isset(self::$options['write'])) {
            file_put_contents(self::$options['write'], $str . PHP_EOL);
        } else {
            puts($str);
        }
    }

    private static function parseOption($args)
    {
        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];
            // phpのswitchの比較は===ではなく==
            switch (true) {
                case self::is_option('-h|--help', $arg):
                    self::usage();
                    break;
                case self::is_option('--version', $arg):
                    throw new ExitException(self::VERSION);
                    break;
                case self::is_option('-t|--to', $arg):
                    if (empty($args[$i+1]) || preg_match('/^-.+$/', $args[$i+1])) {
                        $opt_name = preg_replace('/^-*/', '', $arg);
                        throw new ExitException(self::PROGNAME . ": option requires an argument -- {$opt_name}");
                    }
                    self::$options['to'] = $args[$i+1];
                    $i++;
                    break;
                case self::is_option('-w|--write', $arg):
                    if (!isset($args[$i+2])) {
                        self::$options['write'] = './hello.txt';
                    } else {
                        self::$options['write'] = $args[$i+1];
                        $i++;
                    }
                    break;
                case self::is_option('-j|--japanese', $arg):
                    self::$options['japanese'] = true;
                    break;
                case self::is_option('-|--', $arg):
                    array_push(self::$params, $args);
                    break;
                case self::is_option('-.*', $arg):
                    $opt_name = preg_replace('/^-*/', '', $arg);
                    throw new ExitException(self::PROGNAME . ": illegal option -- '{$opt_name}'");
                    break;
                default:
                    if (!self::is_option('-.+', $arg)) {
                        array_push(self::$params, $arg);
                    }
                    break;
            }
        }
    }

    private static function is_option($regex, $value)
    {
        return preg_match("/^({$regex})$/", $value);
    }
}

class ExitException extends Exception
{
    public function __construct($message='', $code=1)
    {
        parent::__construct($message, $code);
    }
}

function puts($str='')
{
    echo $str, PHP_EOL;
}

function puts_error($str='', $ERROR_STREAM=STDERR)
{
    fputs($ERROR_STREAM, "{$str}\n");
}

if (isset($argv)) {
    $args = array_slice($argv, 1);
    try {
        App::run($args);
    } catch (ExitException $e) {
        puts_error($e->getMessage());
        exit($e->getCode());
    }
}
