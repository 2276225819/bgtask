<?php include __DIR__.'/vendor/autoload.php';
class_alias(\xlx\Windows\Worker::class,\Workerman\Worker::class);
class_alias(\xlx\Windows\Events\Select::class,\Workerman\Events\Select::class);
use \xlx\Client\Task;

$task = Task::whenAll([
    Task::run(xlx\async(function(){
        //noblock sleep 
        echo "1？";  
        yield \xlx\Client\Task::sleep(3);
        echo "1！";
        return 1;
    })),
    Task::run(function(){
        //block sleep 
        echo "2？";
        sleep(2); 
        echo "2！";
        return 2;
    }),
    Task::run(function(){
        //windows only one process will be blocked
        echo "3？";
        echo "3！";
        return 3;
    })
]); 
print_r($task->getResult()); 
Task::run(function(){
    echo "\n";
}); 
/**Array
(
    [0] => 1
    [1] => 2
    [2] => 3
)
[Done] exited with code=0 in 3.569 seconds
**/