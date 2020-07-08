#!/usr/bin/env php
<?php

define('DIR', __DIR__);

require DIR . '/help.php';
require DIR . '/class.php';
$all = require DIR . '/config.php';

empty($argv[1]) && error_message("未选择配置");
$module = $argv[1];
empty($all[$module]) && error_message("配置[{$module}]不存在");
$config = $all[$module];
!function_exists('exec') && error_message("未选择配置请取消禁用exec函数true");


use Swoole\Process;
use Swoole\Timer;
use Swoole\Event;

swoole_async_set(['enable_coroutine' => false]);

$hashes = [];
$serve = null;

echo "🚀 开始监控 @ " . date('Y-m-d H:i:s') . PHP_EOL;

start();
state();
Timer::tick(config('watch.rate'), 'watch');

function start()
{
    global $serve;
    $serve = new Process('task', true);
    $serve->start();
    if (false === $serve->pid) {
        echo swoole_strerror(swoole_errno()) . PHP_EOL;
        exit(1);
    }
    Event::add($serve->pipe, function ($pipe) use (&$serve) {
        $message = @$serve->read();
        if (!empty($message)) {
            echo $message;
        }
    });
}

function watch()
{
    global $hashes;
    foreach ($hashes as $pathname => $current_hash) {
        if (!file_exists($pathname)) {
            unset($hashes[$pathname]);
            continue;
        }
        $new_hash = file_hash($pathname);
        if ($new_hash != $current_hash) {
            change();
            state();
            break;
        }
    }
}

function state()
{
    global $hashes;
    $files = php_files(config('watch.dir'));
    $hashes = array_combine($files, array_map('file_hash', $files));
    $count = count($hashes);
    echo "📡 监控 $count 个文件..." . PHP_EOL;
}

function change()
{
    global $serve;
    echo "🔄 刷新 @ " . date('Y-m-d H:i:s') . PHP_EOL;
    Process::kill($serve->pid);
    start();
}

function task(Process $serve)
{
    $res = exec(config('task'), $op, $rv);

    if ($rv == 0) {
        echo CONSOLE_COLOR_GREEN . "执行成功" . PHP_EOL;
    } else {
        echo CONSOLE_COLOR_RED . "执行失败" . PHP_EOL;
    }
    foreach ($op as $line) {
        echo CONSOLE_COLOR_DEFAULT . $line . PHP_EOL;
    }
}



function del_dir($path)
{
    if (is_dir($path)) {
        //扫描一个目录内的所有目录和文件并返回数组
        $dirs = scandir($path);
        foreach ($dirs as $dir) {
            //排除目录中的当前目录(.)和上一级目录(..)
            if ($dir != '.' && $dir != '..') {
                //如果是目录则递归子目录，继续操作
                $sonDir = $path . '/' . $dir;
                if (is_dir($sonDir)) {
                    //递归删除
                    del_dir($sonDir);
                    //目录内的子目录和文件删除后删除空目录
                    @rmdir($sonDir);
                } else {
                    //如果是文件直接删除
                    @unlink($sonDir);
                }
            }
        }
        @rmdir($path);
    }
}

