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

use xlx\Client\Task;

$task = new \Workerman\Worker("tcp://0.0.0.0:33445");
$task->name="Server";
$task->count = 8;
$task->onWorkerStart=function ($conn) {
    global $app;
    $app = include "ioc.php";
};

$task->onMessage=\xlx\co\warp(function ($conn, $data) {
    global $app;
    list($class,$code) = json_decode($data, true);
    switch ($class) {
        case Task::class:
             $result = yield \xlx\Server\Task::run($code);
             $conn->close($result);
             break;
        default:
            print_r([Task::class,$class,$code]);
            $conn->close();
            break;
    }
});
// $task->onConnect=function(){echo ">";};
// $task->onClose=function(){echo "<";}; 

// 如果不是在根目录启动，则运行runAll方法
if (!defined('GLOBAL_START')) {
    \Workerman\Worker::runAll();
}
