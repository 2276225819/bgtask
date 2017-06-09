<?php

namespace xlx\Server;

class Task
{
    static $mailbox=[];
    static $defers=[];
    public static function popup($hash){
        if(!isset(self::$mailbox[$hash])){
            self::$mailbox[$hash]=[];
        }
        //take all message and run
        //then deferred
        if(count(self::$mailbox[$hash])){ 
            $data = array_pop(self::$mailbox[$hash]);
            return React\Promise\resolve($data);
        }else{  
            self::$defers[$hash] = new \React\Promise\Deferred();  
            return self::$defers[$hash]->promise();
        } 
       
    }

   
    public static function run($code)
    { 
        $obj = unserialize($code);
        $hash = md5( $obj->getReflector()->getCode() );
        \Channel\Client::on($hash, function ($data)use($hash) { 
            if(isset(self::$defers[$hash])){  
                $defer = self::$defers[$hash];
                unset(self::$defers[$hash]);//顺序不能错
                $defer->resolve($data);  
            }else{
                self::$mailbox[$hash][]=$data; 
            } 
        });
        $obj->defer = new class($hash){
            public function __construct($hash)
            {
                $this->hash = $hash;
            }
            public function __destruct()
            {
                \Channel\Client::unsubscribe($this->hash);
            }
        };
        return $obj();
    }
}
