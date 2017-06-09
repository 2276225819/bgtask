<?php 
foreach ([ '/../../../autoload.php', '/../vendor/autoload.php'] as $file) {
    if (file_exists(__DIR__ . $file)) {
        require __DIR__ . $file;
        break;
    }
} 
if(strpos(strtolower(PHP_OS), 'win') === 0){
    class_alias(\xlx\Windows\Worker::class,\Workerman\Worker::class);
    class_alias(\xlx\Windows\Events\Select::class,\Workerman\Events\Select::class);
} 
 
use Workerman\Worker;
use xlx\Client\Task;

$worker = new \Workerman\Worker('http://0.0.0.0:12345'); 
$worker->count=1; 
$worker->onWorkerStart=function(){
    $t = Task::run(function(){
        while (1) {
            echo "loop：\n";
            $atom = yield \xlx\Client\Task::received();  
            echo "has:\n";
            usleep(1000*500);
        } 
    });   
    $GLOBALS['hash'] = $t->hash;
};
$worker->onMessage=function($conn,$data){
    Task::send($GLOBALS['hash'],1); 
    $conn->close(1);
};
\Workerman\Worker::runAll();