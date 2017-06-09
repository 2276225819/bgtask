<?php
foreach ([ '/../../../autoload.php', '/../vendor/autoload.php'] as $file) {
    if (file_exists(__DIR__ . $file)) {
        require __DIR__ . $file;
        break;
    }
} 
if (strpos(strtolower(PHP_OS), 'win') === 0) {
    class_alias(\xlx\Windows\Worker::class, \Workerman\Worker::class);
    class_alias(\xlx\Windows\Events\Select::class, \Workerman\Events\Select::class);
}
 
$task = new \Workerman\Worker();
$task->name="Server-Timer";
$task->count = 1;
$task->onWorkerStart = function () {
    global $app;
    $app = include "ioc.php";
    //$app->execute([\xlx\Service\Jobs::class,'install']);
    // \Workerman\Lib\Timer::add(1,function()use($app){
    //     $app->execute([\xlx\Service\Jobs::class,'timer']);
    // });
};

// 如果不是在根目录启动，则运行runAll方法
if (!defined('GLOBAL_START')) {
    \Workerman\Worker::runAll();
}
