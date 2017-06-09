<?php
require_once __DIR__."/../vendor/autoload.php";   
if(strpos(strtolower(PHP_OS), 'win') === 0){
    class_alias(\xlx\Windows\Worker::class,\Workerman\Worker::class);
    class_alias(\xlx\Windows\Events\Select::class,\Workerman\Events\Select::class); 
} 

use xlx\Client\Task;

$task = new \Workerman\Worker("tcp://0.0.0.0:33445");
$task->name="Server";
$task->count = 8; 
$task->onWorkerStart=function($conn){
    global $app; $app = include "ioc.php";    
};  

$task->onMessage=\xlx\co\warp(function($conn,$data) {  
    global $app; 
    list($class,$code) = json_decode($data,true);  
    switch ( $class ) { 
        case Task::class:   
            return yield \xlx\Server\Task::run($code); 
        default:
            print_r([Task::class,$class,$code]);
    } 
}); 
// $task->onConnect=function(){echo ">";};
// $task->onClose=function(){echo "<";}; 

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    \Workerman\Worker::runAll();
}

