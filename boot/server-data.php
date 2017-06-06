<?php
require_once __DIR__."/../vendor/autoload.php";   
if(strpos(strtolower(PHP_OS), 'win') === 0){
    class_alias(\Workerman\Windows\Worker::class,\Workerman\Worker::class); 
} 

$worker = new GlobalData\Server('127.0.0.1', 2207);
 
// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    \Workerman\Worker::runAll();
}

