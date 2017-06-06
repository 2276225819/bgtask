<?php
require_once __DIR__."/../vendor/autoload.php";   
if(strpos(strtolower(PHP_OS), 'win') >= 0){ 
    class_alias(\Workerman\Windows\Worker::class,\Workerman\Worker::class); 
}



$addr = "text://0.0.0.0:11221";
$task = new \Workerman\Worker($addr);
$task->count = 1;
$task->onWorkerStart = function(){ 
    $app = new Auryn\Injector;
    
    $app->share(\dbm\Connect::class);
    $app->delegate(\dbm\Connect::class,function(){
        return new \dbm\Connect("mysql:dbname=test","root","root");
    });
    
    $app->share(\Predis\Client::class);
    $app->delegate(\Predis\Client::class,function(){
        return new \Predis\Client('tcp://127.0.0.1:6379');
    });

    $app->execute([\xlx\Service\Jobs::class,'install']);
    // \Workerman\Lib\Timer::add(1,function()use($app){ 
    //     $app->execute([\xlx\Service\Jobs::class,'timer']); 
    // });  
};  
$task->onMessage=function($conn,$data){
    echo "onMessage:\n";
    $data = json_decode($data,true); 
    switch($data['type']){

        case \xlx\Actor::class:
            //redis+queue
            break;

        case \xlx\Schedule::class:
            //redis+timer
            break;

        case \xlx\Task::class:
            //process
            break; 
        default:
            print_r($data);
            break;
    }
    $conn->close();
};
\Workerman\Worker::runAll(); 