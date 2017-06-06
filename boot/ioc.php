<?php 
$app = new Auryn\Injector;
$app->share(\dbm\Connect::class);
$app->delegate(\dbm\Connect::class,function(){
    return new \dbm\Connect("mysql:dbname=test","root","root");
});

$app->share(\Predis\Client::class);
$app->delegate(\Predis\Client::class,function(){
    return new \Predis\Client('tcp://127.0.0.1:6379');
}); 


$app->share(GlobalData\Client::class);
$app->delegate(GlobalData\Client::class,function(){
    return new GlobalData\Client('127.0.0.1:2207');
}); 
 
return $app;