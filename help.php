<?php

// 自动初始化配置文件
$config_file = DIR . '/config.php';
if (!is_file($config_file)) {
    $config_example_file = DIR . '/config.example.php';
    exec("cp {$config_example_file} $config_file");
    echo CONSOLE_COLOR_YELLOW . "自动初始化配置文件成功, 请重试!!" . PHP_EOL;
    exit(1);
}

/**
 * 输出错误
 * @param string $message
 * @param bool $stop
 */
function error_message(string $message, bool $stop=true) {
    echo CONSOLE_COLOR_RED . "[x] {$message}" . PHP_EOL;
    if ($stop) exit(1);
}

/**
 * 获取模块配置
 * @param string $name
 * @return mixed
 */
function config(string $name)
{
    global $config;
    $names = explode('.', $name);
    $tmp = $config;
    foreach ($names as $name) {
        if (isset($tmp[$name])) {
            $tmp = $tmp[$name];
        } else {
            error_message("获取配置失败");
        }
    }
    return $tmp;
}

function php_files(string $dirname): array
{
    $directory = new RecursiveDirectoryIterator($dirname);
    $filter = new Filter($directory);
    $iterator = new RecursiveIteratorIterator($filter);
    return array_map(function ($fileInfo) {
        return $fileInfo->getPathname();
    }, iterator_to_array($iterator));
}

/**
 * 获取文件的hash
 * @param string $pathname
 * @return string
 */
function file_hash(string $pathname): string
{
    $contents = file_get_contents($pathname);
    if (false === $contents) {
        return 'deleted';
    }
    return md5($contents);
}
