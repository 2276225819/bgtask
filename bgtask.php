<?php include __DIR__.'/vendor/autoload.php';
class_alias(\Windows\Worker::class,\Workerman\Worker::class);
class_alias(\Windows\Events\Select::class,\Workerman\Events\Select::class);

use Workerman\Worker;
 

$worker = new \Workerman\Worker('tcp://0.0.0.0:33445'); 
$worker->count=4; 
$worker->onWorkerStart=function(){
    global $app;
};
$worker->onMessage=xlx\co\warp(function($conn,$data){
    list($class,$data) = json_decode($data);
    $result = yield unserialize($data)(); 
    $conn->close(json_encode($result));  
});
\Workerman\Worker::runAll();