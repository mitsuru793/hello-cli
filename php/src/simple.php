#!/usr/bin/env php
<?php
require('vendor/autoload.php');


const PROGNAME = 'hello';
const VERSION  = '1.0';
$ERROR_STREAM  = STDERR;

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

function puts_error($str='')
{
    global $ERROR_STREAM;
    fputs($ERROR_STREAM, "{$str}\n");
}

// disuse
function array_shifts(&$ary, $num, $preserve_keys=false)
{
    $ary = array_slice($ary, $num, null, $preserve_keys);
}

function usage()
{
    $message = implode(PHP_EOL, array(
        'Usage: ' . PROGNAME . ' [OPTIONS] FILE',
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

function run($args)
{
    $options = array();
    $params = array();
    //foreach ($args as $arg) {
    for ($i = 0; $i < count($args); $i++) {
        $arg = $args[$i];
        // phpのswitchの比較は===ではなく==
        switch (true) {
            case preg_match('/^(-h|--help)$/', $arg):
                usage();
                break;
            case preg_match('/^--version$/', $arg):
                throw new ExitException(VERSION);
                break;
            case preg_match('/^(-t|--to)$/', $arg):
                if (empty($args[$i+1]) || preg_match('/^-.+$/', $args[$i+1])) {
                    $opt_name = preg_replace('/^-*/', '', $arg);
                    throw new ExitException(PROGNAME . ": option requires an argument -- {$opt_name}");
                }
                $options['to'] = $args[$i+1];
                $i++;
                break;
            case preg_match('/^(-w|--write)$/', $arg):
                if (!isset($args[$i+2])) {
                    $options['write'] = './hello.txt';
                } else {
                    $options['write'] = $args[$i+1];
                    $i++;
                }
                break;
            case preg_match('/^(-j|--japanese)$/', $arg):
                $options['japanese'] = true;
                break;
            case preg_match('/^(-|--)$/', $arg):
                array_push($params, $args);
                break;
            case preg_match('/^-.*$/', $arg):
                $opt_name = preg_replace('/^-*/', '', $arg);
                throw new ExitException(PROGNAME . ": illegal option -- '{$opt_name}'");
                break;
            default:
                if (!preg_match('/^-.+$/', $arg)) {
                    array_push($params, $arg);
                }
                break;
        }
    }

    if (empty($params)) {
        $message =  PROGNAME . ': too few arguments' . PHP_EOL;
        $message .= "Try '" . PROGNAME . " --help' for more information.";
        throw new ExitException($message);
    }

    $outString = '';
    $outString .= isset($options['japanese']) ? 'こんにちは' : 'Hello';
    if (isset($options['to'])) {
        $outString .= ", {$options['to']}. ";
    } else {
        $outString .= '. ';
    }
    $outString .= implode(' ', $params);

    if (isset($options['write'])) {
        file_put_contents($options['write'], $outString . PHP_EOL);
    } else {
        puts($outString);
    }
}

//if (realpath($argv[0]) === __FILE__) {
if (isset($argv)) {
    $args = array_slice($argv, 1);
    try {
        run($args);
    } catch (ExitException $e) {
        puts_error($e->getMessage());
        exit($e->getCode());
    }
}
