<?php include __DIR__.'/vendor/autoload.php';
class_alias(\Windows\Worker::class,\Workerman\Worker::class);
class_alias(\Windows\Events\Select::class,\Workerman\Events\Select::class);

$task = Task::whenAll([
    Task::run(xlx\async(function(){
        echo "!";  
        //noblock sleep 
        yield Task::sleep(2);
        return 1;
    })),
    Task::run(function(){
        echo "!";
        //block sleep 
        sleep(2);
        return 2;
    }),
    Task::run(function(){
        echo "!";
        return 3;
    })
]);

print_r($task->getResult());
/**Array
(
    [0] => 1
    [1] => 2
    [2] => 3
)
[Done] exited with code=0 in 2.107 seconds
**/