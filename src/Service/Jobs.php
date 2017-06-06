<?php namespace xlx\Service;
use \Hprose\Promise; 
use \SuperClosure\Serializer;
class Jobs
{ 
    /**
     * Schedule Install
     * @param \dbm\Connect $db
     * @return void
     */
    public function install(\dbm\Connect $db){

    } 

    /**
     * Schedule Timer
     * @param \dbm\Connect $db
     * @return void
     */
    public function timer(\dbm\Connect $db){
        
    } 

    /**
     * Undocumented function
     *
     * @param string $code
     * @return void
     */
    public function Actor($arr,GlobalData\Client $data,\Auryn\Injector $app){ 
        //$fn = unserialize($code); 


        $hash = $arr['hash'];
        if(isset($data->$hash)){
            
        }else{


        }


    }
    /**
     * Server 
     * @param string $code
     * @return void
     */
    public function Task($code,\Auryn\Injector $app){ 
        $fn = unserialize($code); 
        $result = $app->execute($fn);
        return json_encode($result);
    }
}