<?php
use PHPUnit\Framework\TestCase;
require_once 'src/use_class.php';

function start($argsString)
{
    $args = empty($argsString) ? array() : explode(' ', $argsString);
    ob_start();
    App::run($args);
    return rtrim(ob_get_clean());
}

function rmdir_all($dir) {
    if (!file_exists($dir)) {
        return;
    }
    $dhandle = opendir($dir);
    if ($dhandle) {
        while (false !== ($fname = readdir($dhandle))) {
            if (is_dir( "{$dir}/{$fname}" )) {
                if (($fname != '.') && ($fname != '..')) {
                     $this->rmdir_all("$dir/$fname");
                }
            } else {
                unlink("{$dir}/{$fname}");
            }
        }
        closedir($dhandle);
    }
    rmdir($dir);
}

class CmdTest extends TestCase
{
    private $tmpDir = './tmp';

    private function assertExit($expected, $args)
    {
        try {
            start($args);
            $this->fail('should throw ExitException');
        } catch (ExitException $e) {
            $this->assertEquals($expected, $e->getMessage());
            ob_end_clean();
        }
    }

    public function setUp()
    {
        if (!file_exists($this->tmpDir)) {
            mkdir($this->tmpDir);
        }
    }

    public function tearDown()
    {
        rmdir_all($this->tmpDir);
    }

    public function testNoOption()
    {
        $this->assertEquals('Hello. msg', start('msg'));
    }

    public function testArgumentIsRequired()
    {
        $expected = implode(PHP_EOL, array(
            'hello: too few arguments',
            "Try 'hello --help' for more information.",
        ));
        $this->assertExit($expected, '');
    }

    public function testOptionHelp()
    {
        $expected = implode(PHP_EOL, array(
            'Usage: hello [OPTIONS] FILE',
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
        $this->assertExit($expected, '-h');
        $this->assertExit($expected, '--help');
    }

    public function testOptionVersion()
    {
        $this->assertExit('1.0', '--version');
    }

    public function testOptionTo()
    {
        $this->assertEquals('Hello, yamada. msg', start('-t yamada msg'));
        $this->assertEquals('Hello, yamada. msg', start('--to yamada msg'));
    }

    public function testArgumentOfOptionToIsRequired()
    {
        $this->assertExit('hello: option requires an argument -- t',  '-t -j msg');
        $this->assertExit('hello: option requires an argument -- to', '--to -j msg');
    }

    public function testOptionWrite()
    {
        $path = './tmp/out.txt';
        start("-w {$path} msg");
        $this->assertEquals('Hello. msg' . PHP_EOL, file_get_contents($path));
        unlink($path);
        start("--write {$path} msg");
        $this->assertEquals('Hello. msg' . PHP_EOL, file_get_contents($path));
        unlink($path);
    }

    public function testArgumentOfOptionWriteIsOptional()
    {
        $path = './hello.txt';
        start("-w msg");
        $this->assertEquals('Hello. msg' . PHP_EOL, file_get_contents($path));
        unlink($path);
        start("--write msg");
        $this->assertEquals('Hello. msg' . PHP_EOL, file_get_contents($path));
        unlink($path);
    }

    public function testOptionJapanese()
    {
        $this->assertEquals('こんにちは. 元気？', start('-j 元気？'));
        $this->assertEquals('こんにちは. 元気？', start('--japanese 元気？'));
    }

    public function testIllegalOption()
    {
        $this->assertExit("hello: illegal option -- 'z'", '-z msg');
        $this->assertExit("hello: illegal option -- 'zero'", '--zero msg');
    }

    public function testOptionCanBeAfterArgument()
    {
        $this->assertEquals('こんにちは. msg', start('msg -j'));
    }

    public function testOptionShouldBeSeparated()
    {
        $this->assertExit("hello: illegal option -- 'jw'", '-jw msg');
    }
}
