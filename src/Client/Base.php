<?php namespace xlx\Client; 
use \React\Promise\Promise;
use \React\Promise\PromiseInterface;
use \SuperClosure\Serializer;
class Base {

    public static $addr="tcp://127.0.0.1:11222"; 
    public static $timeout=-1;
 
    public $promise;
    public function __construct(callable $fn){   
        $serializer = new Serializer(); 
        $this->data = $serializer->serialize($fn);  
        $this->promise = new Promise(function($n,$e){
            $this->onsuccess=$n;
            $this->onerror=$e;
        });
    }  
    public function resolve(...$args){ 
        ($this->onsuccess)(...$args);
        return $this->promise;
    }
    public function reject(...$args){
        ($this->onerror)(...$args);
        return $this->promise;
    }
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null){
        return $this->promise->then($onFulfilled,$onRejected,$onProgress);
    }
 
    /**
     * 打开到服务端的连接
     * @return void
     */
    protected function openConnection($timout=-1)
    { 
        $this->connection = stream_socket_client(self::$addr, $err_no, $err_msg);
        if(!$this->connection)
        {
            throw new Exception("can not connect to $address , $err_no:$err_msg");
        }
        stream_set_blocking($this->connection, false);
        stream_set_timeout($this->connection, self::$timeout);
    }

    /**
     * 发送数据给服务端
     * @param string $method
     * @param array $arguments
     */
    protected function sendData($data)
    {
        $this->openConnection();
        $bin_data = json_encode($data)."\n";
        if(fwrite($this->connection, $bin_data) !== strlen($bin_data))
        {
            throw new \Exception('Can not send data');
        }
        return true;
    }  

    
    public function start(){
        try{  
            $this->sendData([ static::class, $this->data, ]);
        }catch(Throwable $e){
            $this->reject($e); 
        }  
    }
    public function wait(){  
        try{  
            $events = [$this->connection] ; 
            while(1){
                $rs = $ws = $es = $events;
                $ret = @stream_select($rs, $ws, $es, 0, static::$timeout); 
                foreach($rs as $fd){ 
                    $this->resolve(stream_get_contents($fd)); 
                    return true;
                } 
            } 
        }catch(Throwable $e){
            $this->reject($e); 
        }  
    }
 
    public static function waitAll($tasks){  
        try{
            foreach($tasks as $key=>$task){
                $task->sendData([ static::class, $task->data, ]);
                $events[$key]=$task->connection;   
            } 
            while(count($events)){  
                $rs = $ws = $es = $events;
                $ret = @stream_select($rs, $ws, $es, 0, static::$timeout); 
                foreach($rs as $key=>$fd){  
                    $tasks[$key]->resolve(stream_get_contents($fd)); 
                    unset($events[$key]);  
                } 
            }  
            return \React\Promise\all($tasks);
        }catch(Throwable $e){
            return \React\Promise\Reject($e);
        } 
    } 
    
    

    public static function startNew($fn){
        $task = new Task($fn);
        $task->start();
        return $task;
    }
}
