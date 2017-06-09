<?php
require_once __DIR__."/../vendor/autoload.php";   
if(strpos(strtolower(PHP_OS), 'win') === 0){
    class_alias(\xlx\Windows\Worker::class,\Workerman\Worker::class);
    class_alias(\xlx\Windows\Events\Select::class,\Workerman\Events\Select::class);
} 
 

$worker = new Channel\Server('127.0.0.1', 2206);
 
// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    \Workerman\Worker::runAll();
}

