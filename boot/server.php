<?php
require_once __DIR__."/../vendor/autoload.php";   
if(strpos(strtolower(PHP_OS), 'win') === 0){
    class_alias(\Workerman\Windows\Worker::class,\Workerman\Worker::class); 
}
$task = new \Workerman\Worker("text://0.0.0.0:11222");
$task->name="Server";
$task->count = 8;
$task->onWorkerStart=function(){
    global $app; $app = include "ioc.php";  
};
$task->onMessage=function($conn,$data) { 
    global $app; 
    list($class,$code) = json_decode($data,true);  
    $class = substr($class,4);//namespace "xlx\"{length=4}
    $result = $app->execute([\xlx\Service\Jobs::class,$class],[$code]);  
    return $conn->close($result);
}; 
// $task->onConnect=function(){echo ">";};
// $task->onClose=function(){echo "<";};


// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    \Workerman\Worker::runAll();
}

